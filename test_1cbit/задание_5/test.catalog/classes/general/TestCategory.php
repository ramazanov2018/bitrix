<?php
namespace Test\Catalog;

use Bitrix\Main;

IncludeModuleLangFile(__FILE__);

class TestCategoryTable extends Main\Entity\DataManager
{

    public static function getTableName()
    {
        return 'b_test_category_list';
    }

    public static function getMap()
    {
        return array(
            'CATEGORY_ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
                'title' => 'ИД категории',
            ),
            'CATEGORY_NAME' => array(
                'data_type' => 'string',
                'required' => false,
                'title' => "Название категории",
            ),
        );
    }
}


class TestCategory
{
    public static function getList($parameters)
    {
        return TestCategoryTable::getList($parameters);
    }

    public static function getById($Id)
    {
        $Id = (int)$Id;
        return TestCategoryTable::getById($Id);
    }
}