<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
	
use Bitrix\Main\Loader;
use Bitrix\Iblock;
global $USER_FIELD_MANAGER;

if (!Loader::includeModule('iblock'))
	return;

$arIBlockSections = array();
$rsSections = CIBlockSection::GetList(array('LEFT_MARGIN' => 'ASC'), array("IBLOCK_CODE" => "tableContent", "DEPTH_LEVEL"=> 1));
while($arRes = $rsSections->Fetch())
	$arIBlockSections[$arRes["ID"]] = "[".$arRes["ID"]."] ".$arRes["NAME"];

$arComponentParameters = array(
	"PARAMETERS" => array(
		"IBLOCK_SECTION_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_IBLOCK_SECTION_LIST_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockSections,
			"DEFAULT" => '',
			"ADDITIONAL_VALUES" => "Y",
			"REFRESH" => "Y",
		),
	),
);


?>
