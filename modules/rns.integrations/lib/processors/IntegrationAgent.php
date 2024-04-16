<?php

namespace RNS\Integrations\Processors;

use RNS\Integrations\Models\IntegrationOptionsTableWrapper;
use RNS\Integrations\IntegrationOptionsTable;
use RNS\Integrations\Helpers\ImportHelper;

class IntegrationAgent
{
    /** @var DataTransferResult */
    public static $result;

    /**
     * @param int $id
     * @param array $params
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function run(int $id, array $params = [])
    {
        ImportHelper::automaticUserMapping($id);
        $integrationOptions = IntegrationOptionsTableWrapper::getById($id);
        $exchangeTypeCode = $integrationOptions->getExchangeTypeCode();
        $className = $integrationOptions->getDirection() == IntegrationOptionsTable::DIRECTION_IMPORT ? 'Import' : 'Export';

        $processorClassPath = $_SERVER['DOCUMENT_ROOT'] . '/local/modules/rns.integrations/lib/processors/' .
            $exchangeTypeCode . '/' . $className . '.php';

        include_once($processorClassPath);

        $processorClass = "RNS\\Integrations\\Processors\\{$exchangeTypeCode}\\{$className}";

        /** @var DataTransferBase $processor */
        $processor = new $processorClass();

        $processor->run($id, $params);
        static::$result = $processor->getResult();

        return "\\RNS\\Integrations\\Processors\\IntegrationAgent::run({$id});";
    }
}
