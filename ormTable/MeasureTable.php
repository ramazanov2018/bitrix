<?php

namespace Serv;



use Bitrix\Main,
    Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class MeasureTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> CODE int mandatory
 * <li> MEASURE_TITLE string(500) optional
 * @package Bitrix\Catalog
 **/

class MeasureTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_catalog_measure';
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
                'title' => Loc::getMessage('MEASURE_ENTITY_ID_FIELD'),
            ),
            'CODE' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => Loc::getMessage('MEASURE_ENTITY_CODE_FIELD'),
            ),
            'MEASURE_TITLE' => array(
                'data_type' => 'string',
                'title' => Loc::getMessage('MEASURE_ENTITY_MEASURE_TITLE_FIELD'),
            ),
        );
    }
}