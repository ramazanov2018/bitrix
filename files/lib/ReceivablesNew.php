<?php

namespace Serv;

use Serv\Register\RegisterTable;
use Bitrix\Main\Diag;


class ReceivablesNew
{
    static $DealStage = 'C1:EXECUTING'; // Стадия договор - подписан

    static $IblockId = 39; //Дебиторская задолженность
    static $Client = '227'; //Клиент
    static $PropCompanySeller = '251'; //Компания продавец
    static $PropContract = '250'; //Договор  контрагента
    static $PropOrderId = '249'; //Номер заказа в   1с
    static $PropProduct = '230'; //Продукт
    static $PropPrice = '252'; //Цена
    static $PropCurrency = '256'; //Валюта

    static $PropVolume = '253'; //Объем
    static $PropPaymentTerm = '254'; //Условия оплаты
    static $PropDeal = '228'; //Сделка (Спецификация)
    static $PropTermsDelivery = '257'; //Условия   поставки
    static $PropExpectedAmount = '255'; //Сумма   ожидаемого поступления
    static $PropExpectedAmount1 = '408'; //Сумма   ожидаемого поступления (предоплата)
    static $PropExpectedAmount2 = '406'; //Сумма   ожидаемого поступления (постоплата)
    static $ReceiptContract = '261'; //Предполагаемые сроки поступлений по контракту
    static $PropAmountFact = '407'; //Сумма поступления предоплаты факт
    static $PropFromFactory = '258'; //Выход с завода
    static $PropExitPort = '259'; //Выход из порта
    static $PropDataKonosamenta = '260'; //Дата коносамента или инвойса для срока поступления
    static $PropRaznitsaNaDnyax = '264'; //Разница между сегодняшней датой и предполагаемой датой поступления
    static $ZaderjkaNaDnyax = '263'; //Задержка на днях, если есть
    static $SrokiSZaderjkoy = '262'; //Предполагаемые сроки поступлений с учетом задержки
    static $PropActual = '491'; //Актуальное

    static $IblockUslovieOplati = 42;
    static $IblockDogovorId = 28;

    static $Date = array(
        '541' => 'UF_CRM_1571651825',
        '542' => 'UF_CRM_1582304564464',
        '543' => 'UF_CRM_1569494555',
        '544' => 'UF_CRM_1569494631'
    );

    static $DealIsDogovor = 'UF_CRM_1611744259';
    static $DealDogovor = 'UF_CRM_1585660389';
    static $DealFile = 'UF_CRM_1571647177';

    static $DealCategoryExport = 1;
    static $DealCreateIn1C = 'UF_CRM_1597916353';

    //Добавление элемента Дебиторская задолженность
    function OnAddReceivables($arFields)
    {
        if (self::includeModules() == false)
            return true;

        if (self::Check($arFields['ID']))
            return true;

        $fileDate = new \Bitrix\Main\Type\DateTime();
        $fileDate->add("7 day");

        $PROP = self::BuildData($arFields['ID'], $fileDate);

        $CreatedId = $PROP['ASSIGNED_BY_ID'];
        unset($PROP['ASSIGNED_BY_ID']);

        $el = new \CIBlockElement;
        $arLoadProductArray = Array(
            "MODIFIED_BY" => $arFields['MODIFIED_BY'],
            "CREATED_BY" => $CreatedId,
            "IBLOCK_ID" => self::$IblockId,
            "PROPERTY_VALUES" => $PROP,
            "NAME" => $arFields['ID'],
            "ACTIVE" => "Y",
        );

        $PRODUCT_ID = $el->Add($arLoadProductArray);
        return true;
    }

