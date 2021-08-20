<?

namespace Serv;

use Serv\Register\RegisterTable;
use Bitrix\Main\Diag;

class PlanfactClass
{
    static $IblockId = 32;

    //Каталог
    static $catalogId = 26;

    /*Отгрузка*/
    static $IblockShipmentId = 38; //Отгрузка
    static $ShipmentDateShipment = '216'; // Дата отгрузки
    static $ShipmentProduct = '217'; // Товар
    static $ShipmentProductCount = '317'; // Количество
    static $ShipmentProductsCounts = '436'; // Количество
    static $ShipmentCompany = '220'; // Компания
    static $ShipmentDeal = '219'; // Сделка
    /*Отгрузка*/

    static $ConfirfField = '190'; // Статус согласования
    static $ConfirfValue = '483'; // Согласован
    static $AfterConfirfValue = '578'; // Согласован

    static $ArhiveIblockId = 36;
    static $ArhiveMonth = 2; //Количество дней для архивации элементов.
    static $PropertyMonth = '123';
    
    

    static $PropHistoryPlan = '188'; //История коректировочного плана
    static $PropsCorrectPlan = ['125', '185', '186', '187']; //Коректировочные планы
    static $PropsCorrectPlanName = ['125'=>'Коректировочный план 1', '185'=>'Коректировочный план 2', '186'=>'Коректировочный план 3', '187'=>'Коректировочный план 4'];
    static $PropManager = '122'; //Ответственный менеджер
    static $PropDeal = '316'; //Сделка
    static $PropPeriod = '180'; //Период
    static $PropStatus = '121'; //Статус
    static $PropClient = '312'; //Клиент
    static $PropProduct = '178'; //Продукт
    static $PropComments = '127'; //комментарии

    static $PropPlan1 = '124';
    static $PropPlan2 = '181';
    static $PropPlan3 = '182';
    static $PropPlan4 = '183';
    static $PropPlanSum = '346';

    static $PropFact1 = '126';
    static $PropFact2 = '313';
    static $PropFact3 = '314';
    static $PropFact4 = '315';
    static $PropFact1Sum = '348';

    static $PropCorrectPlan1 = '125';
    static $PropCorrectPlan2 = '185';
    static $PropCorrectPlan3 = '186';
    static $PropCorrectPlan4 = '187';
    static $PropCorrectPlanSum = '347';

    static $changedDate = false;
    static $changedProducts = false;



    static $Month = [
        '01' => 'Январь',
        '02' => 'Февраль',
        '03' => 'Март',
        '04' => 'Апрель',
        '05' => 'Май',
        '06' => 'Июнь',
        '07' => 'Июль',
        '08' => 'Август',
        '09' => 'Сентябрь',
        '10' => 'Октябрь',
        '11' => 'Ноябрь',
        '12' => 'Декабрь',
    ];

    static $MonthPropId = [
        '01' => '118',
        '02' => '119',
        '03' => '120',
        '04' => '121',
        '05' => '122',
        '06' => '123',
        '07' => '124',
        '08' => '125',
        '09' => '126',
        '10' => '127',
        '11' => '128',
        '12' => '129',
    ];

    //Периоды
    static $Periods = array(
        1 => array('FROM' => '1', 'TO' => '7'),
        2 => array('FROM' => '8', 'TO' => '15'),
        3 => array('FROM' => '16', 'TO' => '22'),
        4 => array('FROM' => '23', 'TO' => '31'),
    );

    static $DateShipment = 'UF_CRM_1569329439';


    // Архивация элементов
    function ArchiveAgents()
    {
        self::ArchiveCreate();
        return 'PlanfactClass::ArchiveAgents();';
    }

    function ArchiveCreate()
    {
    	\CModule::IncludeModule("iblock");
        $Currentmonth = date('n');

        $SelectMonth = array($Currentmonth, $Currentmonth - 1);

        $property_enums = \CIBlockPropertyEnum::GetList(
            Array('DEF' => 'DESC', 'SORT' => 'ASC'),
            Array('IBLOCK_ID' => self::$IblockId, 'PROPERTY_ID' => self::$PropertyMonth, 'XML_ID' => $SelectMonth)
        );

        while ($prop = $property_enums->Fetch())
            $CurrentMonths[] = $prop['ID'];

        $filter = array(
            'IBLOCK_ID' => self::$IblockId,
            '!=PROPERTY_' . self::$PropertyMonth => $CurrentMonths
        );
        $arSelect = array('ID', 'NAME');
        $rsSelectElement = \CIBlockElement::GetList(Array(), $filter, false, Array(), $arSelect);
        while ($ar = $rsSelectElement->Fetch()) {
            $ar['PROPERTIES'] = [];
            $id = $ar['ID'];
            $elements[$ar['ID']] =& $ar;
            unset($ar);

            \CIBlockElement::GetPropertyValuesArray($elements, $filter['IBLOCK_ID'], $filter);


           self::CreateCopy($elements[$id]);
           self::DeleteElement($id);

        }

    }

