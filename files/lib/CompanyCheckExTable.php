<?php
namespace Serv\CheckEx;

use Bitrix\Main,
    Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class ExTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> UF_COMPANY_ID int optional
 * </ul>
 *
 * @package Bitrix\Check
 **/

class CompanyCheckExTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'company_check_ex';
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
                'title' => Loc::getMessage('EX_ENTITY_ID_FIELD'),
            ),
            'UF_COMPANY_ID' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('EX_ENTITY_UF_COMPANY_ID_FIELD'),
            ),
        );
    }
}