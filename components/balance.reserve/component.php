<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

global $APPLICATION;
if (!CModule::IncludeModule('crm'))
{
	ShowError(GetMessage('CRM_MODULE_NOT_INSTALLED'));
	return;
}

use Bitrix\Main;
use Bitrix\Crm\Widget\Filter;
use Bitrix\Crm\Widget\FilterPeriodType;
use CrmReport\Base;




$arResult['GUID'] = $arParams['GUID'] = isset($arParams['GUID']) ? $arParams['GUID'] : 'crm_widget_panel';
$arResult['LAYOUT'] = $arParams['LAYOUT'] =isset($arParams['LAYOUT']) ? $arParams['LAYOUT'] : 'L50R50';
$arResult['MAX_GRAPH_COUNT'] = $arParams['MAX_GRAPH_COUNT'] = isset($arParams['MAX_GRAPH_COUNT']) ? (int)$arParams['MAX_GRAPH_COUNT'] : 6;
$arResult['MAX_WIDGET_COUNT'] = $arParams['MAX_WIDGET_COUNT'] = isset($arParams['MAX_WIDGET_COUNT']) ? (int)$arParams['MAX_WIDGET_COUNT'] : 15;
$arResult['NAVIGATION_CONTEXT_ID'] = $arParams['NAVIGATION_CONTEXT_ID'] =isset($arParams['NAVIGATION_CONTEXT_ID']) ? $arParams['NAVIGATION_CONTEXT_ID'] : '';
$arResult['ENABLE_NAVIGATION'] = $arParams['ENABLE_NAVIGATION'] =isset($arParams['ENABLE_NAVIGATION']) ? $arParams['ENABLE_NAVIGATION'] : true;
$arResult['CURRENT_USER_ID'] = CCrmSecurityHelper::GetCurrentUserID();
$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $arParams["NAME_TEMPLATE"]);
$arParams['IS_SUPERVISOR'] = isset($arParams['IS_SUPERVISOR']) && $arParams['IS_SUPERVISOR'];
$arResult['DEMO_TITLE'] = isset($arParams['~DEMO_TITLE']) ? $arParams['~DEMO_TITLE'] : '';
$arResult['DEMO_CONTENT'] = isset($arParams['~DEMO_CONTENT']) ? $arParams['~DEMO_CONTENT'] : '';
$arResult['CONTEXT_DATA'] = isset($arParams['CONTEXT_DATA']) && is_array($arParams['CONTEXT_DATA']) ? $arParams['CONTEXT_DATA'] : array();
$arResult['ENABLE_TOOLBAR'] = true;

$counterID = isset($arParams['~NAVIGATION_COUNTER_ID']) ? (int)$arParams['~NAVIGATION_COUNTER_ID'] : CCrmUserCounter::Undefined;
if(CCrmUserCounter::IsTypeDefined($counterID))
{
	$counter = new CCrmUserCounter(CCrmPerms::GetCurrentUserID(), $counterID);
	$arResult['NAVIGATION_COUNTER'] = $counter->GetValue(false);
}
else
{
	$arResult['NAVIGATION_COUNTER'] = isset($arParams['~NAVIGATION_COUNTER'])
		? (int)$arParams['~NAVIGATION_COUNTER'] : 0;
}

$entityType = isset($arParams['~ENTITY_TYPE']) ? strtoupper($arParams['~ENTITY_TYPE']) : '';
$entityTypes = isset($arParams['~ENTITY_TYPES']) && is_array($arParams['~ENTITY_TYPES']) ? $arParams['~ENTITY_TYPES'] : array();
if(empty($entityTypes))
{
	if($entityType !== '')
	{
		$entityTypes[] = $entityType;
	}
}
elseif($entityType === '')
{
	$entityType = $entityTypes[0];
}

$arResult['ENTITY_TYPES'] = $entityTypes;
$arResult['DEFAULT_ENTITY_TYPE'] = $entityType;
$arResult['DEFAULT_ENTITY_ID'] = isset($arParams['~ENTITY_ID']) ? (int)$arParams['~ENTITY_ID'] : 0;





$options = CUserOptions::GetOption('widget.widget_panel', $arResult['GUID'], array());

// todo tmp: a workaround to avoid updating options for all users


$arParams['FILTER'] = isset($arParams['FILTER']) ? $arParams['FILTER'] : array();



