<?php 
namespace Serv;

use Bitrix\Main,
Bitrix\Main\Localization\Loc;

class CrmUtmCompanyTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_utm_crm_company';
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
                'title' => Loc::getMessage('CRM_COMPANY_ENTITY_ID_FIELD'),
            ),
            'VALUE_ID' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => Loc::getMessage('CRM_COMPANY_ENTITY_VALUE_ID_FIELD'),
            ),
            'FIELD_ID' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => Loc::getMessage('CRM_COMPANY_ENTITY_FIELD_ID_FIELD'),
            ),
            'VALUE' => array(
                'data_type' => 'text',
                'title' => Loc::getMessage('CRM_COMPANY_ENTITY_VALUE_FIELD'),
            ),
            'VALUE_INT' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('CRM_COMPANY_ENTITY_VALUE_INT_FIELD'),
            ),
            'VALUE_DOUBLE' => array(
                'data_type' => 'float',
                'title' => Loc::getMessage('CRM_COMPANY_ENTITY_VALUE_DOUBLE_FIELD'),
            ),
            'VALUE_DATE' => array(
                'data_type' => 'datetime',
                'title' => Loc::getMessage('CRM_COMPANY_ENTITY_VALUE_DATE_FIELD'),
            ),
        );
    }
}

?>