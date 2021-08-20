<?php

namespace Serv;


use Bitrix\Main,
    Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class ProductRowModTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_crm_product_row';
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
                'title' => Loc::getMessage('PRODUCT_ROW_ENTITY_ID_FIELD'),
            ),
            'MEASURE_CODE' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('PRODUCT_ROW_ENTITY_MEASURE_CODE_FIELD'),
            ),
        );
    }
}