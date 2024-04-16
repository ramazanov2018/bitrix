<?php

use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

Extension::load("ui.buttons");
Extension::load("ui.forms");
Loader::includeModule('socialnetwork');
global $APPLICATION;
$this->addExternalCss("/bitrix/js/rns/analytics/rns-analytics.css");
$APPLICATION->IncludeComponent(
    'rns:analytic.widget.member.selector',
    '',
    [
        'DISPLAY' => 'inline',
        'MAX' => 99999,
        'MIN' => 1,
        'TYPES' => ['USER', 'USER.EXTRANET', 'USER.MAIL'],
        'ATTRIBUTE_PASS' => ['ID'],
    ],
    false,
    ["HIDE_ICONS" => "Y", "ACTIVE_COMPONENT" => "Y"],
    true
);

