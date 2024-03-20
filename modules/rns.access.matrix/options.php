<?php
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Loader;

$module_id = "rns.access.matrix";
if (!Loader::includeModule($module_id)) {
    return;
}
/**
 * @var CMain
 */
global $APPLICATION;

if ($APPLICATION->GetGroupRight($module_id) < "R") {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

$request = \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();

$rsGroups = CGroup::GetList(($by="c_sort"), ($order="desc"), array()); // выбираем группы
$arGroups = [];
while ($arGroup = $rsGroups->Fetch()){
    $arGroups[$arGroup["ID"]] = $arGroup;
}

    $aTabs = array(
        array("DIV" => "edit4", "TAB" => 'Доступ', "ICON" => "blog_path", "TITLE" => 'Доступ'),
    );

    $tabControl = new CAdminTabControl("tabControl", $aTabs);
    ?><form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($mid)?>&lang=<?=LANGUAGE_ID?>"><?
    bitrix_sessid_post();
    $tabControl->Begin();?>
    <?$tabControl->BeginNextTab();?>
    <?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>
    <?$tabControl->Buttons();?>
    <script language="JavaScript">
        function RestoreDefaults()
        {
            if (confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>'))
                window.location = "<?echo $APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?echo LANG?>&mid=<?echo urlencode($mid)."&".bitrix_sessid_get();?>";
        }
    </script>

    <input type="submit" name="Update" value="<?echo GetMessage("MAIN_SAVE")?>">
    <input type="hidden" name="Update" value="Y">
    <input type="reset" name="reset" value="<?echo GetMessage("MAIN_RESET")?>">

    <?$tabControl->End();
    ?></form>