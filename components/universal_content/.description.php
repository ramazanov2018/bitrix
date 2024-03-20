<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$arComponentDescription = array(
    "NAME" => GetMessage("UNIVERSAL_CONTENT_DESCRIPTION_NAME"),
    "DESCRIPTION" => GetMessage("UNIVERSAL_CONTENT_DESCRIPTION"),
    "ICON" => "/images/icon.gif",
    "PATH" => array(
        "ID" => "rns",
        "CHILD" => array(
            "ID" => "universalContent",
            "NAME" => GetMessage("UNIVERSAL_CONTENT_DESCRIPTION_NAME")
        )
    )
);
