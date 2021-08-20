<?php

namespace Serv;
use Bitrix\Main\Type;



class ReserveReportsBP
{
    static $workflowTemplateId =  78;
    static $errorApp = 'Ошибка при сохранении сделки';

    /*Производство* "I"*/
    static $iblockProizvodstvo = 49; //БП - Согласование остатка товара
    static $PropPDate = 354;
    static $PropPCount = 356;
    static $PropPCategory = 358;
    /*Производство*/

    /*Приход товара "P"*/
    static $iblockArrivalGoods = 43; //Приход товара
    static $PropIDate = 271;
    static $PropICount = 272;
    static $PropICategory = 359;
    /*Приход товара*/

    static $iblockProducts = 26; //Инфоблок товары

    /*Сделка "D"*/
    static $DataShipment = 'UF_CRM_1569329439'; // Дата планируемой отгрузки
    static $negativeBalance = 'UF_CRM_1570013203'; //Согласовать отрицательный остаток товара
    static $BalanceProducts = 'UF_CRM_1570013331'; //Остаток товара
    static $DealCreatedIn1C = 'UF_CRM_1597916353'; //Документ создан в 1С

    /*Сделка*/

    //БП - Согласование остатка товара
    static function StartWorkflow($arFields)
    {
        \CModule::IncludeModule('workflow');
        \CModule::IncludeModule('bizproc');


        if (isset($arFields['ERR_MSG_BP'])){
            $arErrorsTmp = array();

            $wfId =\CBPDocument::StartWorkflow(
                self::$workflowTemplateId,
                array("crm","CCrmDocumentDeal","DEAL_".$arFields['ID']),
                array('ERR_arrival' => $arFields['ERR_MSG_BP']),
                $arErrorsTmp
            );
        }
        return true;

    }

    //Проверка остатка товара
    function OnCheckBalanceProducts(&$arFields)
    {
        $arFields[self::$BalanceProducts] = '1';

        if ($arFields[self::$DealCreatedIn1C] == '1'){
            return true;
        }
        if (!\CModule::IncludeModule('iblock')){
            $arFields['RESULT_MESSAGE'] = self::$errorApp;
            return false;
        }

        $products = self::GetDealProducts();  //Требуемые товары

        //если нет выбранных товаров то просто сохраняем
        if ($products == 0)
            return true;

        $reserve = self::GetCategoryReserve($products['COUNT']);//, $arFields['UF_CRM_1569329439'] [categoryId => date]

        $dateShipment = strtotime($arFields[self::$DataShipment]);

        $ERROR = self::GetError($products, $reserve, $dateShipment);


        if (!empty($ERROR) && $arFields[self::$negativeBalance] == '1'){
            $arFields[self::$BalanceProducts] = '0';
            $arFields['ERR_MSG_BP'] = $ERROR;
            return true;
        }elseif(!empty($ERROR)){
            $ERROR[] = 'Вы можете согласовать возможность создания Сделки с руководителем или вернуться к редактированию Сделки», для этого установить флаг в поле "Согласовать отрицательный остаток товара';
            $arFields['RESULT_MESSAGE'] = $ERROR;
            return false;
        }
        return true;
    }

    //Требуемое количество по категориям
    function GetDealProducts($arFields = array(), $_1C = false, $dealId = 0)
    {

        $DealProducts = array();

        if ($_1C){
            $DealProducts = $arFields;
        }

        if (!empty($_REQUEST['DEAL_PRODUCT_DATA']) && !$_1C){
            $DealProducts = json_decode($_REQUEST['DEAL_PRODUCT_DATA'], true);
        }

        foreach ($DealProducts as $product){
            $Products['PRODUCTS'][$product['PRODUCT_ID']]['ID'] = trim($product['PRODUCT_ID']);
            $Products['PRODUCTS'][$product['PRODUCT_ID']]['NAME'] = trim($product['PRODUCT_NAME']);
            $Products['PRODUCTS'][$product['PRODUCT_ID']]['COUNT'] += ProductToneCount($product);
            $Products['PRS_ID'][] = trim($product['PRODUCT_ID']);
        }

        if (empty($Products['PRS_ID']))
            return 0;

        $arSelect = Array('ID', 'PROPERTY_352');
        $arFilter = Array('IBLOCK_ID' => self::$iblockProducts, 'ID' => $Products['PRS_ID'],);

        $resOb = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
        while ($res = $resOb->fetch()){
            $Products['PRODUCT'][$res['PROPERTY_352_VALUE']][] = $Products['PRODUCTS'][$res['ID']];
            $Products['CATEGORIES'][] = $res['PROPERTY_352_VALUE'];
            $Products['COUNT'][$res['PROPERTY_352_VALUE']] += $Products['PRODUCTS'][$res['ID']]['COUNT'];
        }
        unset($Products['PRODUCTS']);
        $Products['CATEGORIES'] = array_unique($Products['CATEGORIES']);
        return $Products;
    }

