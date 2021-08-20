<?php 
namespace Serv;
use \Bitrix\Crm;
class CustomCompany{
	
	function OnCompanyHandler($Fields){
		
		// Если прилетает "кредитный лимит", обновляем Сальдо
		if(isset($Fields['UF_CRM_1568792179350'])){
			$CreditLimit = explode('|', $Fields['UF_CRM_1568792179350']);
			
			self::UpdateSaldoLimit($Fields['ID'], $CreditLimit[0], $CreditLimit[1]);
		}
		return true;
	}

	static function UpdateSaldoLimit($CompanyId, $CreditLimit = 0, $CreditLimitCurrency){
		$SummDeal = self::GetActiveDealForCompany($CompanyId, $CreditLimitCurrency);
		
		$Saldo = $CreditLimit - $SummDeal;
		
		self::UpdateDealSaldo($CompanyId, $Saldo, $CreditLimitCurrency);
	
	}

	static function GetActiveDealForCompany($CompanyId, $CreditLimitCurrency){

	    $val = self::GetCurrency();

		$Summ = 0;
		$rs = \Bitrix\Crm\DealTable::GetList(Array(
			'select' => array('UF_CRM_1569316201'),
			'filter' => array('COMPANY_ID' => $CompanyId, 'CLOSED' => 'N')
		));
		while($ar = $rs->Fetch()){
            $DealSumm = explode('|', $ar['UF_CRM_1569316201']);
            if($DealSumm[1] != $CreditLimitCurrency){
                $Summ += ($DealSumm[0] * $val[$DealSumm[1]]) / $val[$CreditLimitCurrency];
            }else{
                $Summ += $DealSumm[0];
            }
        }
		return $Summ;
	}

	static function UpdateDealSaldo($CompanyId, $SaldoValue, $SaldoCurrency = 'RUB'){
		$res = \Bitrix\Crm\CompanyTable::Update($CompanyId, array('UF_CRM_1568792196067' => $SaldoValue.'|'.$SaldoCurrency));
	}

	function OnDealHandler($arFields){

	    //return false;
		$DealId =   (int)$arFields['ID'];
		$CompanyId = (int)$arFields['COMPANY_ID'];
		
		if($CompanyId <= 0)
			$CompanyId = self::GetCompanyDeal($DealId);
		
		if($CompanyId > 0){
			
			$CreditLimit = \Bitrix\Crm\CompanyTable::GetList(array(
				'select' => array('UF_CRM_1568792179350'),
				'filter' => array('=ID' => $CompanyId)
			))->Fetch()['UF_CRM_1568792179350'];
			
			$arCreditLimit = explode('|', $CreditLimit);
			$CreditLimit = $arCreditLimit[0];

			$SummDeal = self::GetActiveDealForCompany($CompanyId, $arCreditLimit[1]);
			$Saldo = $CreditLimit - $SummDeal;
			
			self::UpdateDealSaldo($CompanyId, $Saldo, $arCreditLimit[1]);
		}
		
		return true;
	}

	function GetCompanyDeal($DealId){
		$CompnayId =  \Bitrix\Crm\DealTable::GetList(Array(
			'select' => array('COMPANY_ID'),
			'filter' => array('=ID' => $DealId)
		))->Fetch()['COMPANY_ID'];
		
		
		return $CompnayId;
		
	}

	static function GetCurrency(){
        \CModule::IncludeModule("currency");
        $val = array();
        $lcur = \CCurrency::GetList(($by="name"), ($order="asc"), LANGUAGE_ID);
        while($lcur_res = $lcur->Fetch())
        {
            $val[$lcur_res['CURRENCY']] = $lcur_res['AMOUNT'];
        }

        return $val;
    }
	
}
?>