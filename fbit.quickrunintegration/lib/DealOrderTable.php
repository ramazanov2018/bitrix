<?php
namespace Fbit\Quickrunintegration;
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\IntegerField;

Loc::loadMessages(__FILE__);

class DealOrderTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_crm_order_deal';
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
                'DEAL_ID',
                [
                    'primary' => true,
                    'title' => Loc::getMessage('ORDER_DEAL_ENTITY_DEAL_ID_FIELD')
                ]
            ),
            new IntegerField(
                'ORDER_ID',
                [
                    'primary' => true,
                    'title' => Loc::getMessage('ORDER_DEAL_ENTITY_ORDER_ID_FIELD')
                ]
            ),

            'ORDER_NUMBER'=> array(
                'expression' => array(
                    '('.$DB->TopSql('SELECT ACCOUNT_NUMBER FROM b_sale_order ord WHERE ord.ID = %s', 1).')', 'ORDER_ID'
                )
            )
        ];
    }
}