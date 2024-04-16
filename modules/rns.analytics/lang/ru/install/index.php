<?php
$MESS['RNSANALYTICS_INSTALL_TITLE'] = 'Установка модуля Аналитика';
$MESS['RNSANALYTICS_UNINSTALL_TITLE'] = 'Удаление модуля Аналитика';
$MESS['RNSANALYTICS_LOG_HL_NAME'] = 'Логи модуля Аналитика';
$MESS['RNSANALYTICS_INSTALL_NOTE'] = 'Для вывода закладки «Статистика» в раздел «Время и отчеты» добавьте следующий код в файл
"/timeman/.left.menu_ext.php":

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . "/local/modules/rns.analytics/public/analytics/.left.menu_ext.php");
if (ModuleManager::isModuleInstalled("rns.analytics")) {
    if (Bitrix\Main\Loader::includeModule("rns.analytics") && Rns\Analytic\Access::MenuShow()){
        $aMenuLinks[] = [
            Bitrix\Main\Localization\::getMessage("TOP_MENU_ANALYTICS"),
            SITE_DIR . "timeman/analytics",
            [],
            ["menu_item_id" => "menu_analytics"],
            "",
        ];
    }
}';

$MESS["TOP_MENU_ANALYTICS"] = "Статистика";
$MESS["RNS_HL_DYNAMIC_URL_NAME"] = "Динамические страницы";