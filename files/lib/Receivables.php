<?php

namespace Serv;

use Serv\Register\RegisterTable;


class Receivables
{
    /*Отгрузка*/
    static $IblockShipmentId = 38; //Отгрузка
    static $ShipmentDateShipment = '216'; // Дата отгрузки
    static $ShipmentNumberShipment = '215'; // Номер отгрузки
    static $ShipmentCompany = '220'; // Компания
    static $ShipmentDeal = '219'; // Сделка
    static $ShipmentAmount = '218'; // Сумма
    static $ShipmentProduct = '217'; // Продукт
    static $ShipmentObyem = '317'; // Количество
    /*Отгрузка*/

    /*Судебный порядок*/
    static $IblockSud = 37;
    static $SudPropDeal = '214';
    static $SudPropDate = '211';
    static $SudPropType = '210';
    /*Судебный порядок*/

    static $IblockId = 54; //Дебиторская задолженность
    static $IblockCourtOrderId = 37; //Судебный порядок

    static $NumberShipment = '444'; //Номер отгрузки
    static $ShipDate = '445'; //Дата отгрузки
    static $Client = '446'; //Клиент
    static $PropDeal = '447'; //Сделка (Спецификация)
    static $PropProduct = '449'; //Продукт
    static $SumStart = '450'; //Сумма начальная
    static $SumRemaining = '451'; //Сумма оставшаяся //TODO (Сумма начальная – загруженная оплата  *Доделать)
    static $daysDelay = '452'; //Кол.-во дней отсрочки (по договору)
    static $postPay = '453'; //Понятийный срок постоплаты
    static $timePay = '454'; //Срок оплаты
    static $timePayConcept = '455'; //Срок оплаты от понятийной даты
    static $dayOverdue = '456'; //Кол-во дней просрочки
    static $dayOverdueConcept = '457'; //Кол-во дней просрочки от понятийного срока постоплаты
    static $EstimatePayDate = '458'; //Ориентировочная дата платежа
    static $EstimatePaySum = '459'; //Ориентировочная сумма платежа
    static $DateReminders = '460'; //Дата отправки напоминания
    static $DateRequirements = '461'; //Дата отправки требования
    static $DateClaim = '462'; //Дата отправки претензии
    static $DateArbitration = '463'; //Дата отправки предарбитража
    static $DateStatement = '464'; //Дата подачи искового заявления
    static $PropOrderId = '466'; //Номер заказа в   1с
    static $PropContract = '448'; //Договор  контрагента
    static $PropCompanySeller = '467'; //Компания   продавец
    static $PropPrice = '468'; //Цена
    static $PropVolume = '469'; //Объем
    static $PropPaymentTerm = '470'; //Условия оплаты
    static $PropExpectedAmount = '471'; //Сумма   ожидаемого поступления
    static $PropCurrency = '472'; //Валюта
    static $PropTermsDelivery = '473'; //Условия   поставки
    static $PropFromFactory = '474'; //Выход с завода
    static $PropExitPort = '475'; //Выход из порта
    static $PropInvoiceDate = '476'; //Дата   коносамента или инвойса для срока поступления
    static $ReceiptContract = '477'; //Предполагаемые сроки поступлений по контракту
    static $ReceiptDelay = '478'; //Предполагаемые сроки поступлений с учетом задержки
    static $DelayInDays = '479'; //Задержка в днях, если есть //
    static $PropDifference = '480'; //Разница между сегодняшней датой и предполагаемой датой поступления
    static $PropManager = '484'; //Ответственный

    static $type = [
        '501' => 'Напоминание',
        "502" => 'Требование',
        '503' => 'Претензия',
        '504' => 'Предарбитража',
        '505' => 'Исковое заявления'
    ];

    static $type2 = [
        '501' => '460',
        "502" => '461',
        '503' => '462',
        '504' => '463',
        '505' => '464'
    ];

