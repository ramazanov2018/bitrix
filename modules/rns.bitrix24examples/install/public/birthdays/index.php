<?php
use Bitrix\Main\Loader;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

/** @global $APPLICATION */
$APPLICATION->SetTitle('Дни рождения');
if (Loader::includeModule('rns.bitrix24examples')){
    $APPLICATION->IncludeComponent(
        "rns:birthdays",
        ".default",
        array(
            "COMPONENT_TEMPLATE" => ".default",
            "SEF_MODE" => "Y",
            "SEF_FOLDER" => "/birthdays/",
            "SEF_URL_TEMPLATES" => array(
                "list" => "/",
                "detail" => "detail/#ELEMENT_ID#/",
            )
        ),
        false
    );
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>

