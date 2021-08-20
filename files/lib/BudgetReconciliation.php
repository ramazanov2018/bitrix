<?php
namespace Serv;


class BudgetReconciliation
{
    static $errorApp = 'Ошибка при сохранении';

    static $iblockReceivables = 39; // Дебеторская задолженность;

    static $DataShipment = 'UF_CRM_1569329439'; // Дата планируемой отгрузки
    static $Company = 'COMPANY_ID'; // Компания
    static $CompanySaldo = 'UF_CRM_1568792196067'; //Салдо (Компания)
    static $CompanyCreditLimit = 'UF_CRM_1568792179350'; //Кредитный лимит (Компания)
    static $DealCreditLimit = 'UF_CRM_1569844571'; //Кредитный лимит (Сделка)
    static $CompanyPostponement = 'UF_CRM_1568792246554'; //Кол.-во дней отсрочки (по договору)
    static $PropNegativeCredit = 'UF_CRM_1569844533'; //Согласовать отрицательный кредит
    static $DealPlanPrice = 'UF_CRM_1569316201'; //Планируемая сумма сделки
    static $DealCreatedIn1C = 'UF_CRM_1597916353'; //Документ создан в 1С
    static $workflowTemplateId = 77;

    function includeModules()
    {
        if(\CModule::IncludeModule("crm")&& \CModule::IncludeModule("iblock")){
            return true;
        }

        return false;
    }

    //Проверка бюджета компании
    function OnBudgetCheck(&$arFields){
        if (self::includeModules() == false){
            $arFields['RESULT_MESSAGE'] = self::$errorApp;
            return false;
        }

        //Документ создан в 1С
        if ($arFields[self::$DealCreatedIn1C] == '1'){
            $arFields[self::$PropNegativeCredit] = '0';
            return true;
        }

        //Планируемая сумма сделки
        $planSum = self::GetProductsSum();

        //Если установлено галочка "Согласовать отрицательный кредит" -- сохраняем сделку
        if ($arFields[self::$PropNegativeCredit] == '1'){

            //Данные комнании
            $CompBudget = self::CompBudget($arFields[self::$Company]);
            $result = self::datePlanRepay($CompBudget, $planSum, $arFields);

            $arFields['datePlanRepay'] = $result['dateFormat'];
            $arFields['CreditLimit'] = $CompBudget['CreditLimit'];
            $arFields['Saldo'] = $CompBudget['Saldo'];
            $arFields[self::$DealCreditLimit] = '0';
            return true;
        }

        //Данные комнании
        $CompBudget = self::CompBudget($arFields[self::$Company]);

        if ($CompBudget['Saldo'] >= $planSum){
            $arFields[self::$DealCreditLimit] = '1';
            return true;
        }


        //Сумма оставшаяся
        //$sumR = self::SumRemaining($arFields[self::$Company],$arFields[self::$DataShipment]);
        //Сальдо, дата планируемого платежа

        $result = self::datePlanRepay($CompBudget, $planSum, $arFields);
        //$Check = ($Budget['Saldo'] + $sumR) - $planSum;

        $Error = 'У контрагента превышен кредитный лимит('.$CompBudget['CreditLimit'].'). Вы можете создать сделку после даты( '.$result['dateFormat'].' ) или согласовать возможность создания Сделки с руководителем, для этого установите флаг в поле "Согласовать отрицательный кредит';

        if ($result['pass'] <= 0){
            $arFields['RESULT_MESSAGE'] = $Error;
            return false;
        }/*else{
            $arFields['COMMENTS'] = $arFields['COMMENTS']. "</br> Сделка была создана с отрицательным кредитным лимитом";
        }*/

        return true;
    }

    //Сумма оставшаяся
    function SumRemaining($CompanyId, $DataShipment)
    {
        $sum = 0;
        $arSelect = Array('NAME', 'PROPERTY_SROK_OPLATY', 'PROPERTY_SUMMA_OSTAVSHAYASYA');
        $arFilter = Array("IBLOCK_ID"=>self::$iblockReceivables, 'PROPERTY_KLIENT' => $CompanyId);

        $res = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);

