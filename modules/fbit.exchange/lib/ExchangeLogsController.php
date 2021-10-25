<?php
namespace Fbit\Exchange;
use Bitrix\Main\Type\DateTime;


class ExchangeLogsController
{
    public static function AddLogs($Fields = array())
    {
        $arFields['UF_DATE_EXCHANGE'] = new DateTime();
        $arFields['UF_SERVICE_NAME'] = trim($Fields['UF_SERVICE_NAME']);
        $arFields['UF_ERROR_DESC'] = trim($Fields['UF_ERROR_DESC']);

        $res = ExchangeLogsTable::Add($arFields);

        if($res->isSuccess())
        {
            return $res->getId();
        }

        return false;
    }
}