<?php 
namespace Serv;
use \Bitrix\Crm;
use \Bitrix\Main\Type;
use Bitrix\Main\Type\DateTime;

class BalanceReserveRepor{
	static $StepStage = array();
	static $Item = array();
	static $ProdFilter = array();
	static $Filter = array();
	static $arFilter = array();
	static $ItemProd = array();
	static $ItemProdId = array();
	static $ItemBalanse = array();
	static $ProdName = array();
	static $StatusList = array();
	static $Categories = array();
	static $CategoryBalance = array();
	static $SignedDeals = array();

	function GetData($Filter){
        self::GetCategories();
        self::GetStatusList();
        self::GetSignedDeals();
        self::GetBalance();

        if($Filter['CatId'] > 0){
            self::GetCatProd($Filter['CatId']);
            self::$arFilter['Category'] = $Filter['CatId'];
        }

		if($Filter['ProdId'] > 0)
			self::GetDealProd($Filter['ProdId']);
			
		if($Filter['dateFrom'] != ''){
            self::$Filter['>UF_CRM_1569329439'] = $Filter['dateFrom'];
            self::$arFilter['dateFrom'] = date('Y-m-d',strtotime($Filter['dateFrom']));
        }

		if($Filter['dateTo'] != ''){
            self::$Filter['<=UF_CRM_1569329439'] = $Filter['dateTo'];
            self::$arFilter['dateTo'] = date('Y-m-d',strtotime($Filter['dateTo']));
        }
       
        
        if($Filter['COMPANY_ID'] > 0){
            self::$Filter['=COMPANY_ID'] = $Filter['COMPANY_ID'];
        }
        if($Filter['ASSIGNED'] > 0){
            self::$Filter['=ASSIGNED_BY_ID'] = $Filter['ASSIGNED'];
        }
        
        if($Filter['Type'] == 'Deal' || $Filter['Type'] == ''){
            $Deals = self::GetActivityDealList();
            self::$Item = $Deals['items'];
            self::GetProductDealList($Deals['id'], $Deals['DateTms']);
        }
        if($Filter['Type'] == 'Production' || $Filter['Type'] == '')
            self::GetProduction();

        if($Filter['Type'] == 'Coming' || $Filter['Type'] == ''){
            self::GetComing();
        }

        
        $Items = (self::$Item);
        ksort($Items);
		
		return array(
			'Items' => $Items,
			'ItemProd' => self::$ItemProd,
			'Balanse' => self::$ItemBalanse,
		);
	}

	//Категории товаров
	static function GetCategories()
    {
        $rs = \Bitrix\Iblock\ElementTable::GetList(array(
            'filter' => array('IBLOCK_ID' => IBLOCK_CATEGORIYA_TOVAROV, 'ACTIVE' => 'Y'),
            'select' => Array('ID', 'NAME'),
            'order' => Array('NAME' => 'ASC')
        ));
        while($ar = $rs->Fetch()){
            self::$Categories[$ar['ID']] = $ar['NAME'];
        }

    }

    //Подписанные сделки
    static function GetSignedDeals(){
	    $DealsId = array();
        $rs = \Bitrix\Crm\DealTable::GetList(Array(
            'filter' => array('!STAGE_ID' => self::$StatusList),
            'select' => array('ID')
        ));
        while($ar = $rs->Fetch()){
            $DealsId[] = $ar['ID'];
        }
        self::$SignedDeals = $DealsId;
    }

