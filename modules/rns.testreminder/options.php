<?php
use Bitrix\Main\Localization\Loc;

$module_id = "rns.testreminder";

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

    if ($request->isPost() && $request['Update'] && check_bitrix_sessid())
    {
        include ($_SERVER['DOCUMENT_ROOT'] . '/local/modules/'.$module_id.'/options/common-save.php');
    }

    $aTabs = array(
        array("DIV" => "edit2", "TAB" => 'Настройки', "ICON" => "blog_path", "TITLE" => 'Настройки'),
        array("DIV" => "edit3", "TAB" => 'Доступ', "ICON" => "blog_path", "TITLE" => 'Доступ'),
    );

    $tabControl = new CAdminTabControl("tabControl", $aTabs);
    ?><form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($mid)?>&lang=<?=LANGUAGE_ID?>"><?
    bitrix_sessid_post();
    $tabControl->Begin();?>
    <?$tabControl->BeginNextTab();?>
    <?include ($_SERVER['DOCUMENT_ROOT'] . '/local/modules/'.$module_id.'/options/common.php'); ?>
    <?$tabControl->EndTab();?>
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