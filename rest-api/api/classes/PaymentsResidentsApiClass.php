<?php
namespace Api\Classes;

use Bitrix\Main\Loader;


class PaymentsResidentsApiClass
{

    const IBLOCK_ID = 46;
    const KONTRAGENT = '318';
    const STATYA_OBOROTOV = '319';
    const SUMMA = '320';
    const DATA_OPLATA_NEREZ = '321';
    const VALYUTA_NEREZ = '322';
    const CONTRACT = 'CONTRACT';

    private $ErrorAdd = 'Ошибка при сохранении';

    //Новый элемент
    public function AddElement($data, $DIRECTION)
    {

        Loader::includeModule('iblock');

        $el = new \CIBlockElement;

        $PROP = array();
        $PROP[self::KONTRAGENT] = $data[self::KONTRAGENT];
        $PROP[self::STATYA_OBOROTOV] = $data[self::STATYA_OBOROTOV];
        $PROP[self::SUMMA] = $data[self::SUMMA];
        $PROP[self::DATA_OPLATA_NEREZ] = $data[self::DATA_OPLATA_NEREZ];
        $PROP[self::VALYUTA_NEREZ] = array('VALUE' => (int)$data[self::VALYUTA_NEREZ]);


        $arLoadProductArray = Array(
            "MODIFIED_BY"    => 1,
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID"      => self::IBLOCK_ID,
            "PROPERTY_VALUES"=> $PROP,
            "NAME"           => $data[self::CONTRACT],
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