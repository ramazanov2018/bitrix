<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");// первый общий пролог
require_once($_SERVER["DOCUMENT_ROOT"]."/local/modules/rns.access.matrix/prolog.php"); // пролог модуля
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");


use Bitrix\Main\Localization\Loc,
    Rns\AccessMatrix\Access,
    Bitrix\Main\Loader;

$APPLICATION->SetTitle('Матрица прав "Гостех"');

if (!Loader::includeModule(ADMIN_MODULE_NAME)) {
    return;
}
/**
 * @var CMain
 */
global $APPLICATION;
\Bitrix\Main\UI\Extension::load('ui.entity-selector');
if ($APPLICATION->GetGroupRight(ADMIN_MODULE_NAME) < "R") {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

$request = \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();
if ($request->isPost() && $request['saveperm'] == 'Y' && check_bitrix_sessid()) {
    Access::AccessMatrixSave($request);
}

$aTabs = [
    ["DIV" => "edit1", "TAB" => 'Права на дашборды', "ICON" => "blog_path", "TITLE" => 'Права на дашборды'],
    ["DIV" => "edit2", "TAB" => 'Права на настраиваемые дашборды', "ICON" => "blog_path", "TITLE" => 'Права на настраиваемые дашборды'],
    ["DIV" => "edit4", "TAB" => 'Права на учет трудозатрат', "ICON" => "blog_path", "TITLE" => 'Права на учет трудозатрат'],
    ["DIV" => "edit5", "TAB" => 'Права на проекты', "ICON" => "blog_path", "TITLE" => 'Права на проекты'],
    ["DIV" => "edit6", "TAB" => 'Права на задачи', "ICON" => "blog_path", "TITLE" => 'Права на задачи'],
    ["DIV" => "edit7", "TAB" => 'Права на поручения', "ICON" => "blog_path", "TITLE" => 'Права на поручения'],
];

$tabControl = new CAdminTabControl("tabControl", $aTabs);
?>
<form method="POST" action="<? echo $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialcharsbx($mid) ?>&lang=<?= LANGUAGE_ID ?>">
    <?
    echo bitrix_sessid_post();
    $tabControl->Begin(); ?>

    <? $tabControl->BeginNextTab(); ?>
    <?Access::ShowAccessDashboard();?>
    <? $tabControl->EndTab(); ?>

    <? $tabControl->BeginNextTab(); ?>
    <?Access::ShowAccessDashboardCustomizable();?>
    <? $tabControl->EndTab(); ?>

    <? $tabControl->BeginNextTab(); ?>
    <?Access::ShowAccessTimeman();?>
    <? $tabControl->EndTab(); ?>

    <? $tabControl->BeginNextTab(); ?>
    <?Access::ShowAccessProject();?>
    <? $tabControl->EndTab(); ?>

    <? $tabControl->BeginNextTab(); ?>
    <?Access::ShowAccessTasks();?>
    <? $tabControl->EndTab(); ?>

    <? $tabControl->BeginNextTab(); ?>
    <?Access::ShowAccessResolutions();?>
    <? $tabControl->EndTab(); ?>

    <script language="JavaScript">
        function RestoreDefaults() {
            if (confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>'))
                window.location = "<?echo $APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?echo LANG?>&mid=<?echo urlencode($mid) . "&" . bitrix_sessid_get();?>";
        }
    </script>

    <input type="hidden" name="saveperm" value="Y">
    <?
    $tabControl->Buttons(
        array(
            "disabled" => false,
            "back_url" => "/bitrix/admin/?lang=".LANGUAGE_ID."&".bitrix_sessid_get()
        )
    );
    ?>
    <? $tabControl->End();
    ?></form>

<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>