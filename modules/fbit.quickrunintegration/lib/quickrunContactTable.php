<?php
namespace Fbit\Quickrunintegration;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\StringField;

Loc::loadMessages(__FILE__);

/**
 * Class ContactTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> NAME string(50) optional
 * <li> LAST_NAME string(50) optional
 * <li> SECOND_NAME string(50) optional
 * </ul>
 *
 * @package Bitrix\Crm
 **/
class quickrunContactTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_crm_contact';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        global $DB;
        return [
            new IntegerField(
                'ID',
                [
                    'primary' => true,
                    'autocomplete' => true,
                    'title' => Loc::getMessage('CONTACT_ENTITY_ID_FIELD')
                ]
            ),

            new StringField(
                'NAME',
                [
                    'title' => Loc::getMessage('CONTACT_ENTITY_NAME_FIELD')
                ]
            ),
            new StringField(
                'LAST_NAME',
                [
                    'title' => Loc::getMessage('CONTACT_ENTITY_LAST_NAME_FIELD')
                ]
            ),
            new StringField(
                'SECOND_NAME',
                [
                    'title' => Loc::getMessage('CONTACT_ENTITY_SECOND_NAME_FIELD')
                ]
            ),

            'PHONE' => array(
                'expression' => array(
                    '('.$DB->TopSql(
                        'SELECT GROUP_CONCAT(VALUE) 
                        FROM b_crm_field_multi cont 
                        WHERE cont.ELEMENT_ID = %s AND TYPE_ID = "PHONE" AND ENTITY_ID = "CONTACT"', 0).')', 'ID'
                ),
            ),

        ];
    }

}