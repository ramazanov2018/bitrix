<?php

use Bitrix\Main\Loader;
use Rns\Analytic\Access as RnsAccess;
use Bitrix\Main\Localization\Loc;


require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION;
$path = getLocalPath('modules/rns.analytics/install/index.php');
$installPath = 'local';
if(strpos($path, 'bitrix') !== false){
    $installPath = 'bitrix';
}
Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . "/".$installPath."/modules/rns.analytics/public/analytics/page_titles.php");

$APPLICATION->SetTitle(Loc::getMessage("TOP_MENU_ANALYTICS_TITLE"));
if (Loader::includeModule("rns.analytics") && RnsAccess::MenuShow())
    $APPLICATION->IncludeComponent("rns:analyticsview", ".default", []);
?>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>