    function CreateCopy($arFields)
    {
        $NewElement = array(
            'IBLOCK_ID' => self::$ArhiveIblockId,
            'IBLOCK_SECTION_ID' => false,
            'ACTIVE' => 'Y',
            'NAME' => $arFields['NAME'].'# COPY'.$arFields['ID'],
        );
        foreach ($arFields['PROPERTIES'] as $PropCode => $PropItem) 
            $Property[$PropCode] = $PropItem['VALUE'];

        $NewElement['PROPERTY_VALUES'] = $Property;
       
        $el = new \CIBlockElement;
        
		$res = $el->Add($NewElement);
		
		
    }

    function DeleteElement($id)
    {
        \CIBlockElement::Delete($id);

    }

    //Автоматическая подстановка значений полей "Ответственный менеджер"
    function PropertyControl(&$arFields)
    {

        if ($arFields['IBLOCK_ID'] !== self::$IblockId) 
        	return true;

        if(count($arFields['PROPERTY_VALUES']) <= 0)
        	return true;
        
        
        $ClientKey = key($arFields['PROPERTY_VALUES'][static::$PropClient]);
        $ClientId = reset($arFields['PROPERTY_VALUES'][self::$PropClient][$ClientKey]);

        $status = self::GetStatus($ClientId);
        $statusKey = key($arFields['PROPERTY_VALUES'][static::$PropStatus]);
        $arFields['PROPERTY_VALUES'][self::$PropStatus][$statusKey] = $status;

        //Получаем данные из поля "Ответственный" карточки компании и записываем в поле "Ответственный менеджер"
        /*if (\CModule::IncludeModule("crm")):

            $dbResMultiFields = \CCrmCompany::GetByID($ClientId);
            $Manager = $dbResMultiFields['ASSIGNED_BY_ID'];

            if (!empty($Manager)) {
                $key = key($arFields['PROPERTY_VALUES'][self::$PropManager]);
                $arFields['PROPERTY_VALUES'][self::$PropManager][$key]['VALUE'] = $Manager;
            }
        endif;*/

        return true;

    }

    //обновление историю корректировочного плана
    function UpdatePlanHistory(&$arFields)
    {
        if ($arFields['IBLOCK_ID'] !== self::$IblockId) 
        	return true;

        if(count($arFields['PROPERTY_VALUES']) <= 0)
        	return true;
        
        $PlanSumKey = key($arFields['PROPERTY_VALUES'][static::$PropPlanSum]);
        $arFields['PROPERTY_VALUES'][static::$PropPlanSum][$PlanSumKey] = self::PlanSum($arFields);
        
        $CPlanSumKey = key($arFields['PROPERTY_VALUES'][static::$PropCorrectPlanSum]);
        $arFields['PROPERTY_VALUES'][static::$PropCorrectPlanSum][$CPlanSumKey] = self::CorrectPlanSum($arFields);

        $FactKey = key($arFields['PROPERTY_VALUES'][static::$PropFact1Sum]);
        $arFields['PROPERTY_VALUES'][static::$PropFact1Sum][$FactKey] = self::FactSum($arFields);

        \CModule::IncludeModule("iblock");
        $props = [];

        $res = \CIBlockElement::GetPropertyValues($arFields['IBLOCK_ID'], array('ID' => $arFields['ID']), false, array("ID" => self::$PropsCorrectPlan));
        while ($row = $res->Fetch()) {
            $props = $row;
        }

        $story = '';
        $date = date("d.m.Y");

        foreach ($props as $key => $values) {
            if (in_array($key, self::$PropsCorrectPlan)) {
                $keyProp = key($arFields['PROPERTY_VALUES'][$key]);
                if ((float)$arFields['PROPERTY_VALUES'][$key][$keyProp] !== (float)$values) {
                    $values = (float)$values;
                    $story .= self::$PropsCorrectPlanName[$key] . ' ' . $date . ' = ' . $values . '</br>';
                }
            }
        }

        if ($story !== '') {
            $keyStory = key($arFields['PROPERTY_VALUES'][self::$PropHistoryPlan]);
            $arFields['PROPERTY_VALUES'][self::$PropHistoryPlan][$keyStory]['VALUE']['TEXT'] = $story . $arFields['PROPERTY_VALUES'][self::$PropHistoryPlan][$keyStory]['VALUE']['TEXT'];
        }

        return true;
    }

