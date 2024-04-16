<?php

namespace RNS\Integrations\Processors;

use Bitrix\Main\Type\DateTime;
use Bitrix\Tasks\Internals\Task\MemberTable;
use CEventLog;
use CUser;
use RNS\Integrations\Helpers\HLBlockHelper;
use RNS\Integrations\Models\IntegrationSettings;
use RNS\Integrations\Models\Mapping;
use RNS\Integrations\Models\IntegrationOptionsTableWrapper;

/**
 * Реализация общего функционала и для импорта и для экспорта данных.
 * @package RNS\Integrations\Processors
 */
abstract class DataTransferBase
{
    /** @var string */
    protected $systemCode;
    /** @var string */
    protected $exchangeTypeCode;
    /** @var string */
    protected $name;

    /** @var IntegrationSettings */
    protected $integrationOptions;

    protected $options;

    /** @var Mapping */
    protected $mapping;

    protected $sourceId;

    protected $params = [];

    protected $lastOperationDate;

    /** @var DataTransferResult */
    protected $result;
    /** @var bool */
    protected $throwExceptions = false;

    /**
     * @param int $optionsId
     * @param array $params
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function run(int $optionsId, array $params = [])
    {
        $this->initialize($optionsId, $params);

        $this->execute();

        IntegrationOptionsTableWrapper::setLastOperationDate($optionsId, DateTime::createFromTimestamp(time()));

        if (!$this->result->success && $this->throwExceptions) {
            throw new \Exception(implode("\n", $this->result->errors));
        }
    }

    public function getCapabilities()
    {
        return [];
    }

    /**
     * @return DataTransferResult
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param string $errorText
     * @return string
     */
    protected function addError(string $errorText)
    {
        $this->result->errors[] = $errorText;
        return $errorText;
    }

    /**
     * @param int $optionsId
     * @param array $params
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    protected function initialize(int $optionsId, array $params = [])
    {
        global $USER;
        if (!(isset($USER) && $USER instanceof CUser)) {
            $USER = new CUser();
        }

        $obj = IntegrationOptionsTableWrapper::getById($optionsId);

        $this->systemCode = $obj->getSystemCode();
        $this->exchangeTypeCode = $obj->getExchangeTypeCode();
        $this->name = $obj->getName();
        $this->options = $obj->getOptions();
        $this->mapping = $obj->getMapping();
        $this->lastOperationDate = $obj->getLastOperationDate();
        $this->params = $params;

        $this->integrationOptions = new IntegrationSettings($this->systemCode);

        $res = HLBlockHelper::getList('b_hlsys_task_source',  ['ID'], [], 'ID',
          ['UF_XML_ID' => strtoupper($this->systemCode)], false);
        if (empty($res)) {
            throw new \Exception('Не найдена запись в справочнике источников задачи для системы ' . $this->systemCode);
        }
        $this->sourceId = $res[0]['ID'];
    }

    /**
     * @param string $message
     * @param mixed $itemId
     * @param string $severity
     * @return bool
     */
    protected function log(string $message, $itemId = null, string $severity = 'ERROR')
    {
        try {
            CEventLog::Add([
              'SEVERITY' => $severity,
              'AUDIT_TYPE_ID' => 'IMPORT_TASK',
              'MODULE_ID' => 'rns.integrations',
              'ITEM_ID' => $itemId ? strval($itemId) : '',
              'DESCRIPTION' => $message
            ]);

            HLBlockHelper::save('ImportLog', [
              'UF_DATETIME' => DateTime::createFromTimestamp(time()),
              'UF_SEVERITY' => $severity,
              'UF_ENTITY_ID' => is_int($itemId) ? (int)$itemId : null,
              'UF_SOURCE_ID' => $this->sourceId,
              'UF_MESSAGE' => $message
            ]);

            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }

    /**
     * @param $taskId
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    protected function deleteTaskMembers($taskId)
    {
        $list = MemberTable::getList([
          'filter' => ['TASK_ID' => $taskId]
        ]);
        while ($item = $list->fetch())
        {
            MemberTable::delete($item);
        }
    }

    /**
     * @return string
     */
    public function getSystemCode(): string
    {
        return $this->systemCode;
    }

    /**
     * @param string $systemCode
     * @return DataTransferBase
     */
    public function setSystemCode(string $systemCode): DataTransferBase
    {
        $this->systemCode = $systemCode;
        return $this;
    }

    /**
     * @return bool
     */
    public function isThrowExceptions(): bool
    {
        return $this->throwExceptions;
    }

    /**
     * @param bool $throwExceptions
     * @return DataTransferBase
     */
    public function setThrowExceptions(bool $throwExceptions): DataTransferBase
    {
        $this->throwExceptions = $throwExceptions;
        return $this;
    }

    protected function isManualRun()
    {
        return !empty($this->params['isManual']) && $this->params['isManual'] == true;
    }

    protected abstract function execute();
}