    //Сумма поступления предоплаты факт, изменяется когда изменяется поле в сделке Сумма предоплаты (факт)/Валюта
    function OnUpdateAmountFact(&$arFields)
    {
        if (empty($arFields['UF_CRM_1571648204']))
            return true;
        if (self::includeModules() == false)
            return true;


        $res = self::ElDeb($arFields['ID']);
        if ($res <= 0){
            return true;
        }

        $PROP[self::$PropAmountFact] = $arFields['UF_CRM_1571648204'];

        $price = explode("|", $arFields['UF_CRM_1571648204']);

        $prod = self::GetProdDeal($arFields['ID']);
        
        $Count = $prod['UF_COUNT_PLAN'];//UF_COUNT_FACT
        $deal = \CCrmDeal::GetList(Array(), Array('ID' => $arFields['ID']), Array('UF_CRM_1571651807'), false)->Fetch();
        if($prod['UF_CRM_1571651807'] != '' )
            $Count = $prod['UF_COUNT_FACT'];
            
        $PROP[self::$PropVolume] = $Count;
        $PROP[self::$PropExpectedAmount] = ($prod['UF_PRICE_PLAN'] * $Count) - $price['0'];
        $PROP[self::$ReceiptContract] = '';
        $PROP[self::$PropActual]   = 584;
        
        \CIBlockElement::SetPropertyValuesEx($res, self::$IblockId, $PROP);

       return true;
    }

    //Перерасчет дебитора, если прилетела дата ухода
    /*function OnRecalculateNerezident(&$arFields){
        if (empty($arFields['UF_CRM_1571651807'])){
            return true;
        }
        $DealId = (int)$arFields['ID'];
        
        $deb = self::ElDeb($DealId);
        
        
        if ($deb <= 0){
            return true;
        }
        //Сделка
        $deal = \CCrmDeal::GetList(Array(), Array('ID' => $DealId), Array(), false)->Fetch();
        //Товары в сделке
        $product = self::GetProdDeal($DealId);
        //условие оплаты
        $arSelect = Array('ID','PROPERTY_404', 'PROPERTY_405');
        $arFilter = Array(
            "IBLOCK_ID" => IBLOCK_USLOVIE_OPLATI,
            "ID" => $deal['UF_CRM_1569319143'],
            );
        
        $res = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect)->fetch();
       
        $PropVolume = $product['UF_COUNT_FACT'];
        $PropPrice  = $product['UF_PRICE_PLAN'];
        
        
        if ($res['PROPERTY_404_VALUE'] > '0'){
            $PropExpectedAmount1 = $PropVolume * $PropPrice * $res['PROPERTY_404_VALUE'];
            $PropExpectedAmount =  $PropExpectedAmount1;
          
        };
        
        if ($res['PROPERTY_405_VALUE'] > '0'){
            $PropExpectedAmount2 = $PropVolume * $PropPrice * $res['PROPERTY_405_VALUE'];
        }
        
        $Update[self::$PropVolume] = $PropVolume;
        $Update[self::$PropExpectedAmount] = $PropExpectedAmount;
        
        //$Update[self::$PropExpectedAmount1] = $PropExpectedAmount1;
        //$Update[self::$PropExpectedAmount2] = $PropExpectedAmount2;
        
        
        if(isset($deal['UF_CRM_1571648204'])){
            $price = explode('|', $deal['UF_CRM_1571648204']);
            
            $Update[self::$PropExpectedAmount] = ($PropVolume * $PropPrice - $price['0']);
        }
       /* 
        pre('$PropVolume'.$PropVolume);
        pre('$PropPrice'.$PropPrice);
        pre($price);
        pre($res['PROPERTY_404_VALUE']);
        
        
        pre($Update);
        die();*/
        
        //
        