    // Текущий остаток по товарам
    static function GetBalance(){
        $params = array(
            'select' => array('UF_COUNT', 'UF_CATEGORY_ID', 'UF_DATE','UF_TYPE','UF_OWNER_ID'),
            'order' => array("UF_DATE"=>'ASC'),
        );
        $resOb = ReserveReportTable::GetList($params);
        $sumP = array();
        $sumS = array();
        $Plan = array();
        $Signed = array();
        while ($res = $resOb->fetch()){
            
            
            
            $DateTms = MakeTimeStamp($res['UF_DATE']);
            if ($res['UF_TYPE'] == 'D'){
                
                
                $sumP[$res['UF_CATEGORY_ID']] -= $res['UF_COUNT'];
                $Plan[$res['UF_CATEGORY_ID']][$DateTms] =  $sumP[$res['UF_CATEGORY_ID']];
                
                if (in_array($res['UF_OWNER_ID'], self::$SignedDeals) !== false){
                    $sumS[$res['UF_CATEGORY_ID']] -= $res['UF_COUNT'];
                    $Signed[$res['UF_CATEGORY_ID']][$DateTms] =  $sumS[$res['UF_CATEGORY_ID']];
                }else{
                    $Signed[$res['UF_CATEGORY_ID']][$DateTms] =  (isset($sumS[$res['UF_CATEGORY_ID']])) ? $sumS[$res['UF_CATEGORY_ID']] : 0;
                }
            }else if ($res['UF_TYPE'] == 'P'){

                $sumP[$res['UF_CATEGORY_ID']] -= $res['UF_COUNT'];
                $Plan[$res['UF_CATEGORY_ID']][$DateTms] =  $sumP[$res['UF_CATEGORY_ID']];

                $sumS[$res['UF_CATEGORY_ID']] -= $res['UF_COUNT'];
                $Signed[$res['UF_CATEGORY_ID']][$DateTms] =  $sumS[$res['UF_CATEGORY_ID']];

            }else if ($res['UF_TYPE'] == 'I'){

                $sumP[$res['UF_CATEGORY_ID']] += $res['UF_COUNT'];
                $Plan[$res['UF_CATEGORY_ID']][$DateTms] =  $sumP[$res['UF_CATEGORY_ID']];

                $sumS[$res['UF_CATEGORY_ID']] += $res['UF_COUNT'];
                $Signed[$res['UF_CATEGORY_ID']][$DateTms] =  $sumS[$res['UF_CATEGORY_ID']];

            }
        }
        
        
        self::$CategoryBalance['Plan'] = $Plan;
        self::$CategoryBalance['Signed'] = $Signed;
        

        
	}

	//приходы
	static function GetComing(){
	    $Select = array(
			'PROPERTY_TOVAR', 'PROPERTY_PLANIRUEMAYA_DATA_PRIKHODA', 'PROPERTY_OBEM',
			'PROPERTY_POSTAVSHCHIK', 'PROPERTY_PROIZVODITEL', 'ID', 'PROPERTY_359'
		);
		$Filter = array(
			'IBLOCK_ID' => IBLOCK_PRIHOD_TOVARA,
            'PROPERTY_359' => self::$arFilter['Category'],
            '>=PROPERTY_271' => self::$arFilter['dateFrom'],
            '<=PROPERTY_271' => self::$arFilter['dateTo'],
		);
		$rs = \CIBlockElement::GetList(array(), $Filter, false, false, $Select);
		while($ar = $rs->fetch()){
			$DateTms = MakeTimeStamp($ar['PROPERTY_PLANIRUEMAYA_DATA_PRIKHODA_VALUE']);
			
			self::$Item[$DateTms][$ar['ID']] = array(
				'Title'			=> self::$ProdName[$ar['PROPERTY_TOVAR_VALUE']],
				'product_id'	=> $ar['PROPERTY_TOVAR_VALUE'],
				'Type'			=> 'Coming',
				'Date'		    => $ar['PROPERTY_PLANIRUEMAYA_DATA_PRIKHODA_VALUE'],
				'V'				=> $ar['PROPERTY_OBEM_VALUE'],
				'Manufacturer'	=> $ar['PROPERTY_PROIZVODITEL_VALUE'],
				'Provider'		=> $ar['PROPERTY_POSTAVSHCHIK_VALUE'],
                'category' => self::$Categories[$ar['PROPERTY_359_VALUE']],
                'BalanceP' => self::$CategoryBalance['Plan'][$ar['PROPERTY_359_VALUE']][$DateTms],
                'BalanceS' => self::$CategoryBalance['Signed'][$ar['PROPERTY_359_VALUE']][$DateTms]
            );
        }
	}

