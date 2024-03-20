<?php

namespace Rns\TestReminder;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\DatetimeField,
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
class TestRemindTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {

        return HLBlockTestRemind::$HLBlockTable;
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
                    'title' => 'ID'
                ]
            ),
            new DatetimeField(
                'UF_DATE_REMIND',
                [
                    'title' => 'Дата напоминании'
                ]
            ),
            new IntegerField(
                'UF_USER_ID',
                [
                    'title' => 'ID пользователя'
                ]
            ),
        ];
    }
}