    //общее количество расходов
    function GetCountExpense($CatId)
    {
        $CatCount = array();
        $params = array(
            'filter' => array('UF_CATEGORY_ID' => $CatId, 'UF_TYPE' => ['D', 'P']),
            'select' => array('UF_COUNT', 'UF_CATEGORY_ID'),
            'group' => array('UF_CATEGORY_ID'),
            'runtime' => array(
                new \Bitrix\Main\Entity\ExpressionField('UF_COUNT', 'SUM(UF_COUNT)')
            )

        );
        $resOb = ReserveReportTable::GetList($params);
        while ($res = $resOb->fetch()){
            $CatCount[$res['UF_CATEGORY_ID']] = (-1)*$res['UF_COUNT'];
        }
        unset($resOb, $res, $resOb);
        return $CatCount;
    }

    //Общий остаток по категориям
    function GetCategoryReserve($categories)
    {
        $CatId = array_keys($categories);

        $check = array();
        foreach ($CatId as $value){
            $check[$value] = 1;
        }

        $CatCount = self::GetCountExpense($CatId); //[catId => count]
        $reserve = array();
        $params = array(
            'filter' => array('UF_CATEGORY_ID' => $CatId, 'UF_TYPE' => ['I']),
            'select' => array('UF_COUNT', 'UF_CATEGORY_ID', 'UF_DATE'),
            'order' => array("UF_DATE"=>'ASC'),

        );
        $resOb = ReserveReportTable::GetList($params);
        $sum = array();
        while ($res = $resOb->fetch()){
            $sum[$res['UF_CATEGORY_ID']] += $res['UF_COUNT'];
            if ($sum[$res['UF_CATEGORY_ID']] >= ($CatCount[$res['UF_CATEGORY_ID']] + $categories[$res['UF_CATEGORY_ID']]) && $check[$res['UF_CATEGORY_ID']] == '1')
            {
                $reserve[$res['UF_CATEGORY_ID']] = strtotime($res['UF_DATE']);
                $check[$res['UF_CATEGORY_ID']] = 0;
            }
        }
        unset($resOb,$res, $params);
        return $reserve;
    }

    //Дата планируемого прихода
    function GetError($products, $Arrival, $dateShipment)
    {
        $error = array();
        foreach ($products['CATEGORIES'] as $category){
            if (empty($Arrival[$category]) || $Arrival[$category] > $dateShipment){
                $DateArrival = ($Arrival[$category]) ? date('d.m.Y', $Arrival[$category]) : 0;
                foreach ($products['PRODUCT'][$category] as $product){
                    $error[] = "По товару(".$product['NAME'].") недостаточно остаток для отгрузки, дата планируемого прихода(".$DateArrival.")\n";
                }
            }
        }
        return $error;
    }

