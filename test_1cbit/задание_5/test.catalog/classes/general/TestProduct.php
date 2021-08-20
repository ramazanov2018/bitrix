<?php
namespace Test\Catalog;

use Bitrix\Main;
use CAdminException;

IncludeModuleLangFile(__FILE__);

class TestProductTable extends Main\Entity\DataManager
{
    public static function getTableName()
    {
        return 'b_test_product_list';
    }

    public static function getMap()
    {
        return array(
            'PRODUCT_ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
                'title' => 'ИД продукта',
            ),
            'PRODUCT_NAME' => array(
                'data_type' => 'string',
                'required' => false,
                'title' => "Название продукта",
            ),
            'PRICE' => array(
                'data_type' => 'integer',
                'required' =>false,
                'title' => "Цена продукта",
            ),
            'CATEGORY_ID' => array(
                'data_type' => 'integer',
                'required' =>false,
                'title' => "ИД категории",
            ),
        );
    }
}


class TestProduct
{

    private $LAST_ERROR = "";

    protected function CheckFields(&$arFields)
    {
        global $APPLICATION;
        $this->LAST_ERROR = "";

        $APPLICATION->ResetException();

        /*событие OnBeforeProductAdd*/
        //срабатывает до добавления элемента
        $db_events = GetModuleEvents('test.catalog', 'OnBeforeProductAdd', true);

        foreach($db_events as $arEvent)
        {
            $bEventRes = ExecuteModuleEventEx($arEvent, array(&$arFields));
            if($bEventRes===false)
            {
                if($err = $APPLICATION->GetException())
                    $this->LAST_ERROR .= $err->GetString()."<br>";
                else
                {
                    $APPLICATION->ThrowException("Unknown error");
                    $this->LAST_ERROR .= "Unknown error.<br>";
                }
                break;
            }
        }
        /*событие OnBeforeProductAdd*/

        $aMsg = array();
        if (strlen($arFields["PRODUCT_NAME"]) == 0)
        {
            $aMsg[] = array(
                "id" => "NAME",
                "text" => GetMessage("CLASS_TEST_PRODUCT_ERR_NAME")
            );

        }

        if(!empty($aMsg))
        {
            $e = new CAdminException($aMsg);
            $GLOBALS["APPLICATION"]->ThrowException($e);
            $this->LAST_ERROR = $e->GetString();
            return false;
        }
        return true;
    }

    public function AddProduct($arFields)
    {

        if(!$this->CheckFields($arFields)) {
            return false;
        }

        $DBManager = TestProductTable::add($arFields);
        $ID = $DBManager->getId();
        return $ID;
    }

    public static function getList($parameters)
    {
        return TestProductTable::getList($parameters);
    }
}