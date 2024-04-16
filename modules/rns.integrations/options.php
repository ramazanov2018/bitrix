<?php
defined('B_PROLOG_INCLUDED') || die;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
global $APPLICATION, $USER;

/** @var string $mid */
$module_id = $mid;
Loader::includeModule($mid);

$RIGHT = $APPLICATION->GetGroupRight($mid);

if ($RIGHT < "R") {
    return;
}

/**
 *
 * Описание логики табов и настроек в табах
 */
/**
 * общие настройки
 */
$arTabs = [
    ["DIV" => "edit1", "TAB" => Loc::getMessage('RNS_INTEGRATIONS_TAB_GENERAL_NAME'), "ICON" => "settings", "TITLE" => Loc::getMessage('RNS_INTEGRATIONS_TAB_GENERAL_TITLE'),
        "OPTIONS" => [
            [
                "active",
                Loc::getMessage("RNS_INTEGRATIONS_OPTION_ACTIVE"),
                "",
                ["checkbox"],
            ],
        ],
    ],
    ["DIV" => "edit2", "TAB" => Loc::getMessage("MAIN_TAB_RIGHTS"), "ICON" => "support_settings", "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_RIGHTS")],
];

$RestoreDefaults = !empty($_REQUEST['RestoreDefaults']);

if (($RIGHT >= "W")) {

    if((!$RestoreDefaults) && check_bitrix_sessid() && (strlen($_POST['save']) > 0 || strlen($_POST['apply']) > 0)){

        foreach ($arTabs as $arTab) {
            __AdmSettingsSaveOptions($module_id, $arTab["OPTIONS"]);
        }
    }elseif ($RestoreDefaults){
        COption::RemoveOption($module_id);
		$z = CGroup::GetList($v1='id',$v2='asc', ['ACTIVE' => 'Y', 'ADMIN' => 'N']);
		while($zr = $z->Fetch())
			$APPLICATION->DelGroupRight($module_id, [$zr['ID']]);
			LocalRedirect($APPLICATION->GetCurPage()."?mid=".$module_id."&lang=".LANG);
    }
}

/*
 * отрисовка формы
 */
$tabControl = new CAdminTabControl('tabControl', $arTabs);
$tabControl->Begin();
?>

<form method="POST"
      action="<?php
      echo $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialcharsbx($mid) ?>&lang=<?= LANGUAGE_ID ?>"
      id="base_exchange_form">
    <?php

    if (!empty($arTabs)) {
        foreach ($arTabs as $key => $arTab) {

            if ($arTab["OPTIONS"]) {

                $tabControl->BeginNextTab();
                __AdmSettingsDrawList($module_id, $arTab["OPTIONS"]);
            }
        }
    }
    ?>

    <?php
    $tabControl->BeginNextTab(); ?>

    <?php
    $Update = $_POST['save'] . $_POST['apply'];
    require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin/group_rights.php");
    ?>

    <?php
    $tabControl->Buttons(['btnApply' => false, 'btnCancel' => false, 'btnSaveAndAdd' => false, 'disabled' => ($RIGHT < "W")]);
    ?>
    <input type="reset" name="reset" value="<?php
    echo Loc::getMessage('MAIN_RESET')?>">
    <input type="submit" name="RestoreDefaults" title="<?php
    echo Loc::getMessage("MAIN_HINT_RESTORE_DEFAULTS") ?>"
           onclick="return confirm('<?php
           echo AddSlashes(Loc::getMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING")) ?>')"
           value="<?= Loc::getMessage("RESTORE_DEFAULTS") ?>">
    <?php
    echo bitrix_sessid_post();
    $tabControl->End();
    ?>
</form>