        while($ob = $res->GetNextElement()){
            $arFields = $ob->GetFields();
            if (strtotime($arFields['PROPERTY_SROK_OPLATY_VALUE']) < strtotime($DataShipment)){
                $sum += $arFields['PROPERTY_SUMMA_OSTAVSHAYASYA_VALUE'];
            }
        }

        return $sum;
    }

    //Дата погашения кредитного лимита и сальдо
    function CompBudget($CompanyId, $payment = 0)
    {
        $CompBudget = array();
        $dbResMultiFields = \CCrmCompany::GetList(
            Array(),
            Array('ID'=>$CompanyId),
            Array(self::$CompanySaldo, self::$CompanyCreditLimit, self::$CompanyPostponement),
            false
        );

        while($arMultiFields = $dbResMultiFields->Fetch())
        {
            $CompBudget['Postponement'] = $arMultiFields[self::$CompanyPostponement];

            $arSaldo = explode('|', $arMultiFields[self::$CompanySaldo]);
            $CompBudget['Saldo'] = $arSaldo[0];

            $arCreditLimit = explode('|', $arMultiFields[self::$CompanyCreditLimit]);
            $CompBudget['CreditLimit'] = $arCreditLimit[0];
        }
        return $CompBudget;
    }

    function datePlanRepay($CompBudget, $planSum = 0, $dealFields)
    {
        $result['pass'] = 0;
        $Saldo = $CompBudget['Saldo'];
        $Postponement = ($CompBudget['Postponement']) ? $CompBudget['Postponement'] : 0;
        $dealOb = \Bitrix\Crm\DealTable::GetList(
            array(
                'order'  => array('UF_CRM_1569329439' => "ASC"),
                'filter' => Array('CLOSED' => 'N', 'COMPANY_ID'=> $dealFields[self::$Company]),
                'select' => Array('COMPANY_ID', self::$DealPlanPrice, self::$DataShipment))
        );
        $result['dateFormat'] = "Не известно";

        while($Deal = $dealOb->Fetch()){
            $DealPrice = explode('|', $Deal[self::$DealPlanPrice]);

            $Saldo += $DealPrice[0];

            if ($Saldo >= $planSum){
                $Date =  new \Bitrix\Main\Type\DateTime($Deal[self::$DataShipment]);
                $Date->add($Postponement." day");
                $result['date'] = $Date;
                $result['dateFormat'] = $result['date']->format("d.m.Y");
                break;
            }

            /*$Date =  new \Bitrix\Main\Type\DateTime($Deal[self::$DataShipment]);
            $Date->add($Postponement." day");
            $result['date'] = $Date;
            $result['dateFormat'] = $result['date']->format("d.m.Y");*/

        }

        if($result['date']){
            $date1 = new \DateTime($result['date']);
            $date2 = new \DateTime($dealFields[self::$DataShipment]);
            $diff = $date2->diff($date1);

            if($diff->days > 0){
                $result['pass'] = $diff->invert > 0 ? 1 : 0 ;
            }
            else{
                $result['pass'] =  1 ;
            }
        }

        return $result;
    }

    //Планируемая сумма по сделке
    function GetProductsSum()
    {
        $ProductsSum = 0;

        if (!empty($_REQUEST['DEAL_PRODUCT_DATA'])){
            $DealProducts = json_decode($_REQUEST['DEAL_PRODUCT_DATA'], true);
            foreach ($DealProducts as $product){
                $ProductsSum += $product['SummPlan'];
            }
        }

        return $ProductsSum;
    }

    //БП - Согласование Бюджета
    static function StartWorkflow($arFields)
    {
        \CModule::IncludeModule('workflow');
        \CModule::IncludeModule('bizproc');


        if ($arFields[self::$PropNegativeCredit] == '1'){
            $arErrorsTmp = array();

            $wfId =\CBPDocument::StartWorkflow(
                self::$workflowTemplateId,
                array("crm","CCrmDocumentDeal","DEAL_".$arFields['ID']),
                array('datePlanRepay' => $arFields['datePlanRepay'], 'CreditLimit' => $arFields['CreditLimit'], 'Saldo' => $arFields['Saldo']),
                $arErrorsTmp
            );
        }
        return true;

    }

}