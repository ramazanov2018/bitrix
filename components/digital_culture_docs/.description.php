<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$arComponentDescription = array(
    "NAME" => GetMessage("DIGITAL_CULTURE_DOCS_DESCRIPTION_NAME"),
    "DESCRIPTION" => GetMessage("DIGITAL_CULTURE_DOCS_DESCRIPTION_DESCRIPTION"),
    "ICON" => "/images/icon.gif",
    "PATH" => array(
        "ID" => "rns",
        "CHILD" => array(
            "ID" => "views",
            "NAME" => GetMessage("DIGITAL_CULTURE_DOCS_DESCRIPTION_NAME")
        )
    )
);