	//расходы на производство
	static function GetProduction()
    {
	    
        
        ////ASSIGNED
        //COMPANY_ID
        
        $Select = array(
            'ID', 'PROPERTY_354', 'PROPERTY_356', 'PROPERTY_358', 'PROPERTY_488', 'PROPERTY_487'
        );
        $Filter = array(
            'IBLOCK_ID' => IBLOCK_PROIZVODSTVO,
            'PROPERTY_358' => self::$arFilter['Category'],
            '>=PROPERTY_354' => self::$arFilter['dateFrom'],
            '<=PROPERTY_354' => self::$arFilter['dateTo'],
        );
        
        if(self::$Filter['=COMPANY_ID']  > 0){
            $Filter['PROPERTY_488'] = self::$Filter['=COMPANY_ID'];
        }
        if(self::$Filter['ASSIGNED'] > 0){
            $Filter['PROPERTY_487'] = self::$Filter['=ASSIGNED'];
        }
        

        $rs = \CIBlockElement::GetList(array(), $Filter, false, false, $Select);
        while($ar = $rs->fetch()){

            $DateTms = MakeTimeStamp($ar['PROPERTY_354_VALUE']);
            
            $AssignedId = (int)$ar['PROPERTY_487_VALUE'];
            
            if(!isset($Assigneds[$AssignedId]) && $AssignedId > 0){
                $Assigned = \CUser::GetByID($AssignedId)->Fetch();
                $Assigneds[$AssignedId] = implode(' ', [$Assigned['LAST_NAME'], $Assigned['NAME'] ]);
            }
            
            
            self::$Item[$DateTms][$ar['ID']] = array(
                'Title'			=> self::$Categories[$ar['PROPERTY_358_VALUE']],
                'Type'			=> 'Production',
                'Date'		    => $ar['PROPERTY_354_VALUE'],
                'V'				=> $ar['PROPERTY_356_VALUE'],
                'BalanceP' => self::$CategoryBalance['Plan'][$ar['PROPERTY_358_VALUE']][$DateTms],
                'BalanceS' => self::$CategoryBalance['Signed'][$ar['PROPERTY_358_VALUE']][$DateTms],
                'category'      =>  self::$Categories[$ar['PROPERTY_358_VALUE']],
                'assigned' 		=> $Assigneds[$AssignedId],
                'contragent'	=> \CCrmCompany::GetByID($ar['PROPERTY_488_VALUE']),
            );
        }
    }

	static function GetStatusList(){
		
		// 	Напраление экспорт
		$StageContractConfirm = \CCrmStatus::GetList(array(), array('ID' => 81))->Fetch()['SORT'];
		$rs = \CCrmStatus::GetList(array('SORT' => 'ASC'), array('ENTITY_ID' => 'DEAL_STAGE_1'));
		
		while($ar = $rs->Fetch()){
		    if($ar['SORT'] < $StageContractConfirm){
		        
				$StagePlan[] = $ar['STATUS_ID'];
		    }
		}
		
		// 	Направление Внутренний
		$StageContractConfirm = \CCrmStatus::GetList(array(), array('ID' => 55))->Fetch()['SORT'];
		$rs = \CCrmStatus::GetList(array('SORT' => 'ASC'), array('ENTITY_ID' => 'DEAL_STAGE', '<SORT' => $StageContractConfirm));
		
		while($ar = $rs->Fetch()){
		    if($ar['SORT'] < $StageContractConfirm){
		        
				$StagePlan[] = $ar['STATUS_ID'];
		    }
		}
		
		self::$StatusList = $StagePlan;
	}