    //Добавление элемнта в таблицу отчета остатка товара (Плановый приход, Производство)
    function Add_I_P_Element($arFields)
    {

        if ($arFields['RESULT'] <= '0')
            return true;
        $arParams['UF_OWNER_ID'] = (int)$arFields['ID'];
        if ($arFields['IBLOCK_ID'] == self::$iblockProizvodstvo){
            $arParams['UF_DATE'] = new Type\Date($arFields['PROPERTY_VALUES'][self::$PropPDate]['n0']['VALUE'], 'd.m.Y');
            $arParams['UF_TYPE'] = 'P';
            $arParams['UF_COUNT'] = (int)$arFields['PROPERTY_VALUES'][self::$PropPCount]['n0'];
            $arParams['UF_CATEGORY_ID'] = (int)$arFields['PROPERTY_VALUES'][self::$PropPCategory];
        } elseif ($arFields['IBLOCK_ID'] == self::$iblockArrivalGoods){
            $arParams['UF_DATE'] = new Type\Date($arFields['PROPERTY_VALUES'][self::$PropIDate]['n0']['VALUE'], 'd.m.Y');
            $arParams['UF_TYPE'] = 'I';
            $arParams['UF_COUNT'] = (int)$arFields['PROPERTY_VALUES'][self::$PropICount]['n0'];
            $arParams['UF_CATEGORY_ID'] = (int)$arFields['PROPERTY_VALUES'][self::$PropICategory];
        }else{
            return true;
        }
        $res = ReserveReportTable::Add($arParams);
        return true;
    }

    //Обновление элемнта в таблице отчета остатка товара (Плановый приход, Производство)
    function Update_I_P_Element($arFields)
    {

        if ($arFields['RESULT'] <= '0')
            return true;
        $arParams['UF_OWNER_ID'] = (int)$arFields['ID'];
        $params = array(
            'filter' => ['=UF_OWNER_ID' => (int)$arFields['ID']],
            'select' => ['ID'],
            );
        if ($arFields['IBLOCK_ID'] == self::$iblockProizvodstvo){

            $key = key($arFields['PROPERTY_VALUES'][self::$PropPDate]);
            $arParams['UF_DATE'] = new Type\Date($arFields['PROPERTY_VALUES'][self::$PropPDate][$key]['VALUE'], 'd.m.Y');
            $arParams['UF_COUNT'] = (int)reset($arFields['PROPERTY_VALUES'][self::$PropPCount]);
            $arParams['UF_CATEGORY_ID'] = (int)$arFields['PROPERTY_VALUES'][self::$PropPCategory];
            $params['filter']['=UF_TYPE'] = 'P';
            $res = ReserveReportTable::Getlist($params)->fetch();
            $r = ReserveReportTable::Update((int)$res['ID'],$arParams);
        } elseif ($arFields['IBLOCK_ID'] == self::$iblockArrivalGoods){
            $key = key($arFields['PROPERTY_VALUES'][self::$PropIDate]);
            $arParams['UF_DATE'] = new Type\Date($arFields['PROPERTY_VALUES'][self::$PropIDate][$key]['VALUE'], 'd.m.Y');
            $arParams['UF_COUNT'] = (int)reset($arFields['PROPERTY_VALUES'][self::$PropICount]);
            $arParams['UF_CATEGORY_ID'] = (int)$arFields['PROPERTY_VALUES'][self::$PropICategory];
            $params['filter']['=UF_TYPE'] = 'I';
            $res = ReserveReportTable::Getlist($params)->fetch();
            $r = ReserveReportTable::Update((int)$res['ID'],$arParams);
        }else{
            return true;
        }
        return true;
    }

    //Удаление элемнта из таблицы отчета остатка товара (Плановый приход, Производство)
    function Delete_I_P_Element(&$arFields){

        $arParams['UF_OWNER_ID'] = (int)$arFields['ID'];
        $params = array(
            'filter' => ['=UF_OWNER_ID' => (int)$arFields['ID']],
            'select' => ['ID'],
        );
        if ($arFields['IBLOCK_ID'] == self::$iblockProizvodstvo){
            $params['filter']['=UF_TYPE'] = 'P';
            $res = ReserveReportTable::Getlist($params)->fetch();
            $r = ReserveReportTable::Delete((int)$res['ID']);
        } elseif ($arFields['IBLOCK_ID'] == self::$iblockArrivalGoods){
            $params['filter']['=UF_TYPE'] = 'I';
            $res = ReserveReportTable::Getlist($params)->fetch();
            $r = ReserveReportTable::Delete((int)$res['ID']);
        }
        return true;
    }