    //Поля Факт(1,2,3,4) план факт
    function OnUpdatePropFacts(&$arFields)
    {
        if ($arFields['IBLOCK_ID'] !== self::$IblockShipmentId) return true;

        if (!$arFields['RESULT']) return true;

        \CModule::IncludeModule("iblock");

        $Res['PRODUKT'] = $arFields['PROPERTY_VALUES'][self::$ShipmentProduct];
        $Res['KOMPANIYA'] = $arFields['PROPERTY_VALUES'][self::$ShipmentCompany]['n0']['VALUE'];

        $i = 0;
        foreach ($Res['PRODUKT'] as $id){
            $Res['COUNT'][$id] = $arFields['PROPERTY_VALUES'][self::$ShipmentProductsCounts]['n'.$i];
            ++$i;
        }
        $Res['DATE'] = $arFields['PROPERTY_VALUES'][self::$ShipmentDateShipment]['n0']['VALUE'];

        $Fact = self::CreateFact($Res['DATE'], $Res['COUNT']);
        self::PropFactUpdate($Res['KOMPANIYA'], $Res['PRODUKT'], $Fact, $Res['DATE']);
        return true;
    }

    function Delete_PF_Element($arFields)
    {
        $filter = array(
            'IBLOCK_ID' => self::$IblockId,
            '=PROPERTY_' . self::$PropDeal => (int)$arFields,
        );
        $arSelect = array('ID', 'PROPERTY_'.self::$ConfirfField, 'PROPERTY_'.self::$PropPlan1, 'PROPERTY_'.self::$PropPlan2, 'PROPERTY_'.self::$PropPlan3, 'PROPERTY_'.self::$PropPlan4, );
        $rsSelectElement = \CIBlockElement::GetList(Array(), $filter, false, Array(), $arSelect);

        $PROP[self::$PropCorrectPlan1] = '';
        $PROP[self::$PropCorrectPlan2] = '';
        $PROP[self::$PropCorrectPlan3] = '';
        $PROP[self::$PropCorrectPlan4] = '';
        while ($ar = $rsSelectElement->Fetch()) {
            if (empty($ar['PROPERTY_'.self::$PropPlan1.'_VALUE']) &&
                empty($ar['PROPERTY_'.self::$PropPlan2.'_VALUE']) &&
                empty($ar['PROPERTY_'.self::$PropPlan3.'_VALUE']) &&
                empty($ar['PROPERTY_'.self::$PropPlan4.'_VALUE']) || $ar['PROPERTY_'.self::$ConfirfField.'_ENUM_ID'] != self::$ConfirfValue){
                self::DeleteElement($ar['ID']);
            }else{
                \CIBlockElement::SetPropertyValuesEx($ar['ID'], self::$IblockId, $PROP);
            }
        }
    }

    function PropFactUpdate($CompanyId, $ProductId, $Fact, $date)
    {
        $time = strtotime($date);
        $month = date("m",$time);

        $arSelect = Array('ID', 'NAME', 'PROPERTY_PRODUKT','PROPERTY_MESYATS', 'PROPERTY_KOMPANIYA', 'PROPERTY_SDELKA','PROPERTY_FAKT','PROPERTY_FAKT_2','PROPERTY_FAKT_3','PROPERTY_FAKT_4',);
        $arFilter = Array(
            "IBLOCK_ID"=>self::$IblockId,
            'PROPERTY_PRODUKT'=> $ProductId,
            'PROPERTY_KOMPANIYA'=> $CompanyId,
            'PROPERTY_MESYATS_VALUE'=> self::$Month[$month],
        );

        $res = \CIBlockElement::GetList(
            Array('PROPERTY_PRIKHOD'=>'DESC'),
            $arFilter,
            false,
            false,
            $arSelect
        );

        while($ob = $res->GetNextElement()){
            $PFact = $ob->GetFields();
            if ($Fact){
                $PROPERTY_VALUE = $PFact['PROPERTY_'.$Fact['CODE'].'_VALUE'] + $Fact['VALUE'][$PFact['PROPERTY_PRODUKT_VALUE']];
                $PROPERTY_CODE = $Fact['CODE'];
                \CIBlockElement::SetPropertyValues($PFact['ID'], self::$IblockId, $PROPERTY_VALUE, $PROPERTY_CODE);
            }

        }
    }

    function CreateFact($Date, $ProductCount)
    {

        $time = strtotime($Date);
        $day = date("d",$time);

        switch ($day){
            case ($day >= self::$Periods[1]['FROM'] && $day <= self::$Periods[1]['TO']):
                return ['CODE' => 'FAKT', 'VALUE'=> $ProductCount];
                break;
            case ($day >= self::$Periods[2]['FROM'] && $day <= self::$Periods[2]['TO']):
                return ['CODE' => 'FAKT_2', 'VALUE'=> $ProductCount];
                break;
            case ($day >= self::$Periods[3]['FROM'] && $day <= self::$Periods[3]['TO']):
                return ['CODE' => 'FAKT_3', 'VALUE'=> $ProductCount];
                break;
            case ($day >= self::$Periods[4]['FROM'] && $day <= self::$Periods[4]['TO']):
                return ['CODE' => 'FAKT_4', 'VALUE'=> $ProductCount];
                break;
        }

        return false;

    }

