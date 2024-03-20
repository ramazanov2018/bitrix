<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$arComponentDescription = array(
    "NAME" => GetMessage("CONTRACT_MAP_DESCRIPTION_NAME"),
    "DESCRIPTION" => GetMessage("CONTRACT_MAP_DESCRIPTION"),
    "ICON" => "/images/icon.gif",
    "PATH" => array(
        "ID" => "rns",
        "CHILD" => array(
            "ID" => "ContractMap",
            "NAME" => GetMessage("CONTRACT_MAP_DESCRIPTION_NAME")
        )
    )
);
