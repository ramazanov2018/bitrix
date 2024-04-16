<?php /** @noinspection ALL */

namespace RNS\Integrations\Processors\Files;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;
use Bitrix\Tasks\Internals\Task\ProjectDependenceTable;
use Bitrix\Tasks\Internals\Task\RelatedTable;
use Bitrix\Tasks\Item\Task;
use CTasks;
use CURLFile;
use RNS\Integrations\Helpers\EntityFacade;
use RNS\Integrations\Helpers\HLBlockHelper;
use RNS\Integrations\Helpers\ImportHelper;
use RNS\Integrations\Helpers\TaskHelper;
use RNS\Integrations\Processors\DataTransferBase;
use RNS\Integrations\Processors\DataTransferResult;
use RNS\Integrations\IntegrationOptionsTable;

class Import extends DataTransferBase
{
    /** @var string */
    private $format;
    /** @var string */
    private $fileName;
    /** @var integer */
    private $projectId;
    /** @var bool */
    private $fakeImport;
    /** @var array */
    private $headers = [];
    /** @var array */
    private $data = [];
    private $sampleDataLimit = 20;
    /** @var int */
    private $filePos = 0;
    /** @var int */
    private $maxExecutionTime = 0;
    /** @var bool */
    private $allEntitiesLoaded = false;

    public function __construct()
    {
        Loc::loadMessages(__FILE__);
    }

