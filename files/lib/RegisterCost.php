<?php 
namespace Serv;

use \Bitrix\Crm;
class RegisterCost{
	static $Field = array(
		'DealId'			=> 'UF_DEAL',
		'ProductId'			=> 'UF_PRODUCT_ID',
		'Id'				=> 'ID',
		'OwnerId'			=> 'UF_OWNER_ID',
		
		'TransportTaxFact' 	=> 'UF_TRANS_TAX_FACT',	//Транспортный расход, (факт)
		'TaraFact' 			=> 'UF_TARA_FACT',		//Тара (выбор тары), (факт)
		'OtherTaxFact' 		=> 'UF_OTHER_RATE_FACT',//Дополнительный расход, (факт)	
		'EffPriceFact' 		=> 'UF_EFFECT_PRICE_FACT',//Эффективная цена товара (факт)
	
		'CountPlan'			=> 'UF_COUNT_PLAN',		//Кол-во, (план)	
		'PricePlan'			=> 'UF_PRICE_PLAN',		//Цена, (план)
		'SummPlan'			=> 'UF_SUMM_PLAN',      //Сумма, (план)
		'TransportTaxPlan'	=> 'UF_TRANS_TAX_PLAN', //Транспортный расход, (план)
		'TaraPlan'			=> 'UF_TARA_PLAN', 		//Тара, (план)
		'OtherPlan'			=> 'UF_OTHER_RATE_PLAN',//Дополнительный расход, (план)	
		'EffPricePlan' 		=> 'UF_EFFECT_PRICE_PLAN',//Эффективная цена товара (план)
		'TransportCurFact'	=> 'UF_TRANS_CUR_FACT',
		'TransportCurPlan'	=> 'UF_TRANS_CUR_PLAN',
	
	    
	);