    function GetElementShipment($ElementID)
    {
        $Res = [];

        $arSelect = Array(
            'ID',
            'NAME',
            'PROPERTY_'.self::$ShipmentDateShipment,
            'PROPERTY_'.self::$ShipmentCompany,
            'PROPERTY_'.self::$ShipmentProduct,
            'PROPERTY_'.self::$ShipmentProductCount,
            'PROPERTY_'.self::$ShipmentDeal,
        );
        $arFilter = Array(
            "IBLOCK_ID"=>self::$IblockShipmentId,
            "ID" => $ElementID
        );

        $res = \CIBlockElement::GetList(
            Array(),
            $arFilter,
            false,
            false,
            $arSelect
        );

        while($ob = $res->GetNextElement()){
            $Element = $ob->GetFields();
            $Res['PRODUKT'] = $Element['PROPERTY_'.self::$ShipmentProduct.'_VALUE'];
            $Res['KOMPANIYA'] = $Element['PROPERTY_'.self::$ShipmentCompany.'_VALUE'];
            $Res['SDELKA'] = $Element['PROPERTY_'.self::$ShipmentDeal.'_VALUE'];
            $Res['COUNT'] = $Element['PROPERTY_'.self::$ShipmentProductCount.'_VALUE'];
            $Res['DATE'] = $Element['PROPERTY_'.self::$ShipmentDateShipment.'_VALUE'];
        }

        return $Res;
    }

    function GetStatus($StatusId)
    {
        $status = 'Нет данных';
        $ff = \CCrmCompany::GetList(Array(),Array('ID'=>$StatusId), Array('UF_CRM_1568792370086'), false)->Fetch();

        if(!empty($ff['UF_CRM_1568792370086'])){
            $ar = \CUserFieldEnum::GetList(array(), array("ID" => $ff['UF_CRM_1568792370086']))->Fetch();
            $status = $ar['VALUE'];
        }
        return $status;
    }
    
    function SetRight($arParams){
    	if($arParams['IBLOCK_ID'] != self::$IblockId)
    		return true;

    	$ManagerId = $arParams['PROPERTY_VALUES'][self::$PropManager];

    	$ManagerId = (int)array_shift($ManagerId)['VALUE'];

    	if($ManagerId <= 0 ){
    		global $USER;
    		$ManagerId = $USER->GetID();
    	}


    	IblockElementRight(self::$IblockId, $arParams['ID'], $ManagerId);
    	
    	return true;
    }

    function PlanSum($arFields)
    {
        $plan1 = reset($arFields['PROPERTY_VALUES'][self::$PropPlan1]);
        $plan2 = reset($arFields['PROPERTY_VALUES'][self::$PropPlan2]);
        $plan3 = reset($arFields['PROPERTY_VALUES'][self::$PropPlan3]);
        $plan4 = reset($arFields['PROPERTY_VALUES'][self::$PropPlan4]);

        return $plan1 + $plan2 + $plan3 + $plan4;
    }

    function CorrectPlanSum($arFields)
    {
        $Cplan1 = reset($arFields['PROPERTY_VALUES'][self::$PropCorrectPlan1]);
        $Cplan2 = reset($arFields['PROPERTY_VALUES'][self::$PropCorrectPlan2]);
        $Cplan3 = reset($arFields['PROPERTY_VALUES'][self::$PropCorrectPlan3]);
        $Cplan4 = reset($arFields['PROPERTY_VALUES'][self::$PropCorrectPlan4]);

        return $Cplan1 + $Cplan2 + $Cplan3 + $Cplan4;

    }

    function FactSum($arFields)
    {
        $Fact1 = reset($arFields['PROPERTY_VALUES'][self::$PropFact1]);
        $Fact2 = reset($arFields['PROPERTY_VALUES'][self::$PropFact2]);
        $Fact3 = reset($arFields['PROPERTY_VALUES'][self::$PropFact3]);
        $Fact4 = reset($arFields['PROPERTY_VALUES'][self::$PropFact4]);

        return $Fact1 + $Fact2 + $Fact3 + $Fact4;

    }

    //Автоматическая фиксация плановых показателей на основании создания сделки.
    function CreatePFElements($arFields)
    {
        if (!$arFields['ID'] || !isset($_REQUEST['DEAL_PRODUCT_DATA']))
            return true;

        $dealProducts = self::deal_request_prod();

        if (!$dealProducts){
            return true;
        }

        self::AddPF($arFields, $dealProducts);

        return true;
    }

