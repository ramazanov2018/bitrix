<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 */

use Bitrix\Crm;

CJSCore::Init(array('amcharts', 'amcharts_funnel', 'amcharts_serial', 'amcharts_pie', 'fx', 'drag_drop', 'popup', 'date'));
$asset = Bitrix\Main\Page\Asset::getInstance();
$asset->addJs('/bitrix/js/crm/common.js');
$asset->addCss('/bitrix/themes/.default/crm-entity-show.css');
$asset->addCss('/bitrix/js/crm/css/crm.css');

if(SITE_TEMPLATE_ID === 'bitrix24')
{
	$APPLICATION->SetAdditionalCSS('/bitrix/themes/.default/bitrix24/crm-entity-show.css');
	$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
	$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '').'pagetitle-toolbar-field-view flexible-layout crm-toolbar crm-pagetitle-view');
}
$quid = $arResult['GUID'];
$prefix = strtolower($quid);
$containerID = "{$prefix}_container";
$settingButtonID = "{$prefix}_settings";
$disableDemoModeButtonID = "{$prefix}_disable_demo";
$demoModeInfoCloseButtonID = "{$prefix}_demo_info_close";
$demoModeInfoContainerID = "{$prefix}_demo_info";


if($arResult['ENABLE_TOOLBAR'])
{
	$toolbarButtons = array(
		
	);

	
	$APPLICATION->IncludeComponent(
		'bitrix:crm.interface.toolbar',
		'title',
		array(
			'TOOLBAR_ID' => "{$prefix}_toolbar",
			'BUTTONS' => $toolbarButtons
		),
		$component,
		array('HIDE_ICONS' => 'Y')
	);
}



$listUrl = $arResult['PATH_TO_LIST'];
$widgetUrl = $arResult['PATH_TO_WIDGET'];
$kanbanUrl = $arResult['PATH_TO_KANBAN'];
$switchToListButtonID = "{$prefix}_list";
$reloadButtonID = "{$prefix}_widget";
$settings = array(
	'defaultEntityType' => $arResult['DEFAULT_ENTITY_TYPE'],
	'entityTypes' => $arResult['ENTITY_TYPES'],
	'layout' => $arResult['LAYOUT'],
	'rows' => $arResult['ROWS'],
	'prefix' => $prefix,
	'containerId' => $containerID,
	'settingButtonId' => $settingButtonID,
	'serviceUrl' => '/bitrix/components/bitrix/crm.widget_panel/settings.php?'.bitrix_sessid_get(),
	'listUrl' => $listUrl,
	'widgetUrl' => $widgetUrl,
	'currencyFormat' => $arResult['CURRENCY_FORMAT'],
	'maxGraphCount' => $arResult['MAX_GRAPH_COUNT'],
	'maxWidgetCount' => $arResult['MAX_WIDGET_COUNT'],
	'isDemoMode' => $arResult['ENABLE_DEMO'],
	'useDemoMode' => $arResult['USE_DEMO'],
	'demoModeInfoContainerId'=> $demoModeInfoContainerID,
	'disableDemoModeButtonId' => $disableDemoModeButtonID,
	'demoModeInfoCloseButtonId' => $demoModeInfoCloseButtonID,
	'isAjaxMode' => \Bitrix\Main\Page\Frame::isAjaxRequest()
);

$filterFieldInfos = array();

$headViewID =  isset($arParams['~RENDER_HEAD_INTO_VIEW']) ? $arParams['~RENDER_HEAD_INTO_VIEW'] : false;
if($headViewID && is_string($headViewID))
	$this->SetViewTarget('below_pagetitle', 0);

if(!$arResult['ENABLE_TOOLBAR'])
{
	?><div class="crm-btn-panel"><span id="<?=htmlspecialcharsbx($settingButtonID)?>" class="crm-btn-panel-btn"></span></div><?
}
?>
<div class="crm-filter-wrap">


<?
if(!isset($arParams['NOT_CALCULATE_DATA']) || $arParams['NOT_CALCULATE_DATA'] == true)
{
	$APPLICATION->IncludeComponent(
		'bitrix:crm.interface.filter',
		'title',
		array(
			'GRID_ID' => $quid,
			'FILTER' => $arResult['FILTER'],
			'FILTER_ROWS' => $arResult['FILTER_ROWS'],
			'FILTER_FIELDS' => $arResult['FILTER_FIELDS'],
			'FILTER_PRESETS' => $arResult['FILTER_PRESETS'],
			'RENDER_FILTER_INTO_VIEW' => false,
			'OPTIONS' => $arResult['OPTIONS'],
			'ENABLE_PROVIDER' => true,
			'DISABLE_SEARCH' => true,
			'VALUE_REQUIRED_MODE' => true,
			'NAVIGATION_BAR' => $navigationBar
		),
		$component,
		array('HIDE_ICONS' => true)
	);
}

if($headViewID && is_string($headViewID))
{
	$this->EndViewTarget();
}

$filterTypeDescriptions =  Crm\Widget\FilterPeriodType::getAllDescriptions();
//Remove unsupported types
unset($filterTypeDescriptions[Crm\Widget\FilterPeriodType::BEFORE]);


$BalanseAllDeal = $arResult['Data']['Balanse']; // Остаток по всем сделкам
$BalansePayDeal = $arResult['Data']['Balanse'];// Остаток по подписанным сделкам