	function OnAfterCrmDealProductRowsSave($DealId, $arFields){
		
		$DealSummPlan = 0;
		$UpdatePlan = false;
		
		
		$DealSum = $DealPrice = $DealCount;
		foreach($arFields as $Item){
			
			$OwnerId 			= $Item['ID'];
			$ProductId 			= $Item['PRODUCT_ID'];
			
			$RegisterCost		= $Item['RegisterCost'];
			
			if($RegisterCost === true){
				$UpdatePlan = true;
				
			
				
				$TransportTaxFact 	= $Item['TransportTaxFact'];
				$TaraFact 			= $Item['TaraFact'];
				$OtherTaxFact 		= $Item['OtherTaxFact'];
				$EffPriceFact		= $Item['EffPriceFact'];
				
				$CountPlan			= $Item['CountPlan'];
				$PricePlan			= $Item['PricePlan'];
				$SummPlan 			= $Item['SummPlan'];
				$TransportTaxPlan 	= $Item['TransportTaxPlan'];
				$TaraPlan			= $Item['TaraPlan'];
				$OtherPlan			= $Item['OtherPlan'];
				$EffPricePlan		= $Item['EffPricePlan'];
				 
		       	$TransportCurPlan	= $Item['TransportCurPlan'];
		       	$TransportCurFact	= $Item['TransportCurFact'];
				
		       	
		       	//$DealSum      += $EffPricePlan;
		       	$DealPrice    += $PricePlan;
		       	$DealCount    += $CountPlan;
		       	
		       	
		       	
		       	$El = (int)self::GetList(array('ID'), array('UF_PRODUCT_ID' => $ProductId, 'UF_DEAL' => $DealId))->Fetch()['ID'];
			
				/*if($OwnerId <= 0)
					$OwnerId = \Bitrix\Crm\ProductRowTable::GetList(
						array(
							'select' => array('ID'),
							'filter' => array('OWNER_TYPE' => 'D', 'OWNER_ID' => $DealId)
						))->Fetch()['ID'];*/
				
				$DealSummPlan += $SummPlan;
				
				$arElement = array(
					'UF_PRODUCT_ID' 		=> $ProductId,
					'UF_OWNER_ID' 			=> 0,//$OwnerId,
					'UF_TRANS_TAX_FACT'		=> $TransportTaxFact,
					'UF_TARA_FACT'			=> $TaraFact,
					'UF_OTHER_RATE_FACT'	=> $OtherTaxFact,
					'UF_EFFECT_PRICE_FACT'	=> $EffPriceFact,
					'UF_COUNT_PLAN'			=> $CountPlan,
					'UF_TARA_PLAN'			=> $TaraPlan,		//Кол-во, (план)	
					'UF_PRICE_PLAN'			=> $PricePlan,		//Цена, (план)
					'UF_SUMM_PLAN'			=> $SummPlan,      //Сумма, (план)
					'UF_TRANS_TAX_PLAN'		=> $TransportTaxPlan, //Транспортный расход, (план)
					'UF_TARA_PLAN'			=> $TaraPlan, 		//Тара, (план)
					'UF_OTHER_RATE_PLAN'	=> $OtherPlan,//Дополнительный расход, (план)	
					'UF_EFFECT_PRICE_PLAN'	=> $EffPricePlan,//Эффективная цена товара (план)
					'UF_TRANS_CUR_FACT'		=> $TransportCurFact, //Транспортный расход, валюта (факт)
					'UF_TRANS_CUR_PLAN'		=> $TransportCurPlan, //Транспортный расход, валюта (план)
				);
				
				self::Recalculator($DealId, $arElement);
				$DealSum      += $arElement['UF_EFFECT_PRICE_PLAN'];
				
				
				if($El > 0){
					self::Update($El, $arElement);	
					
					
				}else{
				    
					$arElement['UF_DEAL'] 		= $DealId;
					$arElement['UF_OWNER_ID'] 	= 0;//$OwnerId;
					
					self::Create($arElement);	
				}
				
			}
		}	
		
		// Обновление поля в сделке: Планируемая сумма сделки UF_CRM_1569316201 
		if($UpdatePlan == true){
			// валюта сделки
			$CurrencyDeal = \Bitrix\Crm\DealTable::GetList(array('filter' => array('ID' => $DealId), 'select' => array('CURRENCY_ID')))->Fetch()['CURRENCY_ID'];
			if($CurrencyDeal == '')
				$CurrencyDeal = 'RUB';
			
				
			$Update = [
			    'UF_CRM_1561454064982' => $DealSum.'|USD', //Эффективная сумма сделки
			    'UF_CRM_1588166454036' => $DealPrice, //Цена товара
			    'UF_CRM_1588166486253' => $DealCount, //Количество 
			    'UF_CRM_1569316201' => $DealSummPlan.'|'.$CurrencyDeal
			    
			];
				
			$UpdateDeal = \Bitrix\Crm\DealTable::Update($DealId, $Update);
            $arFields["ID"] = $DealId;

            //Обновляем сальдо
            CustomCompany::OnDealHandler($arFields);
		}
	   
	}
	
