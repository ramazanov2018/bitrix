<?php
namespace Api\Classes;

use Bitrix\Main\Loader;

class ProductsApiClass
{
    const IBLOCK_ID = 26;
    const ID_1C = '323';
    const SECTION = ['1' => 65, '2' => 66];
    const NAME = 'NAME';
    const DIRECTION = 'DIRECTION';

    private $ErrorAdd = 'Ошибка при сохранении';
    private $ErrorDirection = 'Неправильно указан направление 1C (DIRECTION)';



    private function CheckDirection($DIRECTION)
    {
        if($DIRECTION !== '1' && $DIRECTION !== '2'){
            return false;
        }
        return true;
    }

    //Новый элемент
    public function AddElement($data ,$DIRECTION)
    {
        if (!$this->CheckDirection($DIRECTION)){
            $this->renderJson(['status' => 'error','error'=>$this->ErrorDirection]);
            return false;
        }

        $data[self::DIRECTION] = $DIRECTION;

        Loader::includeModule('iblock');

        $el = new \CIBlockElement;

        $PROP = array();
        $PROP[self::ID_1C] = $data[self::ID_1C];


        $arLoadProductArray = Array(
            "MODIFIED_BY"    => 1,
            "IBLOCK_SECTION_ID" => self::SECTION[$data[self::DIRECTION]],
            "IBLOCK_ID"      => self::IBLOCK_ID,
            "PROPERTY_VALUES"=> $PROP,
            "NAME"           => $data[self::NAME],
            "ACTIVE"         => "Y",
        );

        if($PRODUCT_ID = $el->Add($arLoadProductArray)){
            $this->renderJson(['status' => 'success']);
            return $PRODUCT_ID;
        } else{
            $this->renderJson(['status'=>'error', 'error'=>$this->ErrorAdd]);
            return false;
        }

    }

    //Возврат статуса
    protected function renderJson($status)
    {
        echo json_encode($status, JSON_UNESCAPED_UNICODE);
    }
}