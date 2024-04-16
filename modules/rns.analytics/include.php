<?php
use Bitrix\Main\Loader;
defined('B_PROLOG_INCLUDED') || die;

Bitrix\Main\Loader::registerAutoloadClasses(
    'rns.analytics',
    [
        '\Rns\Analytic\Access'	       => 'lib/Access.php',
        '\Rns\Analytic\Settings'	   => 'lib/Settings.php',
    ]
);

if (!Loader::includeModule('highloadblock')) {
    return false;
}