    //Расчетные поля:(при добавлении)
    function BeforeAddReceivables(&$arFields)
    {

        if ($arFields['IBLOCK_ID'] !== self::$IblockId) return true;
        if (self::includeModules() == false) return true;

        if (!$dealId = reset($arFields['PROPERTY_VALUES'][self::$PropDeal]['n0'])){
            return true;
        }
        $dealId = (int)filter_var($dealId, FILTER_SANITIZE_NUMBER_INT);
        $prodID = $arFields['PROPERTY_VALUES'][self::$PropProduct];
        $deal = self::GetPropDeal($dealId, $prodID);

        //Ответственный
        $arFields['PROPERTY_VALUES'][self::$PropManager]['n0']['VALUE'] = $deal['DEAL']['ASSIGNED_BY_ID'];

        //Кол.-во дней отсрочки (по договору), Понятийныйсрок постоплаты
        if (!empty($deal['DEAL']['COMPANY_ID'])) {
            $CompanyDate = self::GetCompanyDate($deal['DEAL']['COMPANY_ID']);
            $arFields['PROPERTY_VALUES'][self::$daysDelay]['n0'] = $CompanyDate['UF_CRM_1568792246554'];
            $arFields['PROPERTY_VALUES'][self::$postPay]['n0'] = $CompanyDate['UF_CRM_1569309326933'];
        }

        //Договор  контрагента
        $arFields['PROPERTY_VALUES'][self::$PropContract]['n0']['VALUE'] = $deal['DEAL']['UF_CRM_1571831071'];
        //Номер заказа в 1с
        $arFields['PROPERTY_VALUES'][self::$PropOrderId]['n0']['VALUE'] = $deal['DEAL']['UF_CRM_1571647313'];
        //Компания   продавец
        $arFields['PROPERTY_VALUES'][self::$PropCompanySeller]['n0']['VALUE'] = $deal['DEAL']['UF_CRM_1571647560'];
        //Цена
        $arFields['PROPERTY_VALUES'][self::$PropPrice]['n0'] = $deal['PROD']['UF_PRICE_PLAN'];
        //Объём
        //$arFields['PROPERTY_VALUES'][self::$PropVolume]['n0'] = $deal['PROD']['UF_COUNT_PLAN'];
        //Условия оплаты
        $arFields['PROPERTY_VALUES'][self::$PropPaymentTerm]['n0']['VALUE'] = $deal['DEAL']['UF_CRM_1569319143'];
        //Сумма   ожидаемого поступления
        $arFields['PROPERTY_VALUES'][self::$PropExpectedAmount]['n0']['VALUE'] = $deal['DEAL']['UF_CRM_1571655965'];
        //Валюта
        $arFields['PROPERTY_VALUES'][self::$PropCurrency]['n0']['VALUE'] = $deal['DEAL']['CURRENCY_ID'];
        //Условия   поставки
        $arFields['PROPERTY_VALUES'][self::$PropTermsDelivery]['n0']['VALUE'] = $deal['DEAL']['UF_CRM_1569316998'];
        //Выход с завода
        $arFields['PROPERTY_VALUES'][self::$PropFromFactory]['n0']['VALUE'] = $deal['DEAL']['UF_CRM_1571649281'];
        //Выход из порта
        $arFields['PROPERTY_VALUES'][self::$PropExitPort]['n0']['VALUE'] = $deal['DEAL']['UF_CRM_1571651825'];
        //Дата   коносамента или инвойса для срока поступления
        $arFields['PROPERTY_VALUES'][self::$PropInvoiceDate]['n0']['VALUE'] = $deal['DEAL']['UF_CRM_1569494555'];

        //Срок оплаты, Срок оплаты от понятийной даты
        $ShipDate = $arFields['PROPERTY_VALUES'][self::$ShipDate]['n0']['VALUE'];
        $daysDelay = $arFields['PROPERTY_VALUES'][self::$daysDelay]['n0'];
        $postPay = $arFields['PROPERTY_VALUES'][self::$postPay]['n0'];
        $arFields['PROPERTY_VALUES'][self::$timePay]['n0']['VALUE'] = self::TimePay($ShipDate, $daysDelay); //TODO (+ БП Срок окончания отсрочки)
        $arFields['PROPERTY_VALUES'][self::$timePayConcept]['n0']['VALUE'] = self::TimePay($ShipDate, $postPay);


        //Кол-во дней просрочки	,
        $timePay = $arFields['PROPERTY_VALUES'][self::$timePay]['n0']['VALUE'];
        $arFields['PROPERTY_VALUES'][self::$dayOverdue]['n0'] = self::daysOverdue($timePay);

        //Кол-во дней просрочки от понятийного срока постоплаты
        $timePayConcept = $arFields['PROPERTY_VALUES'][self::$timePayConcept]['n0']['VALUE'];
        $arFields['PROPERTY_VALUES'][self::$dayOverdueConcept]['n0'] = self::daysOverdue($timePayConcept);

        //Разница между сегодняшней датой и предполагаемой датой поступления
        $arFields['PROPERTY_VALUES'][self::$PropDifference]['n0']['VALUE'] = self::daysOverdue($timePay);

        //Ориентировочная дата платежа
        $arFields['PROPERTY_VALUES'][self::$EstimatePayDate]['n0']['VALUE'] = $arFields['PROPERTY_VALUES'][self::$timePayConcept]['n0']['VALUE'];

        //Ориентировочная сумма платежа
        $arFields['PROPERTY_VALUES'][self::$EstimatePaySum]['n0'] = $arFields['PROPERTY_VALUES'][self::$SumRemaining]['n0'];

        //Дата отправки напоминания
        $arFields['PROPERTY_VALUES'][self::$DateReminders]['n0']['VALUE'] = self::DateFiling($deal['DEAL']['ID'], self::$type['501']);
        //Дата отправки требования
        $arFields['PROPERTY_VALUES'][self::$DateRequirements]['n0']['VALUE'] = self::DateFiling($deal['DEAL']['ID'], self::$type['502']);
        //Дата отправки претензии
        $arFields['PROPERTY_VALUES'][self::$DateClaim]['n0']['VALUE'] = self::DateFiling($deal['DEAL']['ID'], self::$type['503']);
        //Дата отправки предарбитража
        $arFields['PROPERTY_VALUES'][self::$DateArbitration]['n0']['VALUE'] = self::DateFiling($deal['DEAL']['ID'], self::$type['504']);
        //Дата подачи искового заявления
        $arFields['PROPERTY_VALUES'][self::$DateStatement]['n0']['VALUE'] = self::DateFiling($deal['DEAL']['ID'], self::$type['505']);
        return true;
    }

