<?php 
namespace Serv;

use Bitrix\Main,
Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class ControlSalesVolTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_iblock_element_prop_s52';
    }
    
    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            'IBLOCK_ELEMENT_ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'title' => Loc::getMessage('ELEMENT_PROP_S52_ENTITY_IBLOCK_ELEMENT_ID_FIELD'),
            ),
            'PROPERTY_409' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('ELEMENT_PROP_S52_ENTITY_PROPERTY_409_FIELD'),
            ),
            'PROPERTY_410' => array(
                'data_type' => 'float',
                'title' => Loc::getMessage('ELEMENT_PROP_S52_ENTITY_PROPERTY_410_FIELD'),
            ),
            'PROPERTY_412' => array(
                'data_type' => 'text',
                'title' => Loc::getMessage('ELEMENT_PROP_S52_ENTITY_PROPERTY_412_FIELD'),
            ),
            'PROPERTY_413' => array(
                'data_type' => 'text',
                'title' => Loc::getMessage('ELEMENT_PROP_S52_ENTITY_PROPERTY_413_FIELD'),
            ),
            'PROPERTY_414' => array(
                'data_type' => 'float',
                'title' => Loc::getMessage('ELEMENT_PROP_S52_ENTITY_PROPERTY_414_FIELD'),
            ),
        );
    }
}

?>