<?php

namespace RNS\Integrations\Processors\Database;

use Bitrix\Main\Loader;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;
use Bitrix\Tasks\Internals\Task\ElapsedTimeTable;
use Bitrix\Tasks\Internals\Task\ProjectDependenceTable;
use Bitrix\Tasks\Internals\Task\RelatedTable;
use Bitrix\Tasks\Item\Task;
use CTaskCommentItem;
use CTaskElapsedItem;
use CTaskItem;
use CTasks;
use CTaskTags;
use CUser;
use CUtil;
use RNS\Integrations\Helpers\EntityFacade;
use RNS\Integrations\Helpers\HLBlockHelper;
use RNS\Integrations\Helpers\ImportHelper;
use RNS\Integrations\Models\EntityMapItem;
use RNS\Integrations\Models\EntityTypeMapItem;
use RNS\Integrations\Processors\DataTransferBase;
use RNS\Integrations\Processors\DataTransferResult;
use RNS\Integrations\Processors\FieldHandlerBase;

/**
 * Реализация импорта из базы данных.
 * @package RNS\Integrations\Processors\Database
 */
class Import extends DataTransferBase
{
    public function getCapabilities()
    {
        return [
          'supportedDBMS' => [
            'REFERENCE_ID' => ['pgsql', 'mssql', 'mysql'],
            'REFERENCE' => ['PostgreSQL', 'MS SQL Server', 'MySQL']
          ]
        ];
    }