	function Recalculator($DealId, &$arElement){
	    $Deal = self::getDeal($DealId);
	    
	    $lcur = self::GetCurrency($Deal, $arElement);
	    
	    
	    $arElement['UF_EFFECT_PRICE_PLAN'] = self::CalsEffPricePlan($Deal, $lcur, $arElement);
	  
	}
	function CalsEffPricePlan($Deal, $lcur, $Params){
	    $EffPricePlan = 0;
	    
	    
	    $PaymentTerm    = (float)$Deal['CRM_DEAL_GROUP_PROPERTY_402']; //срок отсрочки
	    $PaymentCoef    = (float)$Deal['CRM_DEAL_GROUP_PROPERTY_405']; //Коэф
	    
	    //План: Эффективная стоимость
	    $PricePlan       = (float)$Params['UF_PRICE_PLAN'];
	    $CountPlan       = (float)$Params['UF_COUNT_PLAN'];
	    $DealSummPlan    = (float)$Params['UF_SUMM_PLAN'];
	    $PricePlanUSD    = $PricePlan*$lcur->CurrencyUSD/$lcur->CurrencyDeal;
	    
	    $TaraPlanUSD        = (float)$Params['UF_TARA_PLAN'];
	    $OtherPlanUSD       = (float)$Params['UF_OTHER_RATE_PLAN'];
	    
	    
	    if(!isset($lcur->TransCurPlan))
	        $lcur->TransCurPlan = 1;
	    
	    $TransportPlanUSD   = ((float)$Params['UF_TRANS_TAX_PLAN']*$lcur->BaseCurrency/$lcur->TransCurPlan)/$CountPlan;
	   
	    $EffPricePlan = $PricePlanUSD - $TaraPlanUSD - $OtherPlanUSD - $TransportPlanUSD - $PricePlanUSD*0.08*$PaymentCoef*$PaymentTerm/365;
	    return round($EffPricePlan, 4);
	}
	function CalsEffPriceFact($lcur, $Params){
	
	}
	function GetCurrency($Deal, $Params){
	    $Item = '';
	    $lcur = \CCurrency::GetList(($by="BASE"), ($order="asc"), LANGUAGE_ID);
	    while($lcur_res = $lcur->Fetch()){
	        $val = $lcur_res['AMOUNT_CNT']/$lcur_res['AMOUNT'];
	        if($lcur_res['CURRENCY'] == 'USD')
	            $Item->CurrencyUSD = $val;
	            
            if($lcur_res['CURRENCY'] == $Deal['CURRENCY_ID'])
                $Item->CurrencyDeal = $val;
                
            if($lcur_res['BASE'] == 'Y')
                $Item->BaseCurrency = $val;
                
            if($lcur_res['NUMCODE'] == $Params['UF_TRANS_CUR_FACT'])
                $Item->TransCurFact = $val;
                
            if($lcur_res['NUMCODE'] == $Params['UF_TRANS_CUR_PLAN'])
                $Item->TransCurPlan = $val;
        }
       
        return $Item;
	}
	
	function getDeal( $Id ){
	    if( (int)$Id <= 0)
	          return [];
	    
	    return \Bitrix\Crm\DealTable::GetList([
	        'select'    => ['ID', 'UF_CRM_1569316201', 'UF_CRM_1569319143', 'UF_CRM_1569319115', 'GROUP', 'CURRENCY_ID'],
	        'filter'    => ['=ID' => $Id],
	        'runtime'   => [
	            new \Bitrix\Main\Entity\ReferenceField(
	                'GROUP',
	                '\Serv\TermSheduleTable',
	                array('=this.UF_CRM_1569319143' => 'ref.IBLOCK_ELEMENT_ID'),
	                array('join_type' => 'LEFT')
	                )
	        ]
	    ])->Fetch();
	}
	
	
	function GetDataDeal($DealId){
		$RegisterTable = new \Serv\Register\RegisterTable();
		$res = $RegisterTable->GetList(array(
			'select' => ['*'],
			'filter'	=> ['UF_DEAL' => $DealId]
		));
		$Items = [];
		while($ar = $res->Fetch()){
			
			$Items[] = [
				'Id'				=> $ar['ID'],
				'DealId'			=> $ar['UF_DEAL'],
				'ProductId' 		=> $ar['UF_PRODUCT_ID'],
				'TransportTaxFact' 	=> $ar['UF_TRANS_TAX_FACT'],
				'TaraFact' 			=> $ar['UF_TARA_FACT'],
				'OtherTaxFact' 		=> $ar['UF_OTHER_RATE_FACT'],
				'EffPriceFact' 		=> $ar['UF_EFFECT_PRICE_FACT'],
				'CountPlan' 		=> $ar['UF_COUNT_PLAN'],
				'PricePlan' 		=> $ar['UF_PRICE_PLAN'],
				'SummPlan' 			=> $ar['UF_SUMM_PLAN'],
				'TransportTaxPlan' 	=> $ar['UF_TRANS_TAX_PLAN'],
				'TaraPlan' 			=> $ar['UF_TARA_PLAN'],
				'OtherPlan' 		=> $ar['UF_OTHER_RATE_PLAN'],
				'EffPricePlan'		=> $ar['UF_EFFECT_PRICE_PLAN'],
				'TransportCurFact'	=> $ar['UF_TRANS_CUR_FACT'],
				'TransportCurPlan'	=> $ar['UF_TRANS_CUR_PLAN'],
			];
			
		}
		return $Items;
		
	}
	