    function AddPF($arFields, $ProductTons)
    {

        \CModule::IncludeModule("iblock");
        \CModule::IncludeModule("bizproc");

        $today = date("d.m.Y H:i:s");

        $time = strtotime($arFields[self::$DateShipment]);
        $day = date("d", $time);
        $month = date("m", $time);

        $el = new \CIBlockElement;

        $PROP = array();
        $PROP[self::$PropClient] = Array("n0" => Array("VALUE" => $arFields['COMPANY_ID']));
        $PROP[self::$PropDeal] = Array("n0" => Array("VALUE" => $arFields['ID']));
        $PROP[self::$PropComments] = Array("n0" => Array("VALUE" => 'Дата созданияия:'.$today.' -- Комментарии:'.$arFields['COMMENTS']));
        $PROP[self::$PropertyMonth] = self::$MonthPropId[$month];
        $PROP[self::$PropManager] = Array("n0" => Array("VALUE" => $arFields['ASSIGNED_BY_ID']));

        $has_pf = self::has_monthPF($month);

        foreach ($ProductTons as $productT) {
            $PROP[self::$PropProduct] = Array("n0" => Array("VALUE" => $productT['PRODUCT_ID']));

            if ($has_pf){    // если планируемая дата отгрузки в текушем месяце
                $PROP[self::$ConfirfField] = self::$AfterConfirfValue;
                switch ($day) {
                    case ($day >= self::$Periods[1]['FROM'] && $day <= self::$Periods[1]['TO']):
                        $PROP[self::$PropCorrectPlan1] = Array("n0" => Array("VALUE" => $productT['COUNT']));
                        break;
                    case ($day >= self::$Periods[2]['FROM'] && $day <= self::$Periods[2]['TO']):
                        $PROP[self::$PropCorrectPlan2] = Array("n0" => Array("VALUE" => $productT['COUNT']));
                        break;
                    case ($day >= self::$Periods[3]['FROM'] && $day <= self::$Periods[3]['TO']):
                        $PROP[self::$PropCorrectPlan3] = Array("n0" => Array("VALUE" => $productT['COUNT']));
                        break;
                    case ($day >= self::$Periods[4]['FROM'] && $day <= self::$Periods[4]['TO']):
                        $PROP[self::$PropCorrectPlan4] = Array("n0" => Array("VALUE" => $productT['COUNT']));
                        break;
                }
            }else{
                switch ($day) {
                    case ($day >= self::$Periods[1]['FROM'] && $day <= self::$Periods[1]['TO']):
                        $PROP[self::$PropPlan1] = Array("n0" => Array("VALUE" => $productT['COUNT']));
                        break;
                    case ($day >= self::$Periods[2]['FROM'] && $day <= self::$Periods[2]['TO']):
                        $PROP[self::$PropPlan2] = Array("n0" => Array("VALUE" => $productT['COUNT']));
                        break;
                    case ($day >= self::$Periods[3]['FROM'] && $day <= self::$Periods[3]['TO']):
                        $PROP[self::$PropPlan3] = Array("n0" => Array("VALUE" => $productT['COUNT']));
                        break;
                    case ($day >= self::$Periods[4]['FROM'] && $day <= self::$Periods[4]['TO']):
                        $PROP[self::$PropPlan4] = Array("n0" => Array("VALUE" => $productT['COUNT']));
                        break;
                }
            }

            $arLoadProductArray = Array(
                "MODIFIED_BY" => ($arFields['MODIFIED_BY']) ? $arFields['MODIFIED_BY'] : 1,
                "IBLOCK_ID" => self::$IblockId,
                "PROPERTY_VALUES" => $PROP,
                "NAME" => 'D_'. $arFields['ID'],
                "ACTIVE" => "Y",
            );
            $ElementId = $el->Add($arLoadProductArray);
            if($ElementId > 0){
                $arErrorsTmp = array();
                $workflowTemplateId = 16;
                $wfId = \CBPDocument::StartWorkflow(
                    $workflowTemplateId,
                    array('lists', 'Bitrix\Lists\BizprocDocumentLists', $ElementId),
                    array(),
                    $arErrorsTmp
                );

            }
        }
    }