    //Расчетные поля:(при обновлении)
    function onBeforeUpdateReceivables(&$arFields)
    {
        if ($arFields['IBLOCK_ID'] !== self::$IblockId) return true;

        $key = key($arFields['PROPERTY_VALUES'][self::$ReceiptContract]);
        $DateContract = reset($arFields['PROPERTY_VALUES'][self::$ReceiptContract][$key]);

        $key = key($arFields['PROPERTY_VALUES'][self::$ReceiptDelay]);
        $DateDelay = reset($arFields['PROPERTY_VALUES'][self::$ReceiptDelay][$key]);

        if (!empty($DateContract) && !empty($DateDelay)) {
            $DateContract = strtotime($DateContract);
            $DateDelay = strtotime($DateDelay);

            $day = ($DateDelay - $DateContract) / 86400;
            $arFields['PROPERTY_VALUES'][self::$DelayInDays]['n0'] = ($day > 0)?$day:0;
        }
        return true;
    }

    //Добавление элемента Дебиторская задолженность
    function OnAddReceivables(&$arFields)
    {
        if ($arFields['IBLOCK_ID'] !== self::$IblockShipmentId) return true;
        if (!$arFields['RESULT']) return true;

        \CModule::IncludeModule("iblock");
        $ElShipment = self::GetElementShipment($arFields['RESULT']);

        $el = new \CIBlockElement;

        $PROP = array();
        $PROP[self::$ShipDate] = Array("n0" => Array("VALUE" => $ElShipment['DATE']));
        $PROP[self::$NumberShipment] = Array("n0" => Array("VALUE" => $ElShipment['NUMBER']));
        $PROP[self::$Client] = Array("n0" => Array("VALUE" => $ElShipment['COMPANY']));
        $PROP[self::$PropDeal] = Array("n0" => Array("VALUE" => $ElShipment['DEAL']));
        $PROP[self::$SumStart] = Array("n0" => $ElShipment['AMOUNT']);
        $PROP[self::$SumRemaining] = Array("n0" => $ElShipment['AMOUNT']);
        $PROP[self::$PropVolume] = Array("n0" => $ElShipment['OBYEM']);


        $PROP[self::$PropProduct] = $ElShipment['PRODUCT'];

        $arLoadProductArray = Array(
            "MODIFIED_BY" => ($arFields['MODIFIED_BY']) ? $arFields['MODIFIED_BY'] : 1,
            "IBLOCK_ID" => self::$IblockId,
            "PROPERTY_VALUES" => $PROP,
            "NAME" => $ElShipment['ID'],
            "ACTIVE" => "Y",
        );

        $PRODUCT_ID = $el->Add($arLoadProductArray);
        return true;
    }

