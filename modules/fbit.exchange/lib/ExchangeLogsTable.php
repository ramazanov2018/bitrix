<?php
namespace Fbit\Exchange;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\DateField,
    Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\TextField;

Loc::loadMessages(__FILE__);

/**
 * Class ExchangeLogsTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> UF_DATE_EXCHANGE date optional
 * <li> UF_SERVICE_NAME text optional
 * <li> UF_ERROR_DESC text optional
 * </ul>
 *
 * @package Bitrix\Fbit
 **/

class ExchangeLogsTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {

        return HLBlockMigration::$HLBlockTable;
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            new IntegerField(
                'ID',
                [
                    'primary' => true,
                    'autocomplete' => true,
                    'title' => Loc::getMessage('EXCHANGE_LOGS_ENTITY_ID_FIELD')
                ]
            ),
            new DateField(
                'UF_DATE_EXCHANGE',
                [
                    'title' => Loc::getMessage('EXCHANGE_LOGS_ENTITY_UF_DATE_EXCHANGE_FIELD')
                ]
            ),
            new TextField(
                'UF_SERVICE_NAME',
                [
                    'title' => Loc::getMessage('EXCHANGE_LOGS_ENTITY_UF_SERVICE_NAME_FIELD')
                ]
            ),
            new TextField(
                'UF_ERROR_DESC',
                [
                    'title' => Loc::getMessage('EXCHANGE_LOGS_ENTITY_UF_ERROR_DESC_FIELD')
                ]
            ),
        ];
    }
}

/*
 * $MESS["EXCHANGE_LOGS_ENTITY_ID_FIELD"] = "";
$MESS["EXCHANGE_LOGS_ENTITY_UF_DATE_EXCHANGE_FIELD"] = "";
$MESS["EXCHANGE_LOGS_ENTITY_UF_SERVICE_NAME_FIELD"] = "";
$MESS["EXCHANGE_LOGS_ENTITY_UF_ERROR_DESC_FIELD"] = "";
 */