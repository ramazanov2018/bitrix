<?php 
namespace Serv;

use Bitrix\Main,
    Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class ReportTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> UF_CATEGORY_ID int optional
 * <li> UF_OWNER_ID int optional
 * <li> UF_COUNT double optional
 * <li> UF_DATE date optional
 * <li> UF_TYPE string optional
 * </ul>
 *
 * @package Bitrix\Report
 **/

class ReserveReportTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'reserve_report';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
                'title' => Loc::getMessage('REPORT_ENTITY_ID_FIELD'),
            ),
            'UF_CATEGORY_ID' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('REPORT_ENTITY_UF_CATEGORY_ID_FIELD'),
            ),
            'UF_OWNER_ID' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('REPORT_ENTITY_UF_OWNER_ID_FIELD'),
            ),
            'UF_COUNT' => array(
                'data_type' => 'float',
                'title' => Loc::getMessage('REPORT_ENTITY_UF_COUNT_FIELD'),
            ),
            'UF_DATE' => array(
                'data_type' => 'date',
                'title' => Loc::getMessage('REPORT_ENTITY_UF_DATE_FIELD'),
            ),
            'UF_TYPE' => array(
                'data_type' => 'text',
                'title' => Loc::getMessage('REPORT_ENTITY_UF_TYPE_FIELD'),
            ),
        );
    }
}