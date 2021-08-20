<?php 
namespace Serv;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

class ProductPropertyTable extends Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_iblock_element_prop_s26';
	}

	public static function getMap()
	{
		return array(
			'IBLOCK_ELEMENT_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'title' => Loc::getMessage('ELEMENT_PROP_S26_ENTITY_IBLOCK_ELEMENT_ID_FIELD'),
			),
			'PROPERTY_160' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('ELEMENT_PROP_S26_ENTITY_PROPERTY_160_FIELD'),
			),
			'PROPERTY_265' => array(
				'data_type' => 'float',
				'title' => Loc::getMessage('ELEMENT_PROP_S26_ENTITY_PROPERTY_265_FIELD'),
			),
			'PROPERTY_266' => array(
				'data_type' => 'float',
				'title' => Loc::getMessage('ELEMENT_PROP_S26_ENTITY_PROPERTY_266_FIELD'),
			),
			'PROPERTY_267' => array(
				'data_type' => 'float',
				'title' => Loc::getMessage('ELEMENT_PROP_S26_ENTITY_PROPERTY_267_FIELD'),
			),
			'PROPERTY_268' => array(
				'data_type' => 'float',
				'title' => Loc::getMessage('ELEMENT_PROP_S26_ENTITY_PROPERTY_268_FIELD'),
			),
			'PROPERTY_269' => array(
				'data_type' => 'float',
				'title' => Loc::getMessage('ELEMENT_PROP_S26_ENTITY_PROPERTY_269_FIELD'),
			),
			'PROPERTY_323' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('ELEMENT_PROP_S26_ENTITY_PROPERTY_323_FIELD'),
			),
			'PROPERTY_340' => array(
				'data_type' => 'float',
				'title' => Loc::getMessage('ELEMENT_PROP_S26_ENTITY_PROPERTY_340_FIELD'),
			),
			'PROPERTY_352' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('ELEMENT_PROP_S26_ENTITY_PROPERTY_352_FIELD'),
			),
		);
	}
}

class ElementPropS49Table extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_iblock_element_prop_s49';
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
				'title' => Loc::getMessage('ELEMENT_PROP_S49_ENTITY_IBLOCK_ELEMENT_ID_FIELD'),
			),
			'PROPERTY_354' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('ELEMENT_PROP_S49_ENTITY_PROPERTY_354_FIELD'),
			),
			'PROPERTY_355' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('ELEMENT_PROP_S49_ENTITY_PROPERTY_355_FIELD'),
			),
			'PROPERTY_356' => array(
				'data_type' => 'float',
				'title' => Loc::getMessage('ELEMENT_PROP_S49_ENTITY_PROPERTY_356_FIELD'),
			),
			'PROPERTY_357' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('ELEMENT_PROP_S49_ENTITY_PROPERTY_357_FIELD'),
			),
			'PROPERTY_358' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('ELEMENT_PROP_S49_ENTITY_PROPERTY_358_FIELD'),
			),
		);
	}
}
?>