        /*     => , // Объем
            self::$PropExpectedAmount = $PropExpectedAmount, //Сумма ожидаемого поступления
            //self::$PropExpectedAmount1 = $PropExpectedAmount1, //Сумма ожидаемого поступления (предоплата)
            //self::$PropExpectedAmount2 = $PropExpectedAmount2, //Сумма ожидаемого поступления (постоплата)
        ];
        
        //Обновляем
        \CIBlockElement::SetPropertyValuesEx($deb, self::$IblockId , $Update);
        return true;
    }*/
    // срабатывает когда изменяется поле в сделке "Дата ухода"
    function ValueNerezident(&$arFields)
    {
        if (self::includeModules() == false)
            return true;


        $deb = self::ElDeb($arFields['ID']);
        if ($deb <= 0){
            return true;
        }

        $dSelect =Array("UF_CRM_1571649281", "UF_CRM_1569319143", "UF_CRM_1571648204", "UF_CRM_1582304564464",
            "UF_CRM_1569494555", "UF_CRM_1571651825", 'UF_CRM_1571651807', "UF_CRM_1569494631");

        $deal = \CCrmDeal::GetList(Array(), Array('ID' => $arFields['ID']), $dSelect, false)->Fetch();

        if (empty($deal['UF_CRM_1571651807'])){
            return true;
        }

        //Товары в сделке
        $product = self::GetProdDeal($arFields['ID']);
        $PropVolume = $product['UF_COUNT_FACT'];
        $PropPrice  = $product['UF_PRICE_PLAN'];

        // Условия оплаты
        $arSelect = Array('PROPERTY_403', 'PROPERTY_402', 'PROPERTY_404', 'PROPERTY_405');
        $arFilter = Array("IBLOCK_ID" =>self::$IblockUslovieOplati, "ID" => $deal["UF_CRM_1569319143"]);
        $res = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect)->fetch();

        $Date =  ($deal[self::$Date[$res["PROPERTY_403_ENUM_ID"]]]) ? new  \Bitrix\Main\Type\DateTime($deal[self::$Date[$res["PROPERTY_403_ENUM_ID"]]]) : '';

        $PROP = array();
        if ($Date != ''){
            $PROP[self::$PropDataKonosamenta] = $Date->format("d.m.Y");     //Дата коносамента или инвойса для срока поступления

            if($res['PROPERTY_405_VALUE'] > 0) // коэф постоплата
                $PROP[self::$PropExpectedAmount] = ($PropVolume * $PropPrice * $res['PROPERTY_405_VALUE']);

            if($res['PROPERTY_404_VALUE'] > 0) // коэф предопата
                $PROP[self::$PropExpectedAmount] = ($PropVolume * $PropPrice * $res['PROPERTY_404_VALUE']);

            $Date->add($res["PROPERTY_402_VALUE"]." day");
            $PROP[self::$ReceiptContract]   = $Date->format("d.m.Y");       //Предполагаемые сроки поступлений по контракту
            $PROP[self::$PropActual]   = 583;       //Предполагаемые сроки поступлений по контракту
        }

        if (!empty($deal["UF_CRM_1571648204"])){
            // Берем Сумма ожидаемого поступления(постоплата) из ИБ Дебиторки
            $price = explode('|', $deal['UF_CRM_1571648204']);

            $PROP[self::$PropExpectedAmount] = ($PropVolume * $PropPrice - $price['0']);
        }

        $PROP[self::$PropVolume] = $PropVolume;                         //Объем
        $PROP[self::$PropFromFactory]   = $deal['UF_CRM_1571651807'];   //Выход с завода
        $PROP[self::$PropExitPort]      = $deal['UF_CRM_1571651825'];   //Выход из порта
        