    /**
     * @param string $format
     * @param string|null $fileName
     * @param string|null $systemCode
     * @param int $projectId
     * @param bool $fake
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function importFile(string $format, ?string $fileName, ?string $systemCode, int $projectId, bool $fake = false)
    {
        $this->format = $format;
        if ($fileName) {
            $this->fileName = $fileName;
        }
        if ($systemCode) {
            $this->systemCode = $systemCode;
        } else {
            $this->systemCode = ImportHelper::getSystemCodeByFormat($this->format);
        }

        $this->projectId = $projectId;
        $this->fakeImport = $fake;

        $res = IntegrationOptionsTable::getList([
            'select' => ['ID'],
            'filter' => ['=SYSTEM.CODE' => $this->systemCode]
        ]);
        if ($row = $res->fetch()) {
            $this->run($row['ID']);
        }
    }

    /**
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function prepareData()
    {
        $res = IntegrationOptionsTable::getList(['select' => ['ID'],
          'filter' => ['=SYSTEM.CODE' => $this->systemCode]]);
        if ($row = $res->fetch()) {
            $this->initialize($row['ID']);
        }
        switch ($this->format) {
            case 'mpp':
                $entities = $this->convertFile($this->fileName);
                break;
        }

        file_put_contents($this->getTempPath(), json_encode($entities, JSON_UNESCAPED_UNICODE));
        $count = 0;
        $taskMaxLevel = $this->options->getTaskLevel();
        foreach ($entities as $index => $entity) {
            if (($entity['OutlineLevel'] ?? 0) <= $taskMaxLevel) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * @throws \Exception
     */
    protected function execute()
    {
        global $USER;

        $this->result = new DataTransferResult();

        try {
            $size = round(filesize($this->fileName) / 1024 / 1024, 0);
            $maxSize = $this->options->getFileMaxSize();
            if ($maxSize > 0 && $size > $maxSize) {
                throw new FileValidationError(
                  Loc::getMessage('INTEGRATIONS_PROCESSOR_FILE_IMPORT_FILE_TOO_LARGE', [
                    '#SIZE#' => $size,
                    '#MAX#' => $maxSize
                  ])
                );
            }

            Loader::includeModule('tasks');
            Loader::includeModule('socialnetwork');

            $keyFieldName = $this->integrationOptions->getEntityKeyField();

            $propMapItems = $this->mapping->getEntityPropertyMap()->getItems();

            $valueMapping = json_decode($this->integrationOptions->getValueMapping() ?? '{}', true);

            $statusMap = $this->mapping->getEntityStatusMap();
            $typeMap = $this->mapping->getEntityTypeMap();

            $statuses = EntityFacade::getEntityStatuses('TASK');
            $internalStatusId = EntityFacade::getStatusMap($statusMap->getDefaultStatusId());

            $taskMaxLevel = $this->options->getTaskLevel();

            $defaultDeadlineDays = $this->mapping->getResponsibleSettings()->getDefaultDeadlineDays() ?? 0;

            if ($this->fakeImport) {
                switch ($this->format) {
                    case 'mpp':
                        try{
                            $entities = $this->convertFile($this->fileName);
                        }catch (\Exception $ex){
                            throw new FileValidationError($ex->getMessage());
                        }
                        break;
                }
            } else {
                $content = file_get_contents($this->getTempPath());
                $entities = json_decode($content, true);
                if ($this->filePos > 0) {
                    $entities = array_slice($entities, $this->filePos, null, true);
                }
            }

            if (!$this->fakeImport && file_exists($this->fileName)) {
                unlink($this->fileName);
            }

            $count = count($entities);
            $maxCount = $this->options->getTaskMaxCount();
            if ($maxCount > 0 && $count > $maxCount) {
                throw new FileValidationError(
                  Loc::getMessage('INTEGRATIONS_PROCESSOR_FILE_IMPORT_TOO_MANY_TASKS', [
                    '#COUNT#' => $count,
                    '#MAX#' => $maxCount
                  ])
                );
            }

            $entityProps = [];
            if ($this->fakeImport) {
                $entityProps = EntityFacade::getEntityProperties();
                $idx = array_search('RESPONSIBLE_ID', $entityProps['REFERENCE_ID']);
                $entityProps['REFERENCE'][$idx] = Loc::getMessage('INTEGRATIONS_PROCESSOR_FILE_IMPORT_EXECUTOR');
            }

            $this->allEntitiesLoaded = true;

            $startTime = getmicrotime();

            $count = 0;

            foreach ($entities as $index => $entity) {
                if (!$this->fakeImport) {
                    $entity['Id'] = $this->generateExternalId($entity['Id']);

                    if ($entity['ParentId'] > 0)
                        $entity['ParentId'] = $this->generateExternalId($entity['ParentId']);

                    if ($entity['PredecessorId'] > 0)
                        $entity['PredecessorId'] = $this->generateExternalId($entity['PredecessorId']);
                }

                try {
                    $level = $entity['OutlineLevel'] ?? 0;
                    if ($level > $taskMaxLevel) {
                        continue;
                    }

                    $key = $entity[$keyFieldName] ?? '';

                    foreach ($propMapItems as $propMapItem) {
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
                            if (isset($mapping[$value])) {
                                $value = $mapping[$value];
                            }
                        }

                        if ($this->fakeImport && $index == 0) {
                            $propIndex = array_search($destProp, $entityProps['REFERENCE_ID']);
                            $this->headers[$destProp] = $entityProps['REFERENCE'][$propIndex];
                        }

                        if ($destProp == 'GROUP_ID') {
                            $res = \CSocNetGroup::GetList([], ['=NAME' => $value], false, false, ['ID', 'NAME']);
                            if ($project = $res->Fetch()) {
                                $value = $project['ID'];
                            } else {
                                $value = $this->projectId;
                            }
                        }

                        if ($destProp == 'PARENT_ID') {
                            $parentTask = CTasks::GetList([], [
                              '=UF_TASK_SOURCE' => $this->sourceId,
                              '=UF_EXTERNAL_ID' => $value,
                              '=ZOMBIE' => 'N',
                              'CHECK_PERMISSIONS' => 'N'
                            ], ['ID'])->Fetch();
                            if ($parentTask) {
                                $value = $parentTask['ID'];
                            }
                            if ($value == 0) {
                                $value = null;
                            }
                        }

                        if ($destProp == 'DEADLINE' && $defaultDeadlineDays > 0 && empty($value)) {
                            $dt = strtotime("+ {$defaultDeadlineDays} days");
                            $value = date('Y-m-d 23:59:59', $dt);
                        }

                        if ($destProp == 'START_DATE_PLAN' || $destProp == 'END_DATE_PLAN' || $destProp == 'DEADLINE' ||
                          $destProp == 'DATE_START' || $destProp == 'CREATED_DATE' || $destProp == 'CHANGED_DATE' ||
                          $destProp == 'CLOSED_DATE') {
                            $value = DateTime::createFromTimestamp(strtotime($value));
                        }

                        $data[$destProp] = $value;
                    }

                    if (!$this->fakeImport) {
                        $data['UF_TASK_SOURCE'] = $this->sourceId;
                    }

                    if ($this->fakeImport) {
                        $project = \CSocNetGroup::getById($data['GROUP_ID']);
                        $data['GROUP_ID'] = $project['NAME'];
                        if ($index == 0) {
                            $propIndex = array_search('GROUP_ID', $entityProps['REFERENCE_ID']);
                            $this->headers['GROUP_ID'] = $entityProps['REFERENCE'][$propIndex];
                        }
                    }

                    $data['CREATED_BY'] = $USER->getID();
                    if (!empty($entity['Author'])) {
                        $res = UserTable::getList([
                          'select' => ['ID'],
                          'filter' => ['=EMAIL' => $entity['Author']]
                        ]);
                        if ($user = $res->fetch()) {
                            $data['CREATED_BY'] = $user['ID'];
                        }
                    }

                    if ($this->fakeImport) {
                        $user = UserTable::getById($data['CREATED_BY'])->fetch();
                        $data['CREATED_BY'] = $user['LAST_NAME'] . ' ' . $user['NAME'] .
                          (!empty($user['SECOND_NAME']) ? ' ' . $user['SECOND_NAME'] : '');
                        if ($index == 0) {
                            $propIndex = array_search('CREATED_BY', $entityProps['REFERENCE_ID']);
                            $this->headers['CREATED_BY'] = $entityProps['REFERENCE'][$propIndex];
                        }
                    }

                    $data['RESPONSIBLE_ID'] = $USER->getID();
                    if (!empty($entity['Responsible'])) {
                        $res = UserTable::getList([
                          'select' => ['ID'],
                          'filter' => ['=EMAIL' => $entity['Responsible']]
                        ]);
                        if ($user = $res->fetch()) {
                            $data['RESPONSIBLE_ID'] = $user['ID'];
                        }
                    }

                    if ($this->fakeImport) {
                        $user = UserTable::getById($data['RESPONSIBLE_ID'])->fetch();
                        $data['RESPONSIBLE_ID'] = $user['LAST_NAME'] . ' ' . $user['NAME'] .
                          (!empty($user['SECOND_NAME']) ? ' ' . $user['SECOND_NAME'] : '');
                        if ($index == 0) {
                            $propIndex = array_search('RESPONSIBLE_ID', $entityProps['REFERENCE_ID']);
                            $this->headers['RESPONSIBLE_ID'] = $entityProps['REFERENCE'][$propIndex];
                        }
                    }

                    if (EntityFacade::checkIndustrialOffice()) {
                        $dictItem = HLBlockHelper::getList('b_hlsys_entities', ['ID', 'UF_NAME'], [], 'ID',
                          ['UF_CODE' => $typeMap->getDefaultTypeId()], false);
                        if (!empty($dictItem)) {
                            $data['UF_RNS_TYPE_ENTITY'] = $dictItem[0]['ID'];
                        }

                        if ($this->fakeImport) {
                            $data['UF_RNS_TYPE_ENTITY'] = $dictItem[0]['UF_NAME'];
                            if ($index == 0) {
                                $propIndex = array_search('UF_RNS_TYPE_ENTITY', $entityProps['REFERENCE_ID']);
                                $this->headers['UF_RNS_TYPE_ENTITY'] = $entityProps['REFERENCE'][$propIndex];
                            }
                        }

                        $dictItem = HLBlockHelper::getList('b_hlsys_status_entity', ['ID', 'UF_RUS_NAME'], [], 'ID',
                          ['UF_ENTITY_TYPE_BIND' => $typeMap->getDefaultTypeId(), 'UF_CODE' => $statusMap->getDefaultStatusId()],
                          false);
                        if (!empty($dictItem)) {
                            $data['UF_RNS_STATUS'] = $dictItem[0]['ID'];
                        }

                        if ($this->fakeImport) {
                            $data['UF_RNS_STATUS'] = $dictItem[0]['UF_RUS_NAME'];
                            if ($index == 0) {
                                $propIndex = array_search('UF_RNS_STATUS', $entityProps['REFERENCE_ID']);
                                $this->headers['UF_RNS_STATUS'] = $entityProps['REFERENCE'][$propIndex];
                            }
                        }
                    } else {
                        $data['STATUS'] = $internalStatusId;
                        if ($this->fakeImport) {
                            $this->headers['STATUS'] = 'Статус';
                            foreach ($statuses['REFERENCE_ID'] as $i => $statusId) {
                                if ($statusId == $statusMap->getDefaultStatusId()) {
                                    $data['STATUS'] = $statuses['REFERENCE'][$i];
                                }
                            }
                        }
                    }

                    if (empty($data['GROUP_ID'])) {
                        $data['GROUP_ID'] = $this->projectId;
                        if ($this->fakeImport) {
                            $project = \CSocNetGroup::getById($data['GROUP_ID']);
                            $data['GROUP_ID'] = $project['NAME'];
                        }
                    }
                    if (empty($data['DEADLINE'])) {
                        $value = new DateTime();
                        $value = $value->add("P{$defaultDeadlineDays}D");
                        $data['DEADLINE'] = $value;
                    }

                    if ($entity['Milestone'] && empty($data['END_DATE_PLAN'])) {
                        $data['END_DATE_PLAN'] = $data['START_DATE_PLAN'] ?? $data['DATE_START'];
                    }

                    if (!$this->fakeImport) {
                        $data['UF_EXTERNAL_ID'] = $entity['Id'];
                        $taskData = !empty($key)
                          ? CTasks::GetList([], [
                            '=UF_TASK_SOURCE' => $this->sourceId,
                            '=UF_EXTERNAL_ID' => $key,
                            '=ZOMBIE' => 'N',
                            'CHECK_PERMISSIONS' => 'N'
                          ], ['*', 'UF_*'])->Fetch()
                          : null;

                        $isNew = !$taskData;

                        if ($isNew) {
                            $task = new Task($data);
                        } else {
                            $task = Task::makeInstanceFromSource($taskData);
                            $this->deleteTaskMembers($task->getId());
                            $task->setData($data);
                        }

                        $result = $task->save();

                        if ($result->isSuccess()) {
                            if ($isNew) {
                                $filename = basename($this->fileName);
                                $comment = Loc::getMessage('INTEGRATIONS_PROCESSOR_FILE_IMPORT_SOURCE', [
                                  '#SRC#' => $filename
                                ]);
                                if ($this->systemCode == 'ms_project') {
                                    $comment .= Loc::getMessage('INTEGRATIONS_PROCESSOR_FILE_IMPORT_NUM', [
                                        '#NUM#' => $entity['OutlineNumber']
                                      ]
                                    );
                                }
                                TaskHelper::addComment($task->getId(), $comment);
                            }

                            $this->addRelatedTask($task->getId(), $entity);

                            if ($isNew) {
                                $this->result->objectsAdded++;
                            } else {
                                $this->result->objectsUpdated++;
                            }

                            $this->log(
                              Loc::getMessage('INTEGRATIONS_PROCESSOR_FILE_IMPORT_SUCCESS', [
                                  '#SYS#' => $this->systemCode
                                ]
                              ),
                              $task->getId(),
                              'INFO'
                            );
                        } else {
                            $messages = implode("\n", $result->getErrors()->getMessages());
                            $this->addError(($index + 1) . ': ' . $messages);
                            $this->log(
                              Loc::getMessage('INTEGRATIONS_PROCESSOR_FILE_IMPORT_ERROR', [
                                  '#SYS#' => $this->systemCode,
                                  '#ERR#' => $messages
                                ]
                              ),
                              $task ? $task->getId() : null
                            );
                        }
                    } else {
                        $this->data[] = $data;
                        if ($count >= $this->sampleDataLimit - 1) {
                            break;
                        }
                        $count++;
                    }
                } catch (\Exception $ex) {
                    $this->addError(($index + 1) . ': ' . $ex->getMessage());
                    $this->log(
                      Loc::getMessage('INTEGRATIONS_PROCESSOR_FILE_IMPORT_ERROR', [
                          '#SYS#' => $this->systemCode,
                          '#ERR#' => $ex->getMessage()
                        ]
                      ),
                      $task ? $task->getId() : null
                    );
                }
                if (!$this->fakeImport && ($this->maxExecutionTime > 0) && ((getmicrotime() - $startTime) > $this->maxExecutionTime)) {
                    $this->filePos = $index + 1;
                    $this->allEntitiesLoaded = false;
                    break;
                }
            }
            $this->result->success = empty($this->result->errors);
        } catch (FileValidationError $ex) {
            $this->addError($ex->getMessage());
            $this->log($ex->getMessage(), $this->systemCode);
        } catch (\Throwable $ex) {
            $this->addError(
              Loc::getMessage('INTEGRATIONS_PROCESSOR_FILE_IMPORT_BAD_FILE', [
                  '#NAME#' => basename($this->fileName)
                ]
              )
            );
            $this->log($ex->getMessage(), $this->systemCode);
        }
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @param string $format
     * @return Import
     */
    public function setFormat(string $format): Import
    {
        $this->format = $format;
        return $this;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     * @return Import
     */
    public function setFileName(string $fileName): Import
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function getFilePos(): int
    {
        return $this->filePos;
    }

    /**
     * @param int $filePos
     * @return Import
     */
    public function setFilePos(int $filePos): Import
    {
        $this->filePos = $filePos;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxExecutionTime(): int
    {
        return $this->maxExecutionTime;
    }

    /**
     * @param int $maxExecutionTime
     * @return Import
     */
    public function setMaxExecutionTime(int $maxExecutionTime): Import
    {
        $this->maxExecutionTime = $maxExecutionTime;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAllEntitiesLoaded(): bool
    {
        return $this->allEntitiesLoaded;
    }

    /**
     * @param int $taskId
     * @param array $entity
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \TasksException
     * @throws \Exception
     */
    private function addRelatedTask(int $taskId, array $entity)
    {
        if (empty($entity['PredecessorId'])) {
            return true;
        }
        $taskData = CTasks::GetList([], [
          '=UF_TASK_SOURCE' => $this->sourceId,
          '=UF_EXTERNAL_ID' =>  $entity['PredecessorId'],
          '=ZOMBIE' => 'N',
          'CHECK_PERMISSIONS' => 'N'
        ], ['ID'])->Fetch();
        if ($taskData) {
            $rel = RelatedTable::getList([
              'filter' => ['=TASK_ID' => $taskId, '=DEPENDS_ON_ID' => $taskData['ID']]
            ])->fetchAll();
            if (empty($rel)) {
                RelatedTable::add(['TASK_ID' => $taskId, 'DEPENDS_ON_ID' => $taskData['ID']]);
            }

            $dep = new \Bitrix\Tasks\Dispatcher\PublicAction\Task\Dependence();
            try {
                $dep->delete($taskData['ID'], $taskId);
            } catch (\Throwable $ex) {}
            try {
                $dep->add($taskData['ID'], $taskId, $this->convertRelationType($entity['RelationType']));
                return true;
            } catch (\Throwable $ex) {
                return false;
            }
        }
        return false;
    }

    /**
     * @param int $relationType
     * @return mixed
     */
    private function convertRelationType(int $relationType)
    {
        $map = [
          0 => ProjectDependenceTable::LINK_TYPE_FINISH_FINISH,
          1 => ProjectDependenceTable::LINK_TYPE_FINISH_START,
          2 => ProjectDependenceTable::LINK_TYPE_START_FINISH,
          3 => ProjectDependenceTable::LINK_TYPE_START_START
        ];
        return $map[$relationType];
    }

    /**
     * @param string $filePath
     * @return bool|mixed|string
     * @throws \Exception
     */
    private function convertFile(string $filePath)
    {
        if (!extension_loaded('curl')) {
            throw new \Exception(Loc::getMessage('INTEGRATIONS_PROCESSOR_FILE_IMPORT_CURL_REQUIRED'));
        }


        $url = $this->options->getConverterUrl();
        if(empty($url)) {
            throw new \Exception(Loc::getMessage('INTEGRATIONS_PROCESSOR_FILE_IMPORT_NOT_URL'));
        }

        $file = new CURLFile($filePath, 'application/vnd.ms-project', basename($filePath));

        $data = ['file' => $file];

        $curlOptions = [
          CURLOPT_URL => $url,
          CURLOPT_POST => true,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_POSTFIELDS => $data
        ];

        $ch = curl_init();
        try {
            curl_setopt_array($ch, $curlOptions);

            $response = curl_exec($ch);
            if (!curl_errno($ch)) {
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($code == 200) {
                    $response = json_decode($response, true);
                } else {
                    $response = json_decode($response, true);
                    if (!empty($response)) {
                        throw new \Exception($response['Message']);
                    }
                    throw new \Exception(
                      Loc::getMessage('INTEGRATIONS_PROCESSOR_FILE_IMPORT_BAD_FILE', [
                          '#NAME#' => basename($this->fileName)
                        ]
                      )
                    );
                }
            } else {
                throw new \Exception(Loc::getMessage('INTEGRATIONS_PROCESSOR_FILE_IMPORT_INTERNAL_ERROR'));
            }
        } finally {
            curl_close($ch);
        }
        return $response;
    }

    private function getTempPath()
    {
        return $_SERVER['DOCUMENT_ROOT'] . '/upload/' . basename($this->fileName) . '.json';
    }

    private function generateExternalId($id)
    {
        $id = (int)$id;
        return md5($this->fileName.$id);
    }
}