    //Обновление элемнта в таблице отчета остатка товара (Сделка)
    function Update_D_Element($dealId, $arFields)
    {

        $dealId = (int)$dealId;
        if (!$dealId)
            return true;

        $params = array(
            'filter' => ['=UF_OWNER_ID' => (int)$dealId, '=UF_TYPE' => 'D'],
            'select' => ['ID', 'UF_DATE', 'UF_CATEGORY_ID'],
        );

        $resOb = ReserveReportTable::Getlist($params);
        $elem = array();
        while ($res = $resOb->fetch()){
            $elem[$res['ID']] = $res['UF_CATEGORY_ID'];
        }

        $products = self::GetDealProducts($arFields, true, $dealId);  //Требуемые товары

        $DealDSH = \CCrmDeal::GetList(Array(), Array('ID' => $dealId,  Array(self::$DataShipment),  false))->fetch()[self::$DataShipment];

        if ($products && !empty($DealDSH)){

            $arParams = array();
            $arParams['UF_DATE'] = new Type\Date($DealDSH, 'd.m.Y');

            //Обновляемые товары
            $delete = array();
            foreach ($elem as $id => $cat){
                if (array_key_exists($cat, $products['COUNT'])) {
                    $arParams['UF_COUNT'] = $products['COUNT'][$cat];
                    unset($products['COUNT'][$cat]);
                    ReserveReportTable::Update((int)$id, $arParams);
                }else{
                    $delete[] = $id;
                }
            }

            //Удаляемые товары
            if (count($delete) > 0){
                self::Delete_Report_Element($dealId, $delete);
            }

            //Новый товары
            if (count($products['COUNT']) > 0){
                $arParams['UF_OWNER_ID'] = (int)$dealId;
                $arParams['UF_TYPE'] = 'D';
                foreach ($products['COUNT'] as $catId => $count){
                    $arParams['UF_COUNT'] = $count;
                    $arParams['UF_CATEGORY_ID'] = (int)$catId;
                    $res = ReserveReportTable::Add($arParams);
                }
            }

        }/*elseif ($products == 0){
            self::Delete_D_Element($dealId);
        }*/

        return true;
    }

    //Удаление элемнта из таблицы отчета остатка товара (Сделка)
    function Delete_D_Element(&$arFields){

        $params = array(
            'filter' => ['=UF_OWNER_ID' => (int)$arFields, '=UF_TYPE' => 'D'],
            'select' => ['ID'],
        );
        $resOb = ReserveReportTable::Getlist($params);
        while ($res = $resOb->fetch()){
            $r = ReserveReportTable::Delete((int)$res['ID']);
        }

        return true;
    }

    //Удаление элемнта из таблицы отчета остатка товара (Сделка)
    function Delete_Report_Element($deal, $id){

        $params = array(
            'filter' => ['=UF_OWNER_ID' => (int)$deal, '=UF_TYPE' => 'D', '=ID' => $id],
            'select' => ['ID'],
        );
        $resOb = ReserveReportTable::Getlist($params);
        while ($res = $resOb->fetch()){
            $r = ReserveReportTable::Delete((int)$res['ID']);
        }

        return true;
    }

    static function On_Update_D_Element($arFields)
    {
        $dealId = (int) $arFields["ID"];
        if ($dealId > 0){
            self::ProdPlanFix($dealId, array());
        }
        return true;
    }

    static function ProdPlanFix($dealId, array $fields){
        $dealId = (int) $dealId;

        if ($dealId > 0){
            $Products = array();
            $ProdRowOb = \CCrmProductRow::GetList(array(), array('=OWNER_ID' => $dealId), false, false, array(), array());
            while($ProdRow = $ProdRowOb->fetch()){
                $Products[$ProdRow['PRODUCT_ID']] = $ProdRow;
            }

            $ProdRowOb = Register\RegisterTable::GetList(array('filter' => array('=UF_DEAL' => $dealId)));
            while($ProdRow = $ProdRowOb->fetch()){
                $Products[$ProdRow['UF_PRODUCT_ID']]['CountPlan'] = $ProdRow['UF_COUNT_PLAN'];
            }

            $r = self::Update_D_Element($dealId, $Products);
        }
    }
}