    function OnDealHandler($arFields)
    {
        self::UpdateReceivablesProp($arFields['ID']);
        return true;
    }

    //Элемент отгрузки
    function GetElementShipment($ElementID)
    {
        $Res = [];

        $arSelect = Array(
            'ID',
            'PROPERTY_' . self::$ShipmentDateShipment,
            'PROPERTY_' . self::$ShipmentCompany,
            'PROPERTY_' . self::$ShipmentNumberShipment,
            'PROPERTY_' . self::$ShipmentAmount,
            'PROPERTY_' . self::$ShipmentDeal,
            'PROPERTY_' . self::$ShipmentProduct,
            'PROPERTY_' . self::$ShipmentObyem,
        );
        $arFilter = Array(
            "IBLOCK_ID" => self::$IblockShipmentId,
            "ID" => $ElementID
        );

        $res = \CIBlockElement::GetList(
            Array(),
            $arFilter,
            false,
            false,
            $arSelect
        );

        while ($ob = $res->GetNextElement()) {
            $Element = $ob->GetFields();
            $Res['ID'] = $Element['ID'];
            $Res['NUMBER'] = $Element['PROPERTY_' . self::$ShipmentNumberShipment . '_VALUE'];
            $Res['COMPANY'] = $Element['PROPERTY_' . self::$ShipmentCompany . '_VALUE'];
            $Res['DEAL'] = $Element['PROPERTY_' . self::$ShipmentDeal . '_VALUE'];
            $Res['AMOUNT'] = (int)$Element['PROPERTY_' . self::$ShipmentAmount . '_VALUE'];
            $Res['DATE'] = $Element['PROPERTY_' . self::$ShipmentDateShipment . '_VALUE'];
            $Res['PRODUCT'] = $Element['PROPERTY_' . self::$ShipmentProduct . '_VALUE'];
            $Res['OBYEM'] = $Element['PROPERTY_' . self::$ShipmentObyem . '_VALUE'];
        }

        return $Res;
    }

    function includeModules()
    {
        if (\CModule::IncludeModule("crm") && \CModule::IncludeModule("iblock")) {
            return true;
        }

        return false;
    }

    function GetCompanyDate($companyId)
    {
        $companyId = (int)$companyId;

        $dbResMultiFields = \CCrmCompany::GetList(Array(), Array('ID' => $companyId), Array(), false);
        $CompanyDate = [];

        while ($arMultiFields = $dbResMultiFields->Fetch()) {
            $CompanyDate['UF_CRM_1568792246554'] = $arMultiFields ['UF_CRM_1568792246554'];
            $CompanyDate['UF_CRM_1569309326933'] = $arMultiFields ['UF_CRM_1569309326933'];
        }

        return $CompanyDate;
    }

    function DateFiling($dealId, $type)
    {
        $Date = '';

        $arSelect = Array('NAME', 'PROPERTY_DATA_PODACHI');
        $arFilter = Array('IBLOCK_ID' => IntVal(self::$IblockCourtOrderId), 'PROPERTY_SDELKA' => $dealId, '%PROPERTY_TIP_VALUE' => $type);

        $res = \CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize" => 1), $arSelect);
        while ($ob = $res->GetNextElement()) {
            $elem = $ob->GetFields();
            $Date = $elem['PROPERTY_DATA_PODACHI_VALUE'];
        }
        return $Date;
    }

