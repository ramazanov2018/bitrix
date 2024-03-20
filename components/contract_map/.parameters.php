<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */

if(!CModule::IncludeModule("iblock"))
	return;

$arIBlocks=array();
$db_iblock = CIBlock::GetList(array("SORT"=>"ASC"), array( "TYPE" => "UniversalContent"));

while($arRes = $db_iblock->Fetch())
	$arIBlocks[$arRes["CODE"]] = "[".$arRes["ID"]."] ".$arRes["NAME"];

$arIBlockSections = array();
if(!empty($arCurrentValues["IBLOCK_CODE"])) {
    $rsSections = CIBlockSection::GetList(array('LEFT_MARGIN' => 'ASC'), array( "IBLOCK_CODE" => ($arCurrentValues["IBLOCK_CODE"]!="-"?$arCurrentValues["IBLOCK_CODE"]:"")));
    while($arRes = $rsSections->Fetch())
        $arIBlockSections[$arRes["CODE"]] = "[".$arRes["ID"]."] ".$arRes["NAME"];
}
$arComponentParameters = array(
	"PARAMETERS" => array(
		"IBLOCK_CODE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_IBLOCK_DESC_LIST_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlocks,
			"DEFAULT" => '',
			"ADDITIONAL_VALUES" => "Y",
			"REFRESH" => "Y",
		),
        "IBLOCK_SECTION_CODE" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("T_IBLOCK_SECTION_LIST_ID"),
            "TYPE" => "LIST",
            "VALUES" => $arIBlockSections,
            "DEFAULT" => '',
            "ADDITIONAL_VALUES" => "Y",
            "REFRESH" => "Y",
        ),

        "TITLE_SHOW" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("T_IBLOCK_DESC_TITLE_SHOW"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ),
	),
);
CIBlockParameters::Add404Settings($arComponentParameters, $arCurrentValues);