$arResult['FILTER'][] = Array(
					'id' => 'PERIOD',
					'name' => GetMessage('CRM_FILTER_FIELD_PERIOD'),
					'default' => true,
					'type' => 'date',
					'exclude' => array(
						/*Main\UI\Filter\DateType::NONE,
						/*Main\UI\Filter\DateType::CURRENT_DAY,
						Main\UI\Filter\DateType::CURRENT_WEEK,
						Main\UI\Filter\DateType::YESTERDAY,
						Main\UI\Filter\DateType::TOMORROW,
						Main\UI\Filter\DateType::PREV_DAYS,
						Main\UI\Filter\DateType::NEXT_DAYS,
						Main\UI\Filter\DateType::NEXT_WEEK,
						Main\UI\Filter\DateType::NEXT_MONTH,
						Main\UI\Filter\DateType::LAST_MONTH,
						Main\UI\Filter\DateType::LAST_WEEK,
						Main\UI\Filter\DateType::EXACT,
						//Main\UI\Filter\DateType::RANGE*/
					)
				);
$rs = \Bitrix\Iblock\ElementTable::GetList(array(
	'filter' => array('IBLOCK_ID' => IBLOCK_CATALOG_ID, 'ACTIVE' => 'Y'),
	'select' => Array('ID', 'NAME'),
	'order' => Array('NAME' => 'ASC')
));
while($ar = $rs->Fetch()){
	$PredItem[$ar['ID']] = $ar['NAME'];
}

$rs = \Bitrix\Iblock\ElementTable::GetList(array(
	'filter' => array('IBLOCK_ID' => IBLOCK_CATEGORIYA_TOVAROV, 'ACTIVE' => 'Y'),
	'select' => Array('ID', 'NAME'),
	'order' => Array('NAME' => 'ASC')
));
while($ar = $rs->Fetch()){
	$CategoryItem[$ar['ID']] = $ar['NAME'];
}

$arResult['FILTER'][] = 
	array(
		'id' => 'PRODUCT_ID',
		'name' => GetMessage('CRM_FILTER_FIELD_PRODUCT'),
		'default' => true,
		'type' => 'list',
		'items' => $PredItem,
		'params' => array(
			'multiple' => 'н'
		)
		
	);
$arResult['FILTER'][] =
	array(
		'id' => 'CATEGORY_ID',
		'name' => GetMessage('CRM_FILTER_FIELD_CATEGORY'),
		'default' => true,
		'type' => 'list',
		'items' => $CategoryItem,
		'params' => array(
			'multiple' => 'N'
		)

	);
$arResult['FILTER'][] =
	array(
		'id' => 'TYPE',
		'name' => GetMessage('CRM_FILTER_FIELD_TYPE'),
		'default' => true,
		'type' => 'list',
		'items' => array('Deal' => 'Сделка', 'Coming' => 'Приход', 'Production' => 'Производство'),
		'params' => array(
			'multiple' => 'N'
		)

	);
$arResult['FILTER'][] =
	array(
	    'id' => 'ASSIGNED_BY_ID',
	    'name' => 'Отвественный',
	    'default' => true,
	    'type' => 'dest_selector',
	    'params' => array(
	        'apiVersion' => 3,
	        'context' => 'CRM_DEAL_FILTER_ASSIGNED_BY_ID',
	        'multiple' => 'Y',
	        'contextCode' => 'U',
	        'enableAll' => 'N',
	        'enableSonetgroups' => 'N',
	        'allowEmailInvitation' => 'N',
	        'allowSearchEmailUsers' => 'N',
	        'departmentSelectDisable' => 'Y',
	        'isNumeric' => 'Y',
	        'prefix' => 'U',
	    )
	    
	);
	$arResult['FILTER'][] =
	array(
	    'id' => 'COMPANY_ID',
	    'name' => 'Компания',
	    'default' => true,
	    'type' => 'dest_selector',
	    'params' => array(
	        'contextCode' => 'CRM',
	        'useClientDatabase' => 'N',
	        'enableAll' => 'N',
	        'enableDepartments' => 'N',
	        'enableUsers' => 'N',
	        'enableSonetgroups' => 'N',
	        'allowEmailInvitation' => 'N',
	        'allowSearchEmailUsers' => 'N',
	        'departmentSelectDisable' => 'Y',
	        'enableCrm' => 'Y',
	        'enableCrmCompanies' => 'Y',
	        'convertJson' => 'N',
	    )
	    
	);
