<?php
namespace Serv\Register;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);


class RegisterTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'cost_register';
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
				'title' => Loc::getMessage('REGISTER_ENTITY_ID_FIELD'),
			),
			'UF_DEAL' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('REGISTER_ENTITY_UF_DEAL_FIELD'),
			),
			'UF_PRODUCT_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('REGISTER_ENTITY_UF_PRODUCT_ID_FIELD'),
			),
			'UF_OWNER_ID' => array(
				'data_type' => 'float',
				'title' => Loc::getMessage('REGISTER_ENTITY_UF_OWNER_ID_FIELD'),
			),
			'UF_TRANS_TAX_PLAN' => array(
				'data_type' => 'float',
				'title' => Loc::getMessage('REGISTER_ENTITY_UF_TRANS_TAX_PLAN_FIELD'),
			),
			'UF_TRANS_TAX_FACT' => array(
				'data_type' => 'float',
				'title' => Loc::getMessage('REGISTER_ENTITY_UF_TRANS_TAX_FACT_FIELD'),
			),
			'UF_TARA_PLAN' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('REGISTER_ENTITY_UF_TARA_PLAN_FIELD'),
			),
			'UF_COUNT_PLAN' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('REGISTER_ENTITY_UF_COUNT_PLAN_FIELD'),
			),
			'UF_TARA_FACT' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('REGISTER_ENTITY_UF_TARA_FACT_FIELD'),
			),
			'UF_OTHER_RATE_PLAN' => array(
				'data_type' => 'float',
				'title' => Loc::getMessage('REGISTER_ENTITY_UF_OTHER_RATE_PLAN_FIELD'),
			),
			'UF_EFFECT_PRICE_PLAN' => array(
				'data_type' => 'float',
				'title' => Loc::getMessage('REGISTER_ENTITY_UF_EFFECT_PRICE_PLAN_FIELD'),
			),
			'UF_EFFECT_PRICE_FACT' => array(
				'data_type' => 'float',
				'title' => Loc::getMessage('REGISTER_ENTITY_UF_EFFECT_PRICE_FACT_FIELD'),
			),
			'UF_OTHER_RATE_FACT' => array(
				'data_type' => 'float',
				'title' => Loc::getMessage('REGISTER_ENTITY_UF_OTHER_RATE_FACT_FIELD'),
			),
			'UF_PRICE_PLAN' => array(
				'data_type' => 'float',
				'title' => Loc::getMessage('REGISTER_ENTITY_UF_PRICE_PLAN_FIELD'),
			),
			'UF_SUMM_PLAN' => array(
				'data_type' => 'float',
				'title' => Loc::getMessage('REGISTER_ENTITY_UF_SUMM_PLAN_FIELD'),
			),
			'UF_COUNT_PLAN' => array(
				'data_type' => 'float',
				'title' => Loc::getMessage('REGISTER_ENTITY_UF_COUNT_PLAN_FIELD'),
			),
			'UF_TRANS_CUR_FACT' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('REGISTER_ENTITY_UF_TRANS_CUR_FACT_FIELD'),
			),
			'UF_TRANS_CUR_PLAN' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('REGISTER_ENTITY_UF_TRANS_CUR_PLAN_FIELD'),
			),
		);
	}
}