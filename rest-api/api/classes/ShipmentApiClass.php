<?php

namespace Api\Classes;

use Bitrix\Main\Loader;

class ShipmentApiClass
{


    const IBLOCK_ID = 38;
    const NOMER_NAIMENOVANIE = '215';
    const DATA_OTGRUZKI = '216';
    const TOVAR = '217';
    const SUMMA = '218';
    const SDELKA = '219';
    const KOMPANIYA = '220';
    const KOLICHESVTO = '317';
    const CURRENCY = 'CURRENCY';
    const UNIT = '353';

    private $Error = 'Не заполнены все поля';
    private $ErrorAdd = 'Ошибка при сохранении';

    //Новый элемент
    public function AddElement($data, $DIRECTION)
    {
        Loader::includeModule('iblock');

        $el = new \CIBlockElement;

        $PROP = array();
        $PROP[self::NOMER_NAIMENOVANIE] = $data[self::NOMER_NAIMENOVANIE];
        $PROP[self::DATA_OTGRUZKI] = $data[self::DATA_OTGRUZKI];
        $PROP[self::TOVAR] = explode(",", $data[self::TOVAR]);
        $PROP[self::SUMMA] = $data[self::SUMMA];
        $PROP[self::SDELKA] = $data[self::SDELKA];
        $PROP[self::KOMPANIYA] = $data[self::KOMPANIYA];
        $PROP[self::KOLICHESVTO] = $data[self::KOLICHESVTO];
        $PROP[self::UNIT] = $data[self::UNIT];

        $arLoadProductArray = Array(
            "MODIFIED_BY"    => 1,
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID"      => self::IBLOCK_ID,
            "PROPERTY_VALUES"=> $PROP,
            "NAME"           => "Отгрузка ". $data[self::NOMER_NAIMENOVANIE],
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