    /**
     * @throws \Exception
     */
    protected function execute()
    {
        global $USER;
        if (!(isset($USER) && $USER instanceof CUser)) {
            $USER = new CUser();
        }

        $this->result = new DataTransferResult();

        try {
            Loader::includeModule('tasks');
            Loader::includeModule('socialnetwork');

            Loc::loadMessages(__FILE__);

            $provider = EntityFacade::getDataProvider($this->exchangeTypeCode, $this->systemCode, $this->options, $this->mapping);

            $entities = $provider->getEntities($this->lastOperationDate);

            $projectMap = $this->mapping->getProjectMap();
            $refFieldName = $this->integrationOptions->getEntityRefFieldName();

            $idFieldName = $this->integrationOptions->getEntityIdFieldName();
            $keyFieldName = $this->integrationOptions->getEntityKeyField();
            $isTranslitNeeded = $this->integrationOptions->isTranslitNeeded();
            $valueMapping = json_decode($this->integrationOptions->getValueMapping() ?? '{}', true);

            $typeMap = $this->mapping->getEntityTypeMap();
            $statusMap = $this->mapping->getEntityStatusMap();
            $propMap = $this->mapping->getEntityPropertyMap();

            $propMapItems = $propMap->getItems();

            $userMap = $this->mapping->getUserMap();
            $defaultUserId = null;
            if (!empty($userMap->getDefaultExternalEmail())) {
                $res = UserTable::getList([
                    'select' => ['ID'],
                    'filter' => ['=EMAIL' => $userMap->getDefaultExternalEmail()]
                ]);
                if ($row = $res->fetch()) {
                    $defaultUserId = intval($row['ID']);
                }
            }
            $respSettings = $this->mapping->getResponsibleSettings();

            $isLevelSupported = !empty($this->integrationOptions->getEntityParentIdFieldName());
            $taskMaxLevel = $this->options->getTaskLevel();

            /** @var FieldHandlerBase $fieldHandler */
            $fieldHandler = null;
            $handlerClassName = "RNS\\Integrations\\Processors\\Hooks\\{$this->systemCode}\\FieldHandler";
            if (class_exists($handlerClassName)) {
                $fieldHandler = new $handlerClassName();
            }

            $allRelatedTasks = [];

            $projectTime = [];

            $importedTaskIds = [];

            $this->result->objectsTotal = count($entities);

            foreach ($entities as $entity) {
                $success = true;
                try {
                    $entityId = $entity[$idFieldName];

                    if ($isLevelSupported) {
                        $level = $provider->getEntityLevel($entityId);
                        if ($level > $taskMaxLevel) {
                            continue;
                        }
                    }

                    if (empty($entity[$refFieldName])) {
                        throw new \Exception(
                          Loc::getMessage('INTEGRATIONS_PROCESSOR_DB_IMPORT_NO_PROJECT_CODE',
                            ['#CODE#' => $entity[$keyFieldName]])
                        );
                    }

                    $data = [];
                    $data['UF_TASK_SOURCE'] = $this->sourceId;
                    $data['UF_EXTERNAL_ID'] = $entityId;

                    /** @var EntityTypeMapItem $typeMapItem */
                    $typeMapItem = null;
                    $entityTypeId = null;
                    $entityTypeCode = null;

                    foreach ($propMapItems as $propMapItem) {

                        if ($entityTypeCode && $propMapItem->getInternalTypeId() &&
                          $propMapItem->getInternalTypeId() != $entityTypeCode) {
                            continue;
                        }

                        $srcProp = $propMapItem->getExternalPropertyId();
                        if (empty($srcProp)) {
                            continue;
                        }

                        $value = $entity[$srcProp];

                        $destProp = $propMapItem->getInternalPropertyId();
                        if (empty($destProp)) {
                            continue;
                        }

                        if (!empty($valueMapping)) {
                            $mapping = $valueMapping[$destProp];
                            if (!empty($mapping)) {
                                if (is_numeric($value)) {
                                    $value = (int)$value;
                                }
                                if (isset($mapping[$value])) {
                                    $value = $mapping[$value];
                                }
                            }
                        }

                        if (EntityFacade::checkIndustrialOffice()) {
                            if ($destProp == 'UF_RNS_TYPE_ENTITY') {
                                $originalValue = $value;
                                if ($isTranslitNeeded) {
                                    $value = CUtil::translit($value, 'ru', ['change_case' => 'U']);
                                }
                                if ($this->systemCode != 'sap') {
                                    $typeMapItem = $typeMap->getItemByExternalTypeId($value);
                                    if ($typeMapItem && $typeMapItem->getInternalTypeId()) {
                                        $value = $typeMapItem->getInternalTypeId();
                                    } else {
                                        $value = $typeMap->getDefaultTypeId();
                                        if (!$value) {
                                            $this->addError(
                                              Loc::getMessage('INTEGRATIONS_PROCESSOR_DB_IMPORT_ENTITY_MAP_NOT_FOUND',
                                                ['#CODE#' => $originalValue, '#ID#' => $entityId])
                                            );
                                            $success = false;
                                            break;
                                        }
                                    }

                                    $dictItem = HLBlockHelper::getList('b_hlsys_entities', ['ID'], [], 'ID',
                                      ['UF_CODE' => $value], false);
                                    if (!empty($dictItem)) {
                                        $entityTypeCode = $value;
                                        $value = $dictItem[0]['ID'];
                                        $entityTypeId = $value;
                                    }
                                }

                            } elseif ($destProp == 'UF_RNS_STATUS') {
                                $originalValue = $value;
                                if ($this->systemCode != 'sap') {
                                    if (!$entityTypeId) {
                                        $this->addError(
                                          Loc::getMessage('INTEGRATIONS_PROCESSOR_DB_IMPORT_BAD_MAP_ORDER',
                                            ['#CODE#' => $entityId])
                                        );
                                        $success = false;
                                        break;
                                    }
                                    if ($isTranslitNeeded) {
                                        $value = CUtil::translit($value, 'ru', ['change_case' => 'U']);
                                    }
                                    $statusMapItem = $typeMapItem
                                      ? $statusMap->getItemByExternalStatusId($typeMapItem->getExternalTypeId(), $value)
                                      : null;
                                    if ($statusMapItem && $statusMapItem->getInternalStatusId()) {
                                        $value = $statusMapItem->getInternalStatusId();
                                    } else {
                                        $value = $statusMap->getDefaultStatusId();
                                        if (!$value) {
                                            $this->addError(
                                              Loc::getMessage(
                                                'INTEGRATIONS_PROCESSOR_DB_IMPORT_STATUS_MAP_NOT_FOUND', [
                                                    '#STATUS#' => $originalValue,
                                                    '#ENTITY#' => $typeMapItem ? $typeMapItem->getExternalTypeId() : $entityTypeCode,
                                                    '#ID#' => $entityId
                                                ]
                                              )
                                            );
                                            $success = false;
                                            break;
                                        }
                                    }

                                    $dictItem = HLBlockHelper::getList(
                                      'b_hlsys_status_entity', ['ID'], [], 'ID', [
                                      'UF_ENTITY_TYPE_BIND' => $entityTypeCode,
                                      'UF_CODE' => $value
                                    ],
                                      false);
                                    if (!empty($dictItem)) {
                                        switch($value){
                                            case 'NEW':
                                                $data['STATUS'] = CTasks::STATE_NEW;
                                                break;
                                            case 'PENDING':
                                                $data['STATUS'] = CTasks::STATE_PENDING;
                                                break;
                                            case 'IN_PROGRESS':
                                                $data['STATUS'] = CTasks::STATE_IN_PROGRESS;
                                                break;
                                            case 'SUPPOSEDLY_COMPLETED':
                                                $data['STATUS'] = CTasks::STATE_SUPPOSEDLY_COMPLETED;
                                                break;
                                            case 'DEFERRED':
                                                $data['STATUS'] = CTasks::STATE_DEFERRED;
                                                break;
                                            case 'DECLINED':
                                                $data['STATUS'] = CTasks::STATE_DECLINED;
                                                break;
                                            case 'COMPLETED':
                                            case 'CLOSED':
                                            case 'FAILED':
                                            case 'PASSED':
                                                $data['STATUS'] = CTasks::STATE_COMPLETED;
                                                break;
                                        }
                                        $value = $dictItem[0]['ID'];
                                    }
                                }
                            }
                        } else {
                            if ($destProp == 'STATUS') {
                                if ($this->systemCode != 'sap') {
                                    $entityType = $entity['issue_type'] ?: 'Задача';
                                    if ($isTranslitNeeded) {
                                        $value = CUtil::translit($value, 'ru', ['change_case' => 'U']);
                                        $entityType = CUtil::translit($entityType, 'ru', ['change_case' => 'U']);
                                    }

                                    $statusMapItem = $statusMap->getItemByExternalStatusId($entityType, $value);
                                    if ($statusMapItem && $statusMapItem->getInternalStatusId()) {
                                        $value = $statusMapItem->getInternalStatusId();
                                    } else {
                                        $value = 'IN_PROGRESS';
                                    }
                                    $value = EntityFacade::getStatusMap($value);
                                }
                            }
                        }
                        if ($destProp == 'GROUP_ID') {
                            $projectItems = $projectMap->getItemsByExternalId($entity[$refFieldName]);
                            if (!empty($projectItems)) {
                                $value = array_map(function ($item) {
                                    /** @var EntityMapItem $item */
                                    return $item->getInternalEntityId();
                                }, $projectItems)[0];
                            } else {
                                $value = $projectMap->getDefaultEntityId();
                            }
                        } elseif ($destProp == 'RESPONSIBLE_ID') {
                            $userMapItem = $value ? $userMap->getItemByExternalId($value) : null;
                            if ($userMapItem && $userMapItem->getInternalId()) {
                                $value = $userMapItem->getInternalId();
                            } elseif ($defaultUserId) {
                                $value = $defaultUserId;
                            } elseif ($userMap->isIgnoreAliens()) {
                                $success = false;
                                break;
                            } else {
                                if ($respSettings->isExecutorLoading() && $this->isManualRun() && intval($USER->getID()) > 0) {
                                    $value = intval($USER->getID());
                                } else {
                                    $value = $respSettings->getDefaultResponsibleId() ?? $defaultUserId;
                                }
                            }
                        } elseif ($destProp == 'CREATED_BY') {
                            $userMapItem = $value ? $userMap->getItemByExternalId($value) : null;
                            if ($userMapItem && $userMapItem->getInternalId()) {
                                $value = $userMapItem->getInternalId();
                            } elseif ($defaultUserId) {
                                $value = $defaultUserId;
                            } elseif ($userMap->isIgnoreAliens()) {
                                $success = false;
                                break;
                            } else {
                                if ($respSettings->isAuthorLoading() && $this->isManualRun() && intval($USER->getID()) > 0) {
                                    $value = intval($USER->getID());
                                } else {
                                    $value = $respSettings->getDefaultAuthorId() ?? $defaultUserId;
                                }
                            }
                        } elseif ($destProp == 'PARENT_ID') {
                            if ($value) {
                                $parentTask = CTasks::GetList([], [
                                  '=UF_TASK_SOURCE' => $this->sourceId,
                                  '=UF_EXTERNAL_ID' => $value,
                                  '=ZOMBIE' => 'N',
                                  'CHECK_PERMISSIONS' => 'N'
                                ], ['ID'])->Fetch();
                                $value = $parentTask ? $parentTask['ID'] : null;
                            } else {
                                $value = null;
                            }
                        } elseif ($destProp == 'DEADLINE') {
                            if (!$value) {
                                $value = new DateTime();
                                $value = $value->add("P{$respSettings->getDefaultDeadlineDays()}D");
                            }
                        } elseif ($destProp == 'GROUP_ID') {
                            $projectItems = $projectMap->getItemsByExternalId($entity[$refFieldName]);
                            if (!empty($projectItems)) {
                                $value = $projectItems[0]->getInternalEntityId();
                            } else {
                                $value = $projectMap->getDefaultEntityId();
                            }
                        } elseif ($destProp == 'TAGS' || $destProp == 'RELATED_TASKS' || $destProp == 'COMMENTS') {
                            $value = json_decode($value);
                        }

                        if ($destProp == 'START_DATE_PLAN' || $destProp == 'END_DATE_PLAN' || $destProp == 'DEADLINE' ||
                          $destProp == 'CREATED_DATE' || $destProp == 'CHANGED_DATE' || $destProp == 'DATE_START' ||
                          $destProp == 'CLOSED_DATE') {
                            if (is_string($value)) {
                                $value = DateTime::createFromTimestamp(strtotime($value));
                            }
                        }

                        if ($destProp == 'PRIORITY') {
                            if (!in_array($value, ['0', '1', '2'])) {
                                if ($this->systemCode == 'sap') {
                                    $value = $propMap->getDefaultPriority();
                                } else {
                                    $value = '1';
                                }
                            }
                        }

                        if ($destProp == 'UF_RNS_SPRINT') {
                            if ($fieldHandler) {
                                $value = $fieldHandler->processField($srcProp, $value, $data);
                            }
                        }

                        $data[$destProp] = $value;
                    }

                    if (!$success) {
                        continue;
                    }

                    if ($this->systemCode == 'sap') {
                        if (EntityFacade::checkIndustrialOffice()) {
                            $dictItem = HLBlockHelper::getList('b_hlsys_entities', ['ID', 'UF_CODE'], [], 'ID',
                              ['UF_CODE' => $typeMap->getDefaultTypeId()], false);
                            if (!empty($dictItem)) {
                                $entityTypeCode = $dictItem[0]['UF_CODE'];
                                $data['UF_RNS_TYPE_ENTITY'] = $dictItem[0]['ID'];
                            }

                            $dictItem = HLBlockHelper::getList(
                              'b_hlsys_status_entity', ['ID'], [], 'ID', [
                              'UF_ENTITY_TYPE_BIND' => $entityTypeCode,
                              'UF_CODE' => $statusMap->getDefaultStatusId()
                            ],
                              false);
                            if (!empty($dictItem)) {
                                $data['UF_RNS_STATUS'] = $dictItem[0]['ID'];
                            }
                        } else {
                            $defStatusId = $statusMap->getDefaultStatusId();
                            $data['STATUS'] = EntityFacade::getStatusMap($defStatusId);
                        }
                    }
                    $data['DURATION_TYPE'] = 'secs';

                    if (EntityFacade::checkIndustrialOffice()) {
                        if (empty($data['UF_RNS_TYPE_ENTITY'])) {
                            $dictItem = HLBlockHelper::getList('b_hlsys_entities', ['ID'], [], 'ID',
                              ['UF_CODE' => $typeMap->getDefaultTypeId()], false);
                            $data['UF_RNS_TYPE_ENTITY'] = $dictItem[0]['ID'];
                        }
                        if (empty($data['UF_RNS_STATUS'])) {
                            $dictItem = HLBlockHelper::getList(
                              'b_hlsys_status_entity', ['ID'], [], 'ID', [
                              'UF_ENTITY_TYPE_BIND' => $typeMap->getDefaultTypeId(),
                              'UF_CODE' => $statusMap->getDefaultStatusId()
                            ],
                              false);
                            $data['UF_RNS_STATUS'] = $dictItem[0]['ID'];
                        }
                    } else {
                        if (empty($data['STATUS'])) {
                            $data['STATUS'] = $statusMap->getDefaultStatusId();
                        }
                    }

                    if (empty($data['RESPONSIBLE_ID'])) {
                        if ($respSettings->isExecutorLoading() && $this->isManualRun() && intval($USER->getID()) > 0) {
                            $data['RESPONSIBLE_ID'] = intval($USER->getID());
                        } else {
                            $data['RESPONSIBLE_ID'] = $respSettings->getDefaultResponsibleId() ?? $defaultUserId;
                        }
                    }
                    if (empty($data['CREATED_BY'])) {
                        if ($respSettings->isAuthorLoading() && $this->isManualRun() && intval($USER->getID()) > 0) {
                            $data['CREATED_BY'] = intval($USER->getID());
                        } else {
                            $data['CREATED_BY'] = $respSettings->getDefaultAuthorId() ?? $defaultUserId;
                        }
                    }
                    if (empty($data['GROUP_ID'])) {
                        $data['GROUP_ID'] = $projectMap->getDefaultEntityId();
                    }
                    if (empty($data['DEADLINE'])) {
                        $value = new DateTime();
                        $value = $value->add("P{$respSettings->getDefaultDeadlineDays()}D");
                        $data['DEADLINE'] = $value;
                    }

                    $tags = [];
                    $relatedTasks = [];
                    $comments = [];
                    $timeSpent = null;

                    if (!empty($data['TAGS'])) {
                        $tags = $data['TAGS'];
                        unset($data['TAGS']);
                    }
                    if (!empty($data['RELATED_TASKS'])) {
                        $relatedTasks = $data['RELATED_TASKS'];
                        unset($data['RELATED_TASKS']);
                    }
                    if (!empty($data['COMMENTS'])) {
                        $comments = $data['COMMENTS'];
                        unset($data['COMMENTS']);
                    }
                    if (!$this->isManualRun()) {
                        $USER->SetParam('USER_ID', $data['RESPONSIBLE_ID']);
                    }

                    if (!empty($data['TIME_ESTIMATE'])) {
                        $data['ALLOW_TIME_TRACKING'] = 'Y';
                        $data['TIME_ESTIMATE'] = intval($data['TIME_ESTIMATE']);

                        if (!empty($data['GROUP_ID'])) {
                            $projectId = $data['GROUP_ID'];
                            if (!array_key_exists($projectId, $projectTime)) {
                                $project = \CSocNetGroup::getById($projectId);
                                $projectTime[$projectId] = [
                                  'name' => $project['NAME'],
                                  'duration' => $project['UF_RNS_PROJECT_DURATION'] ?? 0,
                                  'timeEstimated' => 0
                                ];
                            }
                            $projectTime[$projectId]['timeEstimated'] += $data['TIME_ESTIMATE'];
                        }
                    }

                    $taskData = CTasks::GetList([], [
                      '=UF_TASK_SOURCE' => $this->sourceId,
                      '=UF_EXTERNAL_ID' => $entityId,
                      '=ZOMBIE' => 'N',
                      'CHECK_PERMISSIONS' => 'N'
                    ], ['*', 'UF_*'])->Fetch();
                    $task = $taskData ? Task::makeInstanceFromSource($taskData, $data['RESPONSIBLE_ID']) : null;

                    $isNew = !$task;

                    if ($isNew) {
                        $task = new Task($data);
                    } else {
                        $this->deleteTaskMembers($task->getId());
                        $task->setData($data);
                    }

                    $result = $task->save();

                    if ($result->isSuccess()) {

                        if (!empty($tags)) {
                            $taskTags = new CTaskTags();
                            foreach ($tags as $tag) {
                                $res = CTaskTags::GetList([], ['TASK_ID' => $task->getId(), 'NAME' => $tag]);
                                if ($row = $res->Fetch()) {
                                    continue;
                                }
                                $taskTags->add([
                                  'TASK_ID' => $task->getId(),
                                  'USER_ID' => $data['RESPONSIBLE_ID'],
                                  'NAME' => $tag
                                ], $data['RESPONSIBLE_ID']);
                            }
                        }

                        if (!empty($relatedTasks)) {
                            $allRelatedTasks[$task->getId()] = $relatedTasks;
                        }

                        if (!empty($comments)) {
                            $taskItem = CTaskItem::getInstance($task->getId(), $data['RESPONSIBLE_ID']);

                            $commentFields = [
                              'AUTHOR_ID' => $data['CREATED_BY'],
                              'USE_SMILES' => 'N',
                              'AUX' => 'Y'
                            ];

                            foreach ($comments as $comment) {
                                $commentFields['POST_MESSAGE'] = $comment;
                                CTaskCommentItem::add($taskItem, $commentFields);
                            }
                        }

                        if (!empty($data['DURATION_FACT'])) {
                            $timeSpent = intval($data['DURATION_FACT']);
                            $res = CTaskElapsedItem::getList(
                              ['ID' => 'DESC'],
                              ['USER_ID' => $data['RESPONSIBLE_ID'], 'TASK_ID' => $task->getId()],
                              ['ID']
                            );
                            if (!empty($res[0])) {
                                $row = $res[0][0];
                                ElapsedTimeTable::update($row['ID'], [
                                  'fields' => [
                                    'MINUTES' => $timeSpent,
                                    'SECONDS' => $timeSpent * 60,
                                    'SOURCE' => 0
                                  ]
                                ]);
                            } else {
                                ElapsedTimeTable::add([
                                  'CREATED_DATE' => new DateTime(),
                                  'USER_ID' => $data['RESPONSIBLE_ID'],
                                  'TASK_ID' => $task->getId(),
                                  'MINUTES' => $timeSpent,
                                  'SECONDS' => $timeSpent * 60,
                                  'SOURCE' => 0
                                ]);
                            }
                        }

                        $importedTaskIds[] = $task->getId();
                        if ($isNew) {
                            $this->result->objectsAdded++;
                        } else {
                            $this->result->objectsUpdated++;
                        }
                        $this->log(
                          Loc::getMessage(
                            'INTEGRATIONS_PROCESSOR_DB_IMPORT_SUCCESS',
                            ['#SYS#' => $this->systemCode]),
                          $task->getId(),
                          'INFO'
                        );
                    } else {
                        $messages = implode("\n", $result->getErrors()->getMessages());
                        $this->addError($messages);
                        $this->log(
                          Loc::getMessage('INTEGRATIONS_PROCESSOR_DB_IMPORT_ERROR', [
                            '#SYS#' => $this->systemCode,
                            '#ERR#' => $messages
                          ]),
                          $task->getId());
                    }
                } catch (\Throwable $ex) {
                    $this->addError($ex->getMessage());
                    $this->log(
                      Loc::getMessage('INTEGRATIONS_PROCESSOR_DB_IMPORT_ERROR', [
                          '#SYS#' => $this->systemCode,
                          '#ERR#' => $ex->getMessage()
                        ]
                      ),
                      $task ? $task->getId() : null);
                }
            }

            foreach ($allRelatedTasks as $taskId => $relatedTasks) {
                foreach ($relatedTasks as $i => $key) {
                    try {
                        $relTaskId = $provider->getEntityIdByKey($key);
                        $taskData = CTasks::GetList([], [
                          '=UF_TASK_SOURCE' => $this->sourceId,
                          '=UF_EXTERNAL_ID' => $relTaskId,
                          '=ZOMBIE' => 'N',
                          'CHECK_PERMISSIONS' => 'N'
                        ], ['*', 'UF_*'])->Fetch();
                        if (!$taskData) {
                            continue;
                        }
                        $relTask = Task::makeInstanceFromSource($taskData);

                        $dep = new \Bitrix\Tasks\Dispatcher\PublicAction\Task\Dependence();
                        if ($i == 0) {
                            $taskIdFrom = $relTask->getId();
                            $taskIdTo = $taskId;
                        } else {
                            $taskIdTo = $relTask->getId();
                            $taskIdFrom = $taskId;
                        }
                        try {
                            $dep->delete($taskIdFrom, $taskIdTo);
                        } catch (\Throwable $ex) {}

                        $rel = RelatedTable::getList([
                          'filter' => ['=TASK_ID' => $taskIdTo, '=DEPENDS_ON_ID' => $taskIdFrom]
                        ])->fetchAll();
                        if (empty($rel)) {
                            RelatedTable::add(['TASK_ID' => $taskIdTo, 'DEPENDS_ON_ID' => $taskIdFrom]);
                        }

                        $dep->add($taskIdFrom, $taskIdTo, ProjectDependenceTable::LINK_TYPE_FINISH_START);
                        $messages = $dep->getErrors()->getMessages();
                        if (!empty($messages)) {
                            $message = implode("\n", $messages);
                            $this->addError($message . " [{$taskIdFrom}] => [{$taskIdTo}]");
                            $this->log($message, $taskIdFrom);
                        }
                    } catch (\Bitrix\Tasks\Exception $ex) {
                        $message = implode("\n", $ex->getErrors());
                        $this->addError($message . " [{$taskIdFrom}] => [{$taskIdTo}]");
                        $this->log($message, $taskIdFrom);
                    } catch (\Throwable $ex) {
                        $this->addError($ex->getMessage());
                        $this->log($ex->getMessage(), $taskIdFrom);
                    }
                }
            }

            foreach ($projectTime as $projectId => $entry) {
                if (!$entry['duration']) {
                    continue;
                }
                $tasksTime = 0;
                $res = CTasks::GetList([], [
                  'GROUP_ID' => $projectId,
                  '!ID' => $importedTaskIds,
                  'ZOMBIE' => 'N',
                  '!TIME_ESTIMATE' => false
                ], ['ID', 'TIME_ESTIMATE']);
                while ($row = $res->GetNext()) {
                    $tasksTime += intval($row['TIME_ESTIMATE']);
                }
                $tasksTime += $entry['timeEstimated'];
                if ($tasksTime > $entry['duration']) {
                    $message = Loc::getMessage('INTEGRATIONS_PROCESSOR_DB_TIME_OVERFLOW', [
                      '#PROJ_NAME#' => $entry['name'],
                      '#TASK_TIME#' => ImportHelper::formatTime($tasksTime),
                      '#PROJ_TIME#' => ImportHelper::formatTime($entry['duration'])
                    ]);
                    $this->addError($message);
                    $this->log($message, $projectId, 'NOTICE');
                }
            }

            $this->result->success = empty($this->result->errors);
        } catch (\Throwable $ex) {
            $this->addError($ex->getMessage());
            $this->log($ex->getMessage(), $this->systemCode);
        }
    }
}