    function TimePay($date, $day, $BPdate = 0)
    {
        $day += $BPdate;
        if (!is_int((int)$day)) return $date;
        if ($TimePay = date('d.m.Y', strtotime($date . '+' . $day . 'days'))) return $TimePay;

        return $date;
    }

    function daysOverdue($datePay)
    {
        $today = date('d.m.Y');

        $today = strtotime($today);
        $datePay = strtotime($datePay);

        $day = ($today - $datePay) / 86400;
        if ($day > 0) return $day;
        return 0;
    }
    
	function SetRight($arParams){
        if($arParams['IBLOCK_ID'] != self::$IblockCourtOrderId)
            return true;
        
		// Судебный порядок
        if($arParams['IBLOCK_ID'] == self::$IblockCourtOrderId ){
        	$ManagerId = (int)$arParams['CREATED_BY'];
        	if($ManagerId > 0)
        		IblockElementRight(self::$IblockCourtOrderId, $arParams['ID'], $ManagerId);
        }
    	return true;
    }

    //Поля из сделки
    function GetPropDeal($DealID, $productId)
    {
        $Prop = [];

        $Select = array(
            'ID',
            'COMPANY_ID',
            'ASSIGNED_BY_ID',
            'UF_CRM_1571831071', //Договор   контрагента
            'UF_CRM_1571647313', //Номер заказа
            'UF_CRM_1571647560', //Компания   продавец
            'UF_CRM_1569319143', //Условия оплаты
            'UF_CRM_1571655965', //Сумма ТР
            'UF_CRM_1569316998', //Базис поставки
            'UF_CRM_1571649281', //Дата ухода с завода
            'UF_CRM_1571651825', //Дата ухода судна(факт)
            'UF_CRM_1569494555', //Дата прихода к клиенту судна(план)
            'CURRENCY_ID', //Валюта Сделки

        );
        $arFilterD = ['CHECK_PERMISSIONS' => 'N', 'ID' => $DealID]  ;
        $deal = \CCrmDeal::GetList(Array(), $arFilterD, $Select, false);
        while ($res = $deal->Fetch()){
            $Prop['DEAL'] = $res;
        }

        $dealPrId = 0;
        $arFilterP = ['CHECK_PERMISSIONS' => 'N', 'OWNER_ID' => $DealID, 'PRODUCT_ID' => $productId];
        $dbRes = \CAllCrmProductRow::GetList(['ID'], $arFilterP, ['ID']);
        while ($data = $dbRes->Fetch())
        {
            $dealPrId = $data['ID'];
        }

        $prOb = RegisterTable::GetList(array(
            'filter'=>['UF_DEAL' => $DealID, 'UF_OWNER_ID' => $dealPrId, 'UF_PRODUCT_ID' => $productId],
            'select'=>[
                'UF_PRICE_PLAN',
                'UF_COUNT_PLAN'
            ]
        ));
        while($pr = $prOb->Fetch()){
            $Prop['PROD'] = $pr;
        }

        return $Prop;

    }