    //Автоматическая фиксация плановых показателей на основании обновления сделки.
    function UpdatePFElements($arFields)
    {
        $Products = self::deal_request_prod();
        $dealID = (int)$arFields['ID'];

        //Сделка проиграна или нет товаров
        if ($arFields['STAGE_SEMANTIC_ID'] == 'F' || !is_array($Products)){
            self::Delete_PF_Element($dealID);
            return true;
        }


        \CModule::IncludeModule("crm");
        \CModule::IncludeModule("iblock");

        $changedDate = false;
        $changedProducts = false;

        $modifiedProducts = array();
        $changData = array();

        $rr = \CCrmDeal::GetList(Array(), Array('ID' => $dealID),  Array('ID', 'COMPANY_ID', 'MODIFIED_BY', 'ASSIGNED_BY_ID', 'COMMENTS', self::$DateShipment),  false);
        if($arMultiFields = $rr->Fetch())
        {
            $deal = $arMultiFields;
            if (strtotime($arMultiFields[self::$DateShipment]) != strtotime($arFields[self::$DateShipment]) && !empty($arFields[self::$DateShipment])){
                $changData['DATE_SHIPMENT'] = $arFields[self::$DateShipment];
                $changedDate = true;
            }else{
                $changData['DATE_SHIPMENT'] = $arMultiFields[self::$DateShipment];
            }
        }


        if (is_array($Products)) {
            $DealProducts = array();
            $arFilter = array("OWNER_ID" => $dealID);
            $ProductRows= \CCrmProductRow::GetList(array(), $arFilter, false, false, array() ,array() );

            $deaPrId = array();
            while($ar_fields = $ProductRows->Fetch())
            {
                $deaPrId[] =$ar_fields['PRODUCT_ID'];

                $DealProducts[$ar_fields['PRODUCT_ID']]['ID'] = $ar_fields['ID'];
                $DealProducts[$ar_fields['PRODUCT_ID']]['OWNER_ID'] = $ar_fields['OWNER_ID'];
                $DealProducts[$ar_fields['PRODUCT_ID']]['PRODUCT_ID'] = $ar_fields['PRODUCT_ID'];
                $DealProducts[$ar_fields['PRODUCT_ID']]['MEASURE_CODE'] = $ar_fields['MEASURE_CODE'];
                $prOb = RegisterTable::GetList(array(
                    'filter'=>['UF_DEAL' => $dealID, 'UF_OWNER_ID' => $ar_fields['ID'], 'UF_PRODUCT_ID' => $ar_fields['PRODUCT_ID']],
                    'select'=>['UF_COUNT_PLAN']
                ));

                if($pr = $prOb->Fetch()){
                    $DealProducts[$ar_fields['PRODUCT_ID']]['COUNT'] = $pr['UF_COUNT_PLAN'];
                }
            }


            $createPF = array();
            $dPr = array();
            foreach ($Products as $key => $product){
                $dPr[] = $product['PRODUCT_ID'];
                if (!in_array($product['PRODUCT_ID'], $deaPrId)){
                    $createPF[$key] = $product;
                    unset($Products[$key]);
                }
            }

            $deletePF = array_diff($deaPrId, $dPr);

            //Новый товар
            if (count($createPF) > 0){
                self::AddPF($deal, $createPF);
            }

            //Удаленные элементы
            if (count($deletePF) > 0){
                self::Control_Pf_Element($dealID, $deletePF);
            }

            foreach ($Products as $product){
                if($DealProducts[$product['PRODUCT_ID']]['MEASURE_CODE'] != $product['MEASURE_CODE'] || $DealProducts[$product['PRODUCT_ID']]['COUNT'] != $product['COUNT'] ){
                    $modifiedProducts[$product['PRODUCT_ID']] = $product['PRODUCT_ID'];
                    $changedProducts = true;
                }
            }
            $changData['PRODUCTS'] = $Products;
        }

        //Если изменена планируемая дата отгрузки
        if ($changedDate){
            self::UpdatePF1($arFields, $changData);
        }elseif ($changedProducts){//Если изменены количества товаров
            self::UpdatePF2($arFields, $changData, $modifiedProducts);
        }

        return true;
    }

    function Control_Pf_Element($dealId, $PrId)
    {
        $filter = array(
            'IBLOCK_ID' => self::$IblockId,
            '=PROPERTY_' . self::$PropDeal => (int)$dealId,
            '=PROPERTY_' . self::$PropProduct => $PrId,
        );
        $arSelect = array('ID', 'PROPERTY_'.self::$ConfirfField,'PROPERTY_'.self::$PropPlan1, 'PROPERTY_'.self::$PropPlan2, 'PROPERTY_'.self::$PropPlan3, 'PROPERTY_'.self::$PropPlan4, );
        $rsSelectElement = \CIBlockElement::GetList(Array(), $filter, false, Array(), $arSelect);

        $PROP[self::$PropCorrectPlan1] = '';
        $PROP[self::$PropCorrectPlan2] = '';
        $PROP[self::$PropCorrectPlan3] = '';
        $PROP[self::$PropCorrectPlan4] = '';

        while ($ar = $rsSelectElement->Fetch()) {
            if (empty($ar['PROPERTY_'.self::$PropPlan1.'_VALUE']) &&
                empty($ar['PROPERTY_'.self::$PropPlan2.'_VALUE']) &&
                empty($ar['PROPERTY_'.self::$PropPlan3.'_VALUE']) &&
                empty($ar['PROPERTY_'.self::$PropPlan4.'_VALUE']) ||
                $ar['PROPERTY_'.self::$ConfirfField.'_ENUM_ID'] != self::$ConfirfValue){
                self::DeleteElement($ar['ID']);
            }else{
                \CIBlockElement::SetPropertyValuesEx($ar['ID'], self::$IblockId, $PROP);
            }
        }
    }

