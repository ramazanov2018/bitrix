<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
	
	use Bitrix\Main\Loader;
	use Bitrix\Iblock;
	use Bitrix\Currency;

	global $USER_FIELD_MANAGER;

	if (!Loader::includeModule('iblock'))
	return;

?>
