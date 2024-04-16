<?php

namespace RNS\Integrations\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use CModule;
use RNS\Integrations\Helpers\HLBlockHelper;
use RNS\Integrations\Helpers\ImportHelper;
use RNS\Integrations\Helpers\TaskHelper;
use RNS\Integrations\Processors\Files\Import;

class Task extends Controller
{
    /**
     * @throws \Bitrix\Main\LoaderException
     * @throws \Exception
     */
    public function statusAction()
    {
        $request = $this->getRequest();

        $tasks = $request->getValues();

        if (is_array($tasks['id'])) {
            $tasks = $this->transposeArray($tasks);
        } else if (!is_array($tasks)) {
            $tasks = [$tasks];
        }

        foreach ($tasks as $task) {
            $data = [];
            $extraInfo = [];
            foreach ($task as $key => $value) {
                switch ($key) {
                    case 'id':
                        $data['UF_TASK_ID'] = $value;
                        break;
                    case 'action':
                        $data['UF_ACTION'] = $value;
                        break;
                    case 'userId':
                        $data['UF_USER'] = $value;
                        break;
                    case 'status':
                        $extraInfo[] = 'Статус: ' . $value;
                        break;
                    case 'duration':
                        $extraInfo[] = 'Длительность: ' . $value . ' ч';
                        break;
                    default:
                        $extraInfo[] = $value;
                        break;
                }
            }
            $data['UF_DATETIME'] = DateTime::createFromTimestamp(time());
            $data['UF_EXTRA_INFO'] = implode("; ", $extraInfo);
            $result = HLBlockHelper::save('TaskLog', $data);
            if (!$result->isSuccess()) {
                throw new \Exception(implode("\n", $result->getErrorMessages()));
            }
        }
    }

    /**
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectException
     * @throws \CTaskAssertException
     * @throws \TasksException
     * @throws \Exception
     */
    public function commentAddAction()
    {
        Loader::includeModule('tasks');

        $request = $this->getRequest();
        $taskId = $request->get('taskId');
        $comment = $request->get('commentText');

        if (!$taskId || !$comment) {
            throw new \Exception('Все параметры метода должны быть указаны.');
        }

        TaskHelper::addComment($taskId, $comment);
    }

    /**
     * @return array|string|null
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function importAction()
    {
        $request = $this->getRequest();
        $importParams = $request->get('importParameters');

        $importer = new Import();
        $importer->setThrowExceptions(false);
        $importer->setSystemCode(ImportHelper::getSystemCodeByFormat($importParams['FORMAT']));
        $importer->setFormat($importParams['FORMAT']);
        $importer->setFileName($importParams['CUSTOM_FILE_PATH']);
        $importer->setFilePos((int)$importParams['FILE_POS']);
        $importer->setMaxExecutionTime($importParams['MAX_EXECUTION_TIME']);

        if ((int)$importParams['FILE_POS'] == 0) {
            $importParams['IMPORTS_TOTAL_COUNT'] = $importer->prepareData();
        }

        $importer->importFile($importParams['FORMAT'],null, null, intval($importParams['PROJECT_ID']));
        $result = $importer->getResult();

        $importParams['FILE_POS'] = $importer->getFilePos();
        $importParams['SUCCESSFUL_IMPORTS'] = $result->objectsAdded + $result->objectsUpdated;
        $importParams['ERROR_IMPORTS'] = count($result->errors);
        $importParams['ERROR_IMPORTS_MESSAGES'] = $result->errors;
        $importParams['ALL_LINES_LOADED'] = $importer->isAllEntitiesLoaded();

        return $importParams;
    }

    function transposeArray($array, $selectKey = false)
    {
        if (!is_array($array)) return false;
        $result = [];
        foreach($array as $key => $value) {
            if (!is_array($value)) return $array;
            if ($selectKey) {
                if (isset($value[$selectKey])) $result[] = $value[$selectKey];
            } else {
                foreach ($value as $key2 => $value2) {
                    $result[$key2][$key] = $value2;
                }
            }
        }
        return $result;
    }
}
