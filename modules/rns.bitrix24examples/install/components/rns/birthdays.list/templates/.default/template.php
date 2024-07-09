<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\UI\Toolbar\Facade\Toolbar;

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var $component */
/** @var $templateFolder */
if ($arParams['IFRAME'] == 'Y'){
    CJSCore::Init("sidepanel");

    ?><script type="text/javascript">
        // Prevent loading page without header and footer
        if(window === window.top)
        {
            window.location = "<?=CUtil::JSEscape($APPLICATION->GetCurPageParam('', ['IFRAME'])); ?>";
        }
    </script><?php

    $APPLICATION->ShowHead();
}
# тулбар с фильтром
Toolbar::addFilter([
    'GRID_ID' => $arResult['GRID_ID'],
    'FILTER_ID' => $arResult['FILTER_ID'],
    'FILTER' => $arResult['FILTER'],
    //'RESET_TO_DEFAULT_MODE' => true,
    'ENABLE_LIVE_SEARCH' => true,
    'ENABLE_LABEL' => true,
]);

# Список, гриды
$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
    'GRID_ID' => $arResult['GRID_ID'],
    'COLUMNS' => $arResult['COLUMNS'],
    'ROWS' => $arResult['ROWS'],
    'SHOW_ROW_CHECKBOXES' => false,
    'NAV_OBJECT' => $arResult['NAV'],
    'AJAX_MODE' => 'Y',
    'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
    'PAGE_SIZES' =>  [
        ['NAME' => '2', 'VALUE' => '2'],
        ['NAME' => '20', 'VALUE' => '20'],
        ['NAME' => '50', 'VALUE' => '50'],
        ['NAME' => '100', 'VALUE' => '100']
    ],
    'AJAX_OPTION_JUMP'          => 'N',
    'SHOW_CHECK_ALL_CHECKBOXES' => false,
    'SHOW_ROW_ACTIONS_MENU'     => true,
    'SHOW_GRID_SETTINGS_MENU'   => true,
    'SHOW_NAVIGATION_PANEL'     => true,
    'SHOW_PAGINATION'           => true,
    'SHOW_SELECTED_COUNTER'     => true,
    'SHOW_TOTAL_COUNTER'        => true,
    'SHOW_PAGESIZE'             => true,
    'SHOW_ACTION_PANEL'         => true,
    'ALLOW_COLUMNS_SORT'        => true,
    'ALLOW_COLUMNS_RESIZE'      => true,
    'ALLOW_HORIZONTAL_SCROLL'   => true,
    'ALLOW_SORT'                => true,
    'ALLOW_PIN_HEADER'          => true,
    'AJAX_OPTION_HISTORY'       => 'N'
]);
?>