    function UpdateReceivablesProp($dealId)
    {
        $Element = [];
        $arSelect = Array('ID', 'PROPERTY_'.self::$PropProduct);
        $arFilter = Array("IBLOCK_ID"=>self::$IblockId, 'PROPERTY_'.self::$PropDeal => $dealId);
        $res = \CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50), $arSelect);
        while($ob = $res->GetNextElement())
        {
            $arFields = $ob->GetFields();
            $Element['ELEMENTS'][] = $arFields;
        }
        $el = new \CIBlockElement;
        foreach ($Element['ELEMENTS'] as $ELEMENT){
            $deal = self::GetPropDeal($dealId, $ELEMENT['PROPERTY_'.self::$PropProduct.'_VALUE']);
            $PROP = array();
            //Договор  контрагента
            $PROP[self::$PropContract] = $deal['DEAL']['UF_CRM_1571831071'];
            //Номер заказа в 1с
            $PROP[self::$PropOrderId]= $deal['DEAL']['UF_CRM_1571647313'];
            //Компания   продавец
            $PROP[self::$PropCompanySeller]= $deal['DEAL']['UF_CRM_1571647560'];
            //Цена
            $PROP[self::$PropPrice] = $deal['PROD']['UF_PRICE_PLAN'];
            //Объём
            $PROP[self::$PropVolume] = $deal['PROD']['UF_COUNT_PLAN'];
            //Условия оплаты
            $PROP[self::$PropPaymentTerm]= $deal['DEAL']['UF_CRM_1569319143'];
            //Сумма   ожидаемого поступления
            $PROP[self::$PropExpectedAmount]= $deal['DEAL']['UF_CRM_1571655965'];
            //Валюта
            $PROP[self::$PropCurrency] = $deal['DEAL']['CURRENCY_ID'];
            //Условия   поставки
            $PROP[self::$PropTermsDelivery]= $deal['DEAL']['UF_CRM_1569316998'];
            //Выход с завода
            $PROP[self::$PropFromFactory]= $deal['DEAL']['UF_CRM_1571649281'];
            //Выход из порта
            $PROP[self::$PropExitPort]= $deal['DEAL']['UF_CRM_1571651825'];
            //Дата   коносамента или инвойса для срока поступления
            $PROP[self::$PropInvoiceDate] = $deal['DEAL']['UF_CRM_1569494555'];
            \CIBlockElement::SetPropertyValuesEx($ELEMENT['ID'], self::$IblockId, $PROP);
        }
    }

    //Разница между сегодняшней датой и предполагаемой датой поступления  -- Агент(каждый 86400сек)
    static function UpdateDelayAgent()
    {
        \CModule::IncludeModule('iblock');
        $arSelect = Array('ID', 'PROPERTY_'.self::$timePay, 'PROPERTY_'.self::$timePayConcept);
        $arFilter = Array(
            "IBLOCK_ID" => self::$IblockId,
        );
        $resOb = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
        while ($res = $resOb->fetch()){
            $PROP = array();
            $d1 = $res['PROPERTY_'.self::$timePay.'_VALUE'];
            if (strlen($d1) > 0){
                $PROP[self::$dayOverdue] = self::daysOverdue($d1);
                \CIBlockElement::SetPropertyValuesEx($res['ID'], self::$IblockId, $PROP);
            }

            $d2 = $res['PROPERTY_'.self::$timePayConcept.'_VALUE'];
            if (strlen($d2) > 0){
                $PROP[self::$dayOverdueConcept] = self::daysOverdue($d2);
                \CIBlockElement::SetPropertyValuesEx($res['ID'], self::$IblockId, $PROP);
            }
        }
        return "Serv\Receivables::UpdateDelayAgent();";
    }

    function UpdateTypeDate(&$arFields)
    {
        if ($arFields['IBLOCK_ID'] !== self::$IblockSud) return true;

        $deal = $arFields['PROPERTY_VALUES'][self::$SudPropDeal]['n0']['VALUE'];
        $date = $arFields['PROPERTY_VALUES'][self::$SudPropDate]['n0']['VALUE'];
        $type = $arFields['PROPERTY_VALUES'][self::$SudPropType];

        \CModule::IncludeModule('iblock');

        if ($deal && strlen($date) >0){
            $arSelect = Array('ID');
            $arFilter = Array(
                "IBLOCK_ID" => self::$IblockId,
                "=PROPERTY_".self::$PropDeal => $deal,
            );
            $resOb = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
            while ($res = $resOb->fetch()){
                $PROP = array();
                $PROP[self::$type2[$type]] = $date;
                \CIBlockElement::SetPropertyValuesEx($res['ID'], self::$IblockId, $PROP);
            }
        }
        return true;
    }
    function OnAfterIBlockElementUpdateSetRight($fields){
        
        
        if($fields['IBLOCK_ID'] != self::$IblockId)
            return true;
        
        
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


}