    function UpdatePF1($arFields, $changData){
        $today = date("d.m.Y H:i:s");

        $time = strtotime($changData['DATE_SHIPMENT']);
        $day = date("d", $time);
        $month = date("m", $time);


        $prId = array_keys($changData['PRODUCTS']);
        $pfelement = array();
        $pfagree = array();
        $filterArray = ['IBLOCK_ID'=>self::$IblockId, '=PROPERTY_316' => $arFields['ID'], '=PROPERTY_178'=>$prId];
        $selectArray = ['NAME', 'ID', 'PROPERTY_178', 'PROPERTY_190', 'PROPERTY_127'];

        $objpf = \CIBlockElement::GetList (
            array(),
            $filterArray,
            false,
            false,
            $selectArray
        );
        while ($row_objpf = $objpf -> Fetch()){
            $pfelement[$row_objpf['PROPERTY_178_VALUE']]['ID'] = $row_objpf['ID'];
            $pfelement[$row_objpf['PROPERTY_178_VALUE']]['COMMENTS'] = $row_objpf['PROPERTY_127_VALUE'];
            $pfagree[$row_objpf['PROPERTY_178_VALUE']] = $row_objpf['PROPERTY_190_ENUM_ID'];
        };

        $PROP = array();
        $PROP[self::$PropertyMonth] = self::$MonthPropId[$month];
        $PROP[self::$PropPlan1] = '';
        $PROP[self::$PropPlan2] = '';
        $PROP[self::$PropPlan3] = '';
        $PROP[self::$PropPlan4] = '';
        foreach ($changData['PRODUCTS'] as $prID=>$PRODUCT){
            $PROP[self::$PropComments] = 'Дата изменения:'.$today.' -- Комментарии:'.$arFields['COMMENTS']."\n".$pfelement[$prID]['COMMENTS'];
            switch ($day) {
                case ($day >= self::$Periods[1]['FROM'] && $day <= self::$Periods[1]['TO']):
                    $PROP[self::$PropPlan1] = $PRODUCT['COUNT'];
                    break;
                case ($day >= self::$Periods[2]['FROM'] && $day <= self::$Periods[2]['TO']):
                    $PROP[self::$PropPlan2] = $PRODUCT['COUNT'];
                    break;
                case ($day >= self::$Periods[3]['FROM'] && $day <= self::$Periods[3]['TO']):
                    $PROP[self::$PropPlan3] = $PRODUCT['COUNT'];
                    break;
                case ($day >= self::$Periods[4]['FROM'] && $day <= self::$Periods[4]['TO']):
                    $PROP[self::$PropPlan4] = $PRODUCT['COUNT'];
                    break;
            }

            if ($pfagree[$prID] == 483 || $pfagree[$prID] == self::$AfterConfirfValue){
                $PROP[self::$PropCorrectPlan1] = $PROP[self::$PropPlan1];
                $PROP[self::$PropCorrectPlan2] = $PROP[self::$PropPlan2];
                $PROP[self::$PropCorrectPlan3] = $PROP[self::$PropPlan3];
                $PROP[self::$PropCorrectPlan4] = $PROP[self::$PropPlan4];
                unset($PROP[self::$PropPlan1], $PROP[self::$PropPlan2], $PROP[self::$PropPlan3], $PROP[self::$PropPlan4] );

            }


            \CIBlockElement::SetPropertyValuesEx($pfelement[$prID]['ID'], self::$IblockId, $PROP);
        }
    }

