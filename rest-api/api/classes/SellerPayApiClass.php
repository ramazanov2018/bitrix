<?php

namespace Api\Classes;
use Bitrix\Main\Loader;

class SellerPayApiClass
{
    const IBLOCK_ID = 45;

    private $ElId = '';
    private $errorRes = '';
    private $ErrorAdd = 'Ошибка при сохранении';

    //обновить или создать ?
    public function Controller($data ,$DIRECTION)
    {
        Loader::includeModule('iblock');

        $data['ORIGIN_ID'] = trim($data['ORIGIN_ID']);

        if (!empty($data['ORIGIN_ID'])) {
            if ($this->ElId = $this->CheckExistenceElement(array('CODE' => $data['ORIGIN_ID']))) {
                $this->UpdateElement($this->ElId, $data);
            }
        }

        if ($this->ElId <= 0) {
            $this->AddElement($data);
        }


        if ($this->errorRes !== ''){
            $this->renderJson(['status'=>'error', 'error'=>$this->errorRes]);
        }else{
            $this->renderJson(['status'=>'success']);
        }
    }

    //Существуетли элемент
    public function CheckExistenceElement($arFilter)
    {
        $dbResMultiFields = \CIBlockElement::GetList(Array(), $arFilter, false, false, Array('ID'));
        while ($arMultiFields = $dbResMultiFields->Fetch()) {
            return $arMultiFields['ID'];
        }

        return 0;
    }

    //Обновление элемента
    public  function UpdateElement($ElId, $data)
    {
        $el = new \CIBlockElement;
        $arLoadProductArray = Array(
            "MODIFIED_BY"    => 1,
        );

        if (array_key_exists('NAME', $data)){
            $arLoadProductArray['NAME'] = $data['NAME'];
        }

        $res = $el->Update($ElId, $arLoadProductArray);
    }

    //Новый элемент
    public function AddElement($data)
    {
        $el = new \CIBlockElement;

        $PROP = array();

        $arLoadProductArray = Array(
            "MODIFIED_BY"    => 1,
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID"      => self::IBLOCK_ID,
            "PROPERTY_VALUES"=> $PROP,
            "NAME"           => $data['NAME'],
            "CODE"           => $data['ORIGIN_ID'],
            "ACTIVE"         => "Y",
        );

        if(!$PRODUCT_ID = $el->Add($arLoadProductArray)){
            $this->errorRes = $this->ErrorAdd;
        }

    }

    //Возврат статуса
    private function renderJson($status)
    {
        echo json_encode($status, JSON_UNESCAPED_UNICODE);
    }
}