	// Товары в сделке
	static function GetProductDealList($Deals, $DateSh){
		$Filter['=UF_DEAL'] = $Deals;
		
		if(count(self::$Filter['=ID']) > 0)
			$Filter['=UF_PRODUCT_ID'] = self::$ProdFilter;
		
		$rsHl = Register\RegisterTable::GetList(array(
			'select' => array('*'),
			'filter' => $Filter
		));
		while($arHl = $rsHl->Fetch()){
		    
			$ProductCount[$arHl['UF_DEAL']][$arHl['UF_PRODUCT_ID']] = $arHl['UF_COUNT_PLAN'];
		}
		
		$FilterRow['OWNER_TYPE'] = 'D';
		$FilterRow['=OWNER_ID']  = $Deals;
		if(count(self::$ProdFilter)  > 0)
			$FilterRow['=PRODUCT_ID'] = self::$ProdFilter ;
		
		$rs = \Bitrix\Crm\ProductRowTable::GetList(array(
			'select' => array('*','MEASURE','ID', 'PRICE', 'QUANTITY', 'IBLOCK_ELEMENT', 'SUM_ACCOUNT', 'OWNER_ID', 'PRODUCT_ID', 'PRODUCT_NAME', 'PLAN','CATEGORY'),
			'filter' => $FilterRow,
			'runtime' => Array(
				new \Bitrix\Main\Entity\ReferenceField(
					'PLAN',
					'Serv\Register\RegisterTable',
					array('=this.ID' => 'ref.UF_OWNER_ID', '=this.OWNER_ID' => 'ref.UF_DEAL'),
					array('join_type' => 'LEFT')
				
				),
                new \Bitrix\Main\Entity\ReferenceField(
                    'CATEGORY',
                    '\Serv\ProductPropertyTable',
                    array('=this.PRODUCT_ID' => 'ref.IBLOCK_ELEMENT_ID'),
                    array('join_type' => 'LEFT')

                ),
                new \Bitrix\Main\Entity\ReferenceField(
                    'MEASURE',
                    '\Serv\ProductRowModTable',
                    array('=this.ID' => 'ref.ID'),
                    array('join_type' => 'LEFT')

                ),
			)
		));
		$check = array();
		while($ar = $rs->Fetch()){
            if($ar['PRODUCT_ID'] > 0)
				$prodId[] = $ar['PRODUCT_ID'];

            $measurePr = array();
            $measurePr['CountPlan'] = $ProductCount[$ar['OWNER_ID']][$ar['PRODUCT_ID']];
            $measurePr['MEASURE_CODE'] = $ar["CRM_PRODUCT_ROW_MEASURE_MEASURE_CODE"];
            $measurePr['PRODUCT_ID'] =  $ar['PRODUCT_ID'];
            $MeasureCount = ProductToneCount($measurePr);

            $ProdName[$ar['PRODUCT_ID']] = $ar['CRM_PRODUCT_ROW_IBLOCK_ELEMENT_NAME'];
			$arProduct[$ar['OWNER_ID']][$ar['PRODUCT_ID']] = array(
				//'count' => $ProductCount[$ar['OWNER_ID']][$ar['PRODUCT_ID']],
				'count' => $MeasureCount,
				'product_id' => $ar['PRODUCT_ID'],
			    'title' => $ar['CRM_PRODUCT_ROW_IBLOCK_ELEMENT_NAME'].'/'.$ar['CRM_PRODUCT_ROW_CATEGORY_PROPERTY_352'],
				'summ'	=> $ar['CRM_PRODUCT_ROW_PLAN_UF_EFFECT_PRICE_PLAN'],
                'BalanceP' => self::$CategoryBalance['Plan'][$ar['CRM_PRODUCT_ROW_CATEGORY_PROPERTY_352']][$DateSh[$ar['OWNER_ID']]] + $check[$ar['OWNER_ID']][$ar['CRM_PRODUCT_ROW_CATEGORY_PROPERTY_352']],
                'BalanceS' => self::$CategoryBalance['Signed'][$ar['CRM_PRODUCT_ROW_CATEGORY_PROPERTY_352']][$DateSh[$ar['OWNER_ID']]],
                'categoryId'    => $ar['CRM_PRODUCT_ROW_CATEGORY_PROPERTY_352'],
                'category' => self::$Categories[$ar['CRM_PRODUCT_ROW_CATEGORY_PROPERTY_352']],
			);
            $check[$ar['OWNER_ID']][$ar['CRM_PRODUCT_ROW_CATEGORY_PROPERTY_352']] += $MeasureCount;

        }

        foreach ($arProduct as &$value){
            $value = array_reverse($value);
        }

		self::$ProdName = $ProdName;
		self::$ItemProd = $arProduct;
		self::$ItemProdId = array_unique($prodId);
	}
	// Активные сделки
	function GetActivityDealList(){
		
		
		
		$Select = array(
			'ID', 'CLOSEDATE', 'TITLE', 
			'DATE_CREATE',
			'UF_CRM_1569329439', // Планируемая дата отгрузки
			'UF_CRM_1569316201', // планируемая сумма 
			'STAGE_ID',
			'UF_CRM_1569316998', //базис
			'ASSIGNED_BY_ID', 
			'ASSIGNED_BY',
			'COMPANY_ID', 'STAGE_ID'
		);
		
	
		$Filter = self::$Filter;
		
		$Filter['!=UF_CRM_1569329439'] = '';
		
		$Filter['STAGE_SEMANTIC_ID'] = array('P');
		
		$rs = \Bitrix\Crm\DealTable::GetList(Array(
			'order' => array('UF_CRM_1569329439' => 'ASC'),
			'select' => $Select,
			'filter' => $Filter
		));
		$DateTime = new \Bitrix\Main\Type\DateTime();
		$StatusList = self::$StatusList;

		while($ar = $rs->Fetch()){
			$Ids[] = $ar['ID'];

			$Date = new DateTime($ar['UF_CRM_1569329439']);
			$Date = $Date->format(Type\Date::getFormat());
			
			$DateTms = MakeTimeStamp($Date);
            $DateSh[$ar['ID']] = $DateTms;

            $Deal[$DateTms][$ar['ID']] = array(
				'id'			=> $ar['ID'],
				'title' 		=> $ar['TITLE'],
				'Date'			=> $Date,
				'd'				=> $ar['DATE_CREATE'],
				'assigned' 		=> implode(' ', array(
													$ar['CRM_DEAL_ASSIGNED_BY_LAST_NAME'],
													$ar['CRM_DEAL_ASSIGNED_BY_NAME'],
													$ar['CRM_DEAL_ASSIGNED_BY_SECOND_NAME'],
													)
										),
				'contragent'	=> \CCrmCompany::GetByID($ar['COMPANY_ID']),
				'basis'			=> \CIBlockElement::GetByID($ar['UF_CRM_1569316998'])->Fetch()['NAME'],
				'stage_id'		=> $ar['STAGE_ID'],
				'Status'		=> (in_array($ar['STAGE_ID'], $StatusList) !== false ? 'Plan' : 'Pay'),
				'Type'			=> 'Deal'
			);	
		}
		return array('id' => $Ids, 'items' => $Deal, 'DateTms' => $DateSh);
	}

	// Получвем сделки по ID Продукту
	static function GetDealProd($ProdId){
		self::$ProdFilter[] = $ProdId;
		
		
		$rsHl = \Bitrix\Crm\ProductRowTable::GetList(array(
			'select' => array('ID', 'OWNER_ID', 'PRODUCT_ID'),
			'filter' => array(
				'PRODUCT_ID' => self::$ProdFilter
			)
		));
		while($ar = $rsHl -> Fetch())
			$Filter[] = $ar['OWNER_ID'];
			
		if(count($Filter) <= 0)
			$Filter[] = -1;
			
		self::$Filter['=ID'] = $Filter;
	}

	// Получвем Продукты по ID Категории
	static function GetCatProd($CatId)
    {
        $rs = \CIBlockElement::GetList(array('ID' => 'ASC'),array('IBLOCK_ID' => IBLOCK_CATALOG_ID, '=PROPERTY_352' => $CatId), false, false, array('ID'));
        while ($ar = $rs->Fetch()) {
            self::$ProdFilter[] = $ar['ID'];
        }
    }
}


?>