<?php
namespace Api\Classes;
use Bitrix\Main\Loader;

class TermPayApiClass
{
    const IBLOCK_ID = 42;
    const PR_DESCRIPTION = 331;
    const PR_COST = 332;

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

        $PROP = array();
        if (array_key_exists(self::PR_DESCRIPTION, $data)){
            $PROP[self::PR_DESCRIPTION] = $data[self::PR_DESCRIPTION];
        }

        if (array_key_exists(self::PR_COST, $data)){
            $PROP[self::PR_COST] = $data[self::PR_COST];
        }
        $arLoadProductArray[ "PROPERTY_VALUES"] = $PROP;


        $res = $el->Update($ElId, $arLoadProductArray);
    }

    //Новый элемент
    public function AddElement($data)
    {
        $el = new \CIBlockElement;

        $PROP = array();
        $PROP[self::PR_DESCRIPTION] = $data[self::PR_DESCRIPTION];
        $PROP[self::PR_COST] = $data[self::PR_COST];

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
