<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$arComponentDescription = array(
    "NAME" => GetMessage("CONTENT_TABS_DESCRIPTION_NAME"),
    "DESCRIPTION" => GetMessage("CONTENT_TABS_DESCRIPTION"),
    "ICON" => "/images/icon.gif",
    "PATH" => array(
        "ID" => "rns",
        "CHILD" => array(
            "ID" => "views",
            "NAME" => GetMessage("CONTENT_TABS_DESCRIPTION_NAME")
        )
    )
);