	function GetList($Select, $Filter){
		
		$RegisterTable = new \Serv\Register\RegisterTable();
		$res = $RegisterTable->GetList(array(
			'select' => $Select,
			'filter' => $Filter
		));
		
		return $res;
	}
	
	function Create($Params){
		$RegisterTable = new \Serv\Register\RegisterTable();
		$res = $RegisterTable->Add($Params);

		
		return $res;	
	}
	
	function Update($id, $Params){
		
	    
		$RegisterTable = new \Serv\Register\RegisterTable();
		$res = $RegisterTable->Update($id, $Params);
		
		return $res;	
		
	}
 	
 	public function UpdateDeal($Id, $Fileds){
 		
 	    
 	    
   		$RegisterTable = new \Serv\Register\RegisterTable();
 		
   		foreach($Fileds as $Code => $Value){
   			
 			if(isset(self::$Field[$Code]))
 				$Params[self::$Field[$Code]] = $Value;
 			
 		}
 		/************************** Расчет Эффективной цены план ****************************/
 		
 		//Параметры сделки
 		$DealId = (int)$Params['UF_DEAL'];
 		
 		if($DealId > 0){
 		    $Deal = self::getDeal($DealId);//

            //Тара
	        if ($DealTara = $Deal['UF_CRM_1569319115'] == '307' && !empty($Fileds['ProductId'])){
                $Params['UF_TARA_PLAN'] = self::Tara($Fileds['ProductId']);	//Тара, (план)
            }

	        $PaymentTerm    = (float)$Deal['CRM_DEAL_GROUP_PROPERTY_402']; //срок отсрочки
	        $PaymentCoef    = (float)$Deal['CRM_DEAL_GROUP_PROPERTY_405']; //Коэф
	        
	        $lcur = \CCurrency::GetList(($by="BASE"), ($order="asc"), LANGUAGE_ID);
	        $CurrencyDeal = 1;
	        $TransCurFact = $TransCurPlan = 1;
	       
	        while($lcur_res = $lcur->Fetch()){
	            $val = $lcur_res['AMOUNT_CNT']/$lcur_res['AMOUNT'];
	            if($lcur_res['CURRENCY'] == 'USD')
	                $CurrencyUSD = $val;
	            
	            if($lcur_res['CURRENCY'] == $Deal['CURRENCY_ID'])
	                $CurrencyDeal = $val;
	            
	            if($lcur_res['BASE'] == 'Y')
	                $BaseCurrency = $val;
	            
	            if($lcur_res['NUMCODE'] == $Params['UF_TRANS_CUR_FACT'])
	                $TransCurFact = $val;
	            
	            if($lcur_res['NUMCODE'] == $Params['UF_TRANS_CUR_PLAN'])
	                $TransCurPlan = $val;
	        }
	        
	        //План: Эффективная стоимость 
	        $PricePlan       = (float)$Params['UF_PRICE_PLAN'];
	        $CountPlan       = (float)$Params['UF_COUNT_PLAN'];
	        $DealSummPlan    = (float)$Params['UF_SUMM_PLAN'];
	        $PricePlanUSD    = $PricePlan*$CurrencyUSD/$CurrencyDeal;
     		
     		$TaraPlanUSD        = (float)$Params['UF_TARA_PLAN'];
     		$OtherPlanUSD       = (float)$Params['UF_OTHER_RATE_PLAN'];
     		
     		$TransportPlanUSD   = ((float)$Params['UF_TRANS_TAX_PLAN']*$BaseCurrency/$TransCurPlan)/$CountPlan;
     		
     		
     		$EffPricePlan = $PricePlanUSD - $TaraPlanUSD - $OtherPlanUSD - $TransportPlanUSD - $PricePlanUSD*0.08*$PaymentCoef*$PaymentTerm/365;
     		$EffPricePlan = round($EffPricePlan, 4);
     		
     		//pre($EffPricePlan);
     		//pre($TransportPlanUSD);
     		
     		//die();
     		if($EffPricePlan < 0)
     		    $EffPricePlan = 0;
     		
     		//Обновляем сделку
     		$Update = [
     		    'UF_CRM_1561454064982' => $EffPricePlan.'|USD', //Эффективная сумма сделки
     		    'UF_CRM_1588166454036' => $PricePlan, //Цена товара
     		    'UF_CRM_1588166486253' => $CountPlan, //Количество
     		    'UF_CRM_1569316201'    => $DealSummPlan.'|'.$Deal['CURRENCY_ID']
     		    
     		];
     		
     		$Params['UF_EFFECT_PRICE_PLAN'] = $EffPricePlan;
     		
     		if(
     		    isset($Params['UF_PRICE_PLAN']) && 
     		    isset($Params['UF_COUNT_PLAN'])
     		){
     		    $UpdateDeal = \Bitrix\Crm\DealTable::Update($DealId, $Update);
     		}
     		
     		// Факт : Эффективная стоимость 
     		$ProdRow = \Bitrix\Crm\ProductRowTable::GetList(['select'=> ['*'],'filter' => ['OWNER_ID' => $DealId, 'OWNER_TYPE' => 'D']])->Fetch();
     		
     		$PriceFact       = (float)$ProdRow['PRICE'];
     		$CountFact       = (float)$ProdRow['QUANTITY'];
     		$PriceFactUSD    = $PriceFact*$CurrencyUSD/$CurrencyDeal;
     		
     		$TaraFactUSD        = (float)$Params['UF_TARA_FACT'];
     		$OtherFactUSD       = (float)$Params['UF_OTHER_RATE_FACT'];
     		
     		
     		$TransportFactUSD = 0;
     		if($CountFact > 0)
         		$TransportFactUSD   = ((float)$Params['UF_TARA_FACT']*$BaseCurrency/$TransCurFact)/$CountFact;
     		
     		
     		
     		$EffectPriceFact =  ($PriceFactUSD - $TaraFactUSD - $OtherFactUSD - $TransportFactUSD)*(1-0.08*$PaymentCoef*$PaymentTerm/365);
     		$EffectPriceFact = round($EffectPriceFact, 4);
     		if($EffectPriceFact < 0 )
     		    $EffectPriceFact = 0;
     		   
     		$Params['UF_EFFECT_PRICE_FACT'] = $EffectPriceFact;
 		}


 		if($Id > 0){
 		    unset($Params['UF_DEAL']);
 			$res = $RegisterTable->Update($Id, $Params);
            ReserveReportsBP::ProdPlanFix($DealId, array());
            return true;
 		}



 		
 		$res = $RegisterTable->Add($Params);
        self::DealIsDogovor($DealId);
        ReserveReportsBP::ProdPlanFix($DealId,array());

 		return $res->GetID();
   }


   static function Tara($prId)
   {
       $id = (int)$prId;
       $db_props = \CIBlockElement::GetProperty(IBLOCK_CATALOG_ID, $id , array("sort" => "asc"), Array("ID"=>486))->Fetch();

       return (float)$db_props['VALUE'];
   }

    static function DealIsDogovor($dealId){
        $dealId = (int)$dealId;
        $FileDealId = (int)trim(file_get_contents(DOGOVOR_IN_1C_FILE));
        if ($dealId == $FileDealId){
            ReceivablesNew::DealIsDogovor($dealId);
            file_put_contents(DOGOVOR_IN_1C_FILE, '');
        }
    }
}

?>