?>
</div>


<?php if(!isset($arParams['NOT_CALCULATE_DATA']) || $arParams['NOT_CALCULATE_DATA'] == false): ?>
<style>
    .report_table {border: 1px solid #e5e5e5;
                    border-collapse: collapse;
                    width: 100%;
                    border-color: #e5e5e5;
                    border-bottom: 1px solid #e5e5e5;}
    .report_table th {border: 1px solid #e5e5e5;
                        border-color: #e5e5e5;}
    .report_table td {border-top: 1px solid #e5e5e5;
                        border-right: 1px solid #e5e5e5;
                        padding: 14px 17px 14px;
                        text-align: left;
                        overflow: hidden;
                        vertical-align: middle;
                        font-size: 14px;
                        border-right: 0;}
    .table_header {background-color: #e9e8c4;
                    font-size: 14px;}
    .table_header th {background: none repeat scroll 0 0 #f4f0d2;
                        border-top: 1px solid #e5e5e5;
                        border-right: 1px solid #e5e5e5;
                        color: #58564c;
                        font: 12px Arial,Helvetica,sans-serif;
                        overflow: hidden;
                        padding: 8px 17px 8px;
                        text-align: left;}

</style>

<div class="crm-widget" id="<?=htmlspecialcharsbx($containerID)?>">

    <table class="report_table">
        <thead>
            <tr class="table_header">
                <th>Товар</th>
                <th>Сделка</th>
                <th>Дата планируемой отгрузки</th>
                <th>Резерв подписанной сделки</th>
                <th>Резерв планируемой сделки</th>
                <th>Цена FCA с НДС</th>
                <th>Приход</th>
                <th>Приход (объем)</th>
                <th>Поставщик</th>
                <th>Производитель</th>
                <th>Общий остаток по<br/>подписанным сделкам</th>
                <th>Общий остаток по<br/>всем сделкам</th>
                <th>Отвественный</th>
                <th>Контрагент</th>
                <th>Базис отгрузки</th>
            </tr>
        </thead>
        <tbody>
        <?foreach($arResult['Data']['Items'] as $date => $Items){
        	foreach($Items as $DealId => $Item){
        		if($Item['Type'] == 'Deal'){
		        	$DealId = $Item['id'];
		        	foreach($arResult['Data']['ItemProd'][$DealId] as $Prod){
		        		$ProdId = (int)$Prod['product_id'];
		        		
		        		$BalanseAllDeal[$ProdId] -= $Prod['count']; // Остаток по всем сделкам
			        	if($Item['Status'] == 'Plan')
							$BalansePayDeal[$ProdId] -= $Prod['count']; // Остаток по подписанным сделкам
		        		
		        		if($ProdId <= 0)
		        			continue;
		        		
		        		?>
		        		<tr>
		        			<td><?=$Prod['title']?></td>
			                <td><a href="/crm/deal/details/<?=$Item['id']?>/" target="_blank"><?=$Item['title']?></a></td>
			                <td><?=$Item['date']?></td>
			                <td><?=($Item['Status'] == 'Plan' ? $Prod['count'] : 0)?></td>
			                <td><?=($Item['Status'] == 'Pay' ? $Prod['count'] : 0)?></td>
			                <td><?=$Prod['summ']?></td>
			                <td>-</td>
			                <td>-</td>
			                <td>-</td>
			                <td>-</td>
			                <td><?=$BalansePayDeal[$ProdId]?></td>
			                <td><?=$BalanseAllDeal[$ProdId]?></td>
			                <td><?=$Item['assigned']?></td>
			                <td><?=$Item['contragent']?></td>
			                <td><?=$Item['basis']?></td>
		        		</tr>
		        	<?}
		        	
        		}else{
        			$ProdId = (int)$Item['product_id'];
        			$BalanseAllDeal[$ProdId] += $Item['V'];
        			$BalansePayDeal[$ProdId] += $Item['V'];
        			?>
        			<tr>
	        			<td><?=$Item['Title']?></td>
		                <td>-</td>
		                <td>-</td>
		                <td>-</td>
		                <td>-</td>
		                <td>-</td>
		                <td><?=$Item['Coming']?></td>
		                <td><?=$Item['V']?></td>
		                <td><?=$Item['Provider']?></td>
		                <td><?=$Item['Manufacturer']?></td>
		                <td><?=$BalansePayDeal[$ProdId]?></td>
		                <td><?=$BalanseAllDeal[$ProdId]?></td>
		                <td><?=$Item['assigned']?></td>
		                <td><?=$Item['contragent']?></td>
		                <td><?=$Item['basis']?></td>
	        		</tr>
        			<?
        		}	
        	}
	    }?>
        </tbody>
    </table>
</div>
<?
if(!empty($arResult['BUILDERS'])):
	?><div id="rebuildMessageWrapper" ></div><?
endif;

?>


<script type="text/javascript">
	
	BX.ready(
		function()
		{
			
			BX.CrmWidgetManager.filter = <?=CUtil::PhpToJSObject($arResult['WIDGET_FILTER'])?>;
			
			
			BX.CrmWidgetPanel.current = BX.CrmWidgetPanel.create("<?=CUtil::JSEscape("{$quid}")?>", <?=CUtil::PhpToJSObject($settings)?>);
			BX.CrmWidgetPanel.current.layout();
		}
	);
</script>
<?php  endif;?>