        \CIBlockElement::SetPropertyValuesEx($deb, self::$IblockId, $PROP);
       return true;
    }

    function ZaderjkaNaDnyax(&$arFields)
    {
        if ($arFields['IBLOCK_ID'] != self::$IblockId){
            return true;
        }

        $date1 = current($arFields['PROPERTY_VALUES'][self::$SrokiSZaderjkoy])['VALUE'];
        $date2 = current($arFields['PROPERTY_VALUES'][self::$ReceiptContract])['VALUE'];

        if (!empty($date1) && !empty($date2)){
            $date1 =  strtotime($date1);
            $date2 =  strtotime($date2);

            if ($date1 > $date2){
                $arFields['PROPERTY_VALUES'][self::$ZaderjkaNaDnyax]["n0"] = 1;
            }

        }
        return true;
    }

    function BuildData($DealId, $fileDate)
    {
        $PROP = array();

        $deal = \CCrmDeal::GetList(Array(), Array('ID' => $DealId), Array(), false)->Fetch();
        $prod = self::GetProdDeal($DealId);

        $PROP[self::$PropCompanySeller] = $deal['UF_CRM_1571647560'];
        $PROP[self::$PropContract] = \CIBlockElement::GetByID($deal['UF_CRM_1585660389'])->GetNext()['NAME'];
        $PROP[self::$Client] = $deal['COMPANY_ID'];
        $PROP[self::$PropOrderId] = $deal['TITLE'];
        $PROP[self::$PropProduct] = $prod['UF_PRODUCT_ID'];
        $PROP[self::$PropPrice] = $prod['UF_PRICE_PLAN'];
        $PROP[self::$PropVolume] = $prod['UF_COUNT_PLAN'];
        $PROP[self::$PropDeal] = $DealId;
        $PROP[self::$PropPaymentTerm] = $deal['UF_CRM_1569319143'];
        $PROP[self::$PropTermsDelivery] = $deal['UF_CRM_1569316998'];
        $PROP[self::$PropCurrency] = $deal['CURRENCY_ID'];
        $PROP['ASSIGNED_BY_ID'] = $deal['ASSIGNED_BY_ID'];

        if (empty($deal['UF_CRM_1569319143'])){
            return $PROP;
        }

        //условие оплаты
        $arSelect = Array('ID','PROPERTY_404', 'PROPERTY_405');
        $arFilter = Array(
            "IBLOCK_ID" => IBLOCK_USLOVIE_OPLATI,
            "ID" => $deal['UF_CRM_1569319143'],
        );

        $res = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect)->fetch();
        if ($res['PROPERTY_404_VALUE'] > '0'){
            $PROP[self::$PropExpectedAmount1] = $PROP[self::$PropVolume] * $PROP[self::$PropPrice] * $res['PROPERTY_404_VALUE'];
            $PROP[self::$PropExpectedAmount] =  $PROP[self::$PropExpectedAmount1];
            $PROP[self::$ReceiptContract] = $fileDate->format("d.m.Y");
        };

        if ($res['PROPERTY_405_VALUE'] > '0'){
            $PROP[self::$PropExpectedAmount2] = $PROP[self::$PropVolume] * $PROP[self::$PropPrice] * $res['PROPERTY_405_VALUE'];
        }

        return $PROP;
    }

    //Поля из сделки
    function GetProdDeal($DealID)
    {
        $dealPrId = 0;
        $arFilterP = ['CHECK_PERMISSIONS' => 'N', 'OWNER_ID' => $DealID,];
        $dbRes = \CAllCrmProductRow::GetList(['ID'], $arFilterP, ['PRODUCT_ID']);
        while ($data = $dbRes->Fetch())
        {
            $dealPrId = $data['PRODUCT_ID'];
        }
        $prOb = RegisterTable::GetList(array(
            'filter'=>['UF_DEAL' => $DealID, 'UF_PRODUCT_ID' => $dealPrId, 'PrRow.OWNER_TYPE' => 'D'],
            'select'=>[
                'UF_PRICE_PLAN',
                'UF_COUNT_PLAN', 
                'UF_COUNT_FACT' => 'PrRow.QUANTITY',
                'UF_PRODUCT_ID'
            ],
            'runtime' => [
                new \Bitrix\Main\Entity\ReferenceField(
                    'PrRow',
                    '\Bitrix\Crm\ProductRowTable',
                    array(
                           '=this.UF_PRODUCT_ID' => 'ref.PRODUCT_ID', 
                           '=this.UF_DEAL' => 'ref.OWNER_ID', 
                    ),
                    array('join_type' => 'LEFT')
                    )
                
            ]
        ))->Fetch();

        return $prOb;
    }

    function Check($DealId)
    {
        $deal = \CCrmDeal::GetById($DealId);

        if ($deal['STAGE_ID'] != self::$DealStage || $deal['CATEGORY_ID'] != self::$DealCategoryExport){
            return true;
        }

        if ($deal['COMPANY_ID'] == '60926' || $deal['COMPANY_ID'] == '59568'){
            return true;
        }

        $res = self::ElDeb($DealId);
        if ($res > 0){
            return true;
        }

        $fileId = \CCrmDeal::GetList(Array(), Array('ID' => $DealId), Array(self::$DealIsDogovor), false)->Fetch()[self::$DealIsDogovor];
        if ($fileId == 0){
            return true;
        }

        return false;
    }

    /*function fileDogovor($DealId)
    {
        $fileId = \CCrmDeal::GetList(Array(), Array('ID' => $DealId), Array("UF_CRM_1571647177"), false)->Fetch()["UF_CRM_1571647177"];
        if ($fileId <= 0){
            return false;
        }

        if ($fileDate = \CFile::GetByID($fileId)->Fetch()['TIMESTAMP_X']){
            return $fileDate;
        }
        else {
            return false;
        }

    }*/

    function includeModules()
    {
        if (\CModule::IncludeModule("crm") && \CModule::IncludeModule("iblock")) {
            return true;
        }

        return false;
    }

    function ElDeb($DealId)
    {
        $arSelect = Array('ID');
        $arFilter = Array(
            "IBLOCK_ID" => self::$IblockId,
            "=PROPERTY_228" => $DealId
        );

        $res = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect)->fetch()['ID'];
        return $res;
    }

    function SetRight($arParams){
        if($arParams['IBLOCK_ID'] != self::$IblockId)
            return true;
        //Дебиторская задолженность
        if($arParams['IBLOCK_ID'] == self::$IblockId ){
            $ManagerId = 4;

            IblockElementRight(self::$IblockId, $arParams['ID'], $ManagerId);
        }
        return true;
    }

    function difference ($date){
        $now = strtotime("now");
        $Dif = $date - $now;
        $d = $Dif / 86400;
        return ceil($d);
    }

    function Prop262UpdateEx ($ELEMENT_ID, $IBLOCK_ID, $PROPERTY_VALUES, $FLAGS){


        if ($IBLOCK_ID != self::$IblockId || empty($ELEMENT_ID)){
            return true;
        }
        if (isset($PROPERTY_VALUES[self::$SrokiSZaderjkoy])){
            \CModule::IncludeModule("iblock");

            self::Prop263Update($ELEMENT_ID, $IBLOCK_ID); //Задержка на днях, если есть
            self::Prop264Update($ELEMENT_ID, $IBLOCK_ID, $PROPERTY_VALUES, $FLAGS);  //Разница между сегодняшней датой и предполагаемой датой поступления
        }

        if (array_key_exists(self::$ZaderjkaNaDnyax, $PROPERTY_VALUES)){
            \CModule::IncludeModule("iblock");

            self::Prop263Update($ELEMENT_ID, $IBLOCK_ID);
        }

        if (array_key_exists(self::$PropRaznitsaNaDnyax, $PROPERTY_VALUES)){
            \CModule::IncludeModule("iblock");

            self::Prop264Update($ELEMENT_ID, $IBLOCK_ID, $PROPERTY_VALUES, $FLAGS);  //Разница между сегодняшней датой и предполагаемой датой поступления
        }

        return true;

    }


    function Prop263Update ($ELEMENT_ID, $IBLOCK_ID)
    {
        $arSelect = Array('PROPERTY_261', 'PROPERTY_262');
        $arFilter = Array(
            "IBLOCK_ID" =>$IBLOCK_ID,
            "ID" => $ELEMENT_ID
        );

        $el = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect)->fetch();
        $date1 = new \DateTime($el["PROPERTY_262_VALUE"]);
        $date2 = new \DateTime($el["PROPERTY_261_VALUE"]);
        
        $diffValue = 0;
        $coef = 1;
        
        
        $diff = $date1->diff($date2);
      
        if($diff->days > 0)
            $diffValue = ($diff->invert > 0 ? 1 : -1)*$diff->days;
            
           
        \CIBlockElement::SetPropertyValueCode($ELEMENT_ID, self::$ZaderjkaNaDnyax , $diffValue);
        /*
        
        die();
        if (isset($date1) && isset($date2)){
            $date1 =  strtotime($date1);
            $date2 =  strtotime($date2);

            if ($date1 > $date2){
                $PROPERTY_VALUE = "1";
                \CIBlockElement::SetPropertyValueCode($ELEMENT_ID, self::$ZaderjkaNaDnyax , $PROPERTY_VALUE);
            }

            if ($date1 <= $date2){
                $PROPERTY_VALUE = "0";
                \CIBlockElement::SetPropertyValueCode($ELEMENT_ID, self::$ZaderjkaNaDnyax , $PROPERTY_VALUE);
            }

        }*/
    }

    function closedDeal($arFields)
    {
        \CModule::IncludeModule('crm');

        $Deals = \CCrmDeal::GetList(Array(), Array('ID' => $arFields["ID"]), Array("CLOSED"), false)->Fetch();

        if ($Deals["CLOSED"] === "Y"){

            self::DеbitorDelete($arFields["ID"]);
        }

        return true ;
    }

    function deletedDeal($arFields)
    {
        self::DеbitorDelete($arFields);
        return true ;
    }

    function DеbitorDelete($DealId)
    {
        $DealId = (int)$DealId;
        \CModule::IncludeModule('iblock');
        $arSelect = Array('ID');
        $arFilter = Array(
            "IBLOCK_ID" => self::$IblockId,
            "=PROPERTY_228" => $DealId
        );

        $resOb = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
        while ($res = $resOb->fetch()){
            $res = \CIBlockElement::Delete($res["ID"]);
        }
    }

    //Разница между сегодняшней датой и предполагаемой датой поступления  -- Агент(каждый 86400сек)
    static function UpdateDelayAgent()
    {
        $ar = array();
        \CModule::IncludeModule('iblock');
        $arSelect = Array('ID', 'PROPERTY_262');
        $arFilter = Array(
            "IBLOCK_ID" => self::$IblockId,
        );
        $resOb = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
        while ($res = $resOb->fetch()){
                        if (strlen($res['PROPERTY_262_VALUE']) > 0){
                $Date =  new \Bitrix\Main\Type\DateTime($res['PROPERTY_262_VALUE']);
                $d = self::difference($Date->getTimestamp());
                $PROP[self::$PropRaznitsaNaDnyax] = $d;
                \CIBlockElement::SetPropertyValuesEx($res['ID'], self::$IblockId, $PROP);
            }
            else{
                $PROP[self::$PropRaznitsaNaDnyax] = '';
                \CIBlockElement::SetPropertyValuesEx($res['ID'], self::$IblockId, $PROP);
            }
        }

        return "Serv\ReceivablesNew::UpdateDelayAgent();";
    }

    function Prop264Update($ELEMENT_ID, $IBLOCK_ID, $PROPERTY_VALUES, $FLAGS)
    {
        $arSelect = Array('PROPERTY_262');
        $arFilter = Array(
            "IBLOCK_ID" =>$IBLOCK_ID,
            "ID" => $ELEMENT_ID
        );

        $el = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect)->fetch();
        $val = $el["PROPERTY_262_VALUE"];
        if (strlen($val) > 0){
            $Date =  new \Bitrix\Main\Type\DateTime($val);
            $d = self::difference($Date->getTimestamp());
            \CIBlockElement::SetPropertyValueCode($ELEMENT_ID, self::$PropRaznitsaNaDnyax , $d);
        }
    }
    
    function OnAfterIBlockElementUpdateSetRight($fields)
    {
        
        
        if($fields['IBLOCK_ID'] != self::$IblockId){
            return true;
        }
        
        $arTasks = \CIBlockRights::GetRightsList();   // получаем массив уровней доступа
        
        $object = new \CIBlockElementRights($fields['IBLOCK_ID'], $fields['ID']); // создаём объект прав, передав в него id инфоблока и id элемента
        $arRights = $object->GetRights(); // получим права для элемента
        
        
        foreach($arRights as $id => $Item){
            if($Item['GROUP_CODE'] == 'AU')
                unset($arRights[$id]);
            
        }
        
        $arRights['n0'] = array(
            'GROUP_CODE' => 'AU',
            'DO_INHERIT' => 'Y',
            'IS_INHERITED' => 'Y',
            'OVERWRITED' => true,
            'TASK_ID' => 61,
            'XML_ID' => '',
            'ENTITY_TYPE' => 'element',
            'ENTITY_ID' => $fields['ID']
        );
       
        
        $R = $object->SetRights($arRights);
        
     }

    function onICDealAdd($arFields)
    {
        if($arFields[self::$DealCreateIn1C] != 1) return true;

        if(empty($arFields[self::$DealDogovor] || $arFields['CATEGORY_ID'] != self::$DealCategoryExport)) return true;

        $dealId = (int)$arFields['ID'];
        $dogId = (int)$arFields[self::$DealDogovor];
        if (self::DogovorIn1C($dogId)){
            //Фиксируем id сделки в файл для обновления, после прихода плановых покозателей из 1С ()
            file_put_contents(DOGOVOR_IN_1C_FILE, $dealId);
        }
        return true;
    }

    static function DealIsDogovor($dealId){
        \CModule::IncludeModule('crm');

        $dealId = (int)$dealId;
        $arFields[self::$DealIsDogovor] = 1;
        $arFields['STAGE_ID'] = self::$DealStage;
        self::UpdateDeal($dealId, $arFields);
    }

    function DogovorIn1C ($id)
    {
        $arSelect = Array("ID");
        $arFilter = Array(
            "IBLOCK_ID"=>self::$IblockDogovorId,
            "ID" => $id,
            "=PROPERTY_489_ENUM_ID" => 579,
        );

        $res = \CIBlockElement::GetList(Array('ID'=>'DESC'), $arFilter, false, false, $arSelect);

        if($ob = $res->fetch()){
            return true;
        }
        return false;
    }

    function isFile($id)
    {
         $arSelect = Array("ID", "PROPERTY_90");
         $arFilter = Array(
             "IBLOCK_ID"=>self::$IblockDogovorId,
             "ID" => $id,
             array(
                 "LOGIC" => "OR",
                 array("!PROPERTY_90" => false),
                 array("=PROPERTY_489_ENUM_ID" => 579),
             ),
         );

         $res = \CIBlockElement::GetList(Array('ID'=>'DESC'), $arFilter, false, false, $arSelect);

         if($ob = $res->fetch()){
             $fileId = $ob['PROPERTY_90_VALUE'];
             if($fileId > 0){
                 $fileInfo = \CFile::GetByID($fileId);
                 
                 if($fileArr = $fileInfo->Fetch())
                 {
                     $newFilePath = 'crm/'.$fileArr['FILE_NAME'];
                     $fileCopy = \CFile::CopyFile($fileId, false, $newFilePath);
                     $file = \CFile::MakeFileArray($fileCopy);
                    
                     $arFields[self::$DealFile] = $file;
                 }
             }

             
             $filter = [
                 self::$DealDogovor => $id, 
                 self::$DealIsDogovor => 0 ,
                 'CATEGORY_ID' => self::$DealCategoryExport
                 
             ];
            
             $id = (int)$ob['ID'];
             $dealOb = \CCrmDeal::GetList(array(), $filter, array('ID'), false);
             while ($deal = $dealOb->Fetch()){
                  
                 $dId = (int)$deal['ID'];
                 $arFields[self::$DealIsDogovor] = 1;
                 $arFields['STAGE_ID'] = self::$DealStage;
                 self::UpdateDeal($dId, $arFields);
             }
             return true;   
             
         }
     }

    function OnDogovorChange($arFields)
    {
        $id = (int) $arFields['ID'];
        self::isFile($id);
        return true;
    }

    static function UpdateDeal($id, $arFields)
    {
        $c = new \CCrmDeal(false);
        $res = $c->Update($id, $arFields, true, true, array('CURRENT_USER' => 1));
    }

}