    function UpdatePF2($arFields, $changData, $modifiedProducts){
        $today = date("d.m.Y H:i:s");

        $time = strtotime($changData['DATE_SHIPMENT']);
        $day = date("d", $time);
        $month = date("m", $time);


        $prId = array_keys($modifiedProducts);
        $pfelement = array();
        $pfagree = array();
        $filterArray = ['IBLOCK_ID'=>self::$IblockId, '=PROPERTY_316' => $arFields['ID'], '=PROPERTY_178'=>$prId];
        $selectArray = ['NAME', 'ID', 'PROPERTY_178', 'PROPERTY_190', 'PROPERTY_127'];

        $objpf = \CIBlockElement::GetList (
            array(),
            $filterArray,
            false,
            false,
            $selectArray
        );
        while ($row_objpf = $objpf -> Fetch()){
            $pfelement[$row_objpf['PROPERTY_178_VALUE']]['ID'] = $row_objpf['ID'];
            $pfelement[$row_objpf['PROPERTY_178_VALUE']]['COMMENTS'] = $row_objpf['PROPERTY_127_VALUE'];
            $pfagree[$row_objpf['PROPERTY_178_VALUE']] = $row_objpf['PROPERTY_190_ENUM_ID'];

        };

        $PROP = array();
        $PROP[self::$PropertyMonth] = self::$MonthPropId[$month];
        $PROP[self::$PropPlan1] = '';
        $PROP[self::$PropPlan2] = '';
        $PROP[self::$PropPlan3] = '';
        $PROP[self::$PropPlan4] = '';
        foreach ($modifiedProducts as $prID=>$PRODUCT){
            $PROP[self::$PropComments] ='Дата изменения:'.$today.' -- Комментарии:'.$arFields['COMMENTS']."\n".$pfelement[$prID]['COMMENTS'];
            switch ($day) {
                case ($day >= self::$Periods[1]['FROM'] && $day <= self::$Periods[1]['TO']):
                    $PROP[self::$PropPlan1] = $changData['PRODUCTS'][$PRODUCT]['COUNT'];
                    break;
                case ($day >= self::$Periods[2]['FROM'] && $day <= self::$Periods[2]['TO']):
                    $PROP[self::$PropPlan2] = $changData['PRODUCTS'][$PRODUCT]['COUNT'];
                    break;
                case ($day >= self::$Periods[3]['FROM'] && $day <= self::$Periods[3]['TO']):
                    $PROP[self::$PropPlan3] = $changData['PRODUCTS'][$PRODUCT]['COUNT'];
                    break;
                case ($day >= self::$Periods[4]['FROM'] && $day <= self::$Periods[4]['TO']):
                    $PROP[self::$PropPlan4] = $changData['PRODUCTS'][$PRODUCT]['COUNT'];
                    break;
            }

            if ($pfagree[$prID] == 483 || $pfagree[$prID] == self::$AfterConfirfValue){
                $PROP[self::$PropCorrectPlan1] = $PROP[self::$PropPlan1];
                $PROP[self::$PropCorrectPlan2] = $PROP[self::$PropPlan2];
                $PROP[self::$PropCorrectPlan3] = $PROP[self::$PropPlan3];
                $PROP[self::$PropCorrectPlan4] = $PROP[self::$PropPlan4];
                unset($PROP[self::$PropPlan1], $PROP[self::$PropPlan2], $PROP[self::$PropPlan3], $PROP[self::$PropPlan4] );
            }

            \CIBlockElement::SetPropertyValuesEx($pfelement[$prID]['ID'], self::$IblockId, $PROP);
        }

        return true;
    }

    function FixCompanyPF($arFields)
    {
        $compId = (int)$arFields['COMPANY_ID'];
        if ($compId > 0){
            $filterArray = ['IBLOCK_ID'=>self::$IblockId, '=PROPERTY_316' => $arFields['ID']];
            $selectArray = ['ID'];
            $PROP[self::$PropClient] = $compId;

            $objpf = \CIBlockElement::GetList (array(), $filterArray, false, false, $selectArray );
            while ($row_objpf = $objpf -> Fetch()){
                \CIBlockElement::SetPropertyValuesEx($row_objpf['ID'], self::$IblockId, $PROP);
            }
        }
    }

    function has_monthPF($month){

        //$now =  date("m", strtotime("now"));

        if ($month){
            $arSelect = Array('ID');
            $arFilter = Array(
                "IBLOCK_ID"=>self::$IblockId,
                'PROPERTY_123'=> self::$MonthPropId[$month],
                'PROPERTY_190' => self::$ConfirfValue
            );

            $res = \CIBlockElement::GetList(
                Array('ID'=>'DESC'),
                $arFilter,
                false,
                false,
                $arSelect
            );

            if($ob = $res->fetch()){
                return true;
            }
        }

        return false;
    }

    function deal_request_prod()
    {
        $request_prod =  json_decode($_REQUEST['DEAL_PRODUCT_DATA'], true);
        $dealProducts = array();
        if (is_array($request_prod)) {
            foreach ($request_prod as $product) {
                $dealProducts[$product['PRODUCT_ID']]['MEASURE_CODE'] = $product['MEASURE_CODE'];
                $dealProducts[$product['PRODUCT_ID']]['PRODUCT_ID'] = $product['PRODUCT_ID'];
                $dealProducts[$product['PRODUCT_ID']]['COUNT'] += ProductToneCount($product);
            }
            return $dealProducts;
        }

        return false;
    }

}

?>