$arResult['FILTER_ROWS'] = array(
	'RESPONSIBLE_ID'   => true,
	'CATEGORY_ID'      => true,
	'PERIOD'           => true,
    'STATUS_ID'        => true,
    'ASSIGNED_BY_ID'   => true,
    'COMPANY_ID'       => true,
);

//region Filter Presets
$monthPresetFilter = array();

Filter::addDateType(
	$monthPresetFilter,
	'PERIOD',
	FilterPeriodType::convertToDateType(FilterPeriodType::CURRENT_MONTH)
);
//$monthPresetFilter['RESPONSIBLE_ID'][0] = 1;

//


$arResult['FILTER_PRESETS'] = array(
	'filter_current_month' => array(
		'name' => FilterPeriodType::getDescription(FilterPeriodType::CURRENT_MONTH),
		'fields' => $monthPresetFilter
	),
	
);

//endregion


$gridOptions = new CGridOptions($arResult['GUID']);
$filterOptions = new Main\UI\Filter\Options($arResult['GUID'], $arResult['FILTER_PRESETS']);
$arResult['FILTER_FIELDS'] = $filterOptions->getFilter($arResult['FILTER']);

if(Filter::getDateType($arResult['FILTER_FIELDS'], 'PERIOD') === '')
{
	$defaultFilter = array();
	Filter::addDateType(
		$defaultFilter,
		'PERIOD',
	    FilterPeriodType::convertToDateType(FilterPeriodType::CURRENT_MONTH)
	);
	$filterOptions->setupDefaultFilter(
		$defaultFilter,
		array_keys($arResult['FILTER_ROWS'])
	);
	$arResult['FILTER_FIELDS'] = $filterOptions->getFilter($arResult['FILTER']);
}

$arResult['WIDGET_FILTER'] = Filter::internalizeParams($arResult['FILTER_FIELDS']);

foreach(array_keys($arResult['FILTER_ROWS']) as $k)
{
	$arResult['FILTER_ROWS'][$k] = in_array($k, $visibleRows);
}


$arResult['OPTIONS'] = array(
	'filter_rows' => implode(',', array_keys($arResult['FILTER_ROWS'])),
	'filters' => array_merge($arResult['FILTER_PRESETS'], $gridSettings['filters'])
);

Filter::sanitizeParams($arResult['WIDGET_FILTER']);
$commonFilter = new Filter($arResult['WIDGET_FILTER']);


if($commonFilter->isEmpty())
{
	$commonFilter->setPeriodTypeID(FilterPeriodType::LAST_DAYS_30);
	$arResult['WIDGET_FILTER'] = $commonFilter->getParams();
}


if($arResult['DEFAULT_ENTITY_TYPE'] !== '')
{
	$commonFilter->setContextEntityTypeName($arResult['DEFAULT_ENTITY_TYPE']);
	if($arResult['DEFAULT_ENTITY_ID'] > 0)
	{
		$commonFilter->setContextEntityID($arResult['DEFAULT_ENTITY_ID']);
	}
}

$arResult['WIDGET_FILTER']['enableEmpty'] = false;
//$arResult['WIDGET_FILTER']['defaultPeriodType'] = FilterPeriodType::LAST_DAYS_30;


$FilterData = $arResult['FILTER_FIELDS'];

if($FilterData['PERIOD_from'] != ''){
	$Filter['dateFrom'] = $FilterData['PERIOD_from'];
}
if($FilterData['PERIOD_to'] != ''){
	$Filter['dateTo'] = $FilterData['PERIOD_to'];
}
if($FilterData['PRODUCT_ID'] != ''){
	$Filter['ProdId'] = $FilterData['PRODUCT_ID'];
}
if($FilterData['CATEGORY_ID'] != ''){
	$Filter['CatId'] = $FilterData['CATEGORY_ID'];
}
if($FilterData['TYPE'] != ''){
	$Filter['Type'] = $FilterData['TYPE'];
}
if( is_array($FilterData['ASSIGNED_BY_ID']) ){
    $Filter['ASSIGNED'] = $FilterData['ASSIGNED_BY_ID'][0];
}
if($FilterData['COMPANY_ID'] != ''){
    $Filter['COMPANY_ID'] = str_replace('CRMCOMPANY','', $FilterData['COMPANY_ID']);
}



$Data = Serv\BalanceReserveRepor::GetData($Filter);
$arResult['Data'] = $Data;



$this->IncludeComponentTemplate();
