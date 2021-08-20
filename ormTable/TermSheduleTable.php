<?php 
namespace Serv;
use Bitrix\Main,
    Bitrix\Main\Localization\Loc;
    Loc::loadMessages(__FILE__);



class TermSheduleTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_iblock_element_prop_s42';
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
                'title' => Loc::getMessage('ELEMENT_PROP_S42_ENTITY_IBLOCK_ELEMENT_ID_FIELD'),
            ),
            'PROPERTY_331' => array(
                'data_type' => 'text',
                'title' => Loc::getMessage('ELEMENT_PROP_S42_ENTITY_PROPERTY_331_FIELD'),
            ),
            'PROPERTY_332' => array(
                'data_type' => 'text',
                'title' => Loc::getMessage('ELEMENT_PROP_S42_ENTITY_PROPERTY_332_FIELD'),
            ),
            'PROPERTY_402' => array(
                'data_type' => 'float',
                'title' => Loc::getMessage('ELEMENT_PROP_S42_ENTITY_PROPERTY_402_FIELD'),
            ),
            'PROPERTY_403' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('ELEMENT_PROP_S42_ENTITY_PROPERTY_403_FIELD'),
            ),
            'PROPERTY_404' => array(
                'data_type' => 'float',
                'title' => Loc::getMessage('ELEMENT_PROP_S42_ENTITY_PROPERTY_404_FIELD'),
            ),
            'PROPERTY_405' => array(
                'data_type' => 'float',
                'title' => Loc::getMessage('ELEMENT_PROP_S42_ENTITY_PROPERTY_405_FIELD'),
            ),
        );
    }
}

?>