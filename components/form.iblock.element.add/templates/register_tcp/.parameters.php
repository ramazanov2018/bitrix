<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

$arTemplateParameters = array(
    "FIELDS_ORDER"         => Array(
        "NAME"     => GetMessage("ORDER_PROPERTY"),
        "TYPE"     => "string",
        "DEFAULT"  => "",
        "MULTIPLE" => "Y",
    ),
    "IBLOCK_ID_DEPARTMENT" => Array(
        "NAME"     => GetMessage("IBLOCK_ID_DEPARTMENT"),
        "TYPE"     => "string",
        "DEFAULT"  => "",
        "MULTIPLE" => "N",
    ),
);
if (class_exists('Bitrix\Main\UserConsent\Agreement')) {
    $arTemplateParameters["USER_CONSENT"] = array();
}
?>