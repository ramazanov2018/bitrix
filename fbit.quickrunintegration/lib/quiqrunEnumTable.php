<?php

namespace Fbit\Quickrunintegration;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\StringField;
Loc::loadMessages(__FILE__);

/**
 * Class FieldEnumTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> USER_FIELD_ID int optional
 * <li> VALUE string(255) mandatory
 * </ul>
 *
 * @package Bitrix\User
 **/


class quiqrunEnumTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_user_field_enum';
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
                    'title' => Loc::getMessage('FIELD_ENUM_ENTITY_ID_FIELD')
                ]
            ),
            new IntegerField(
                'USER_FIELD_ID',
                [
                    'title' => Loc::getMessage('FIELD_ENUM_ENTITY_USER_FIELD_ID_FIELD')
                ]
            ),
            new StringField(
                'VALUE',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateValue'],
                    'title' => Loc::getMessage('FIELD_ENUM_ENTITY_VALUE_FIELD')
                ]
            ),
        ];
    }
}