<?php

$module_id = "test.catalog";
$TEST_CATALOG_RIGHT = $APPLICATION->GetGroupRight($module_id);
if ($TEST_CATALOG_RIGHT>="R") :
    $strWarning = "";
    if ($TEST_CATALOG_RIGHT=="POST" && strlen($Update)>0 && $TEST_CATALOG_RIGHT=="W" && check_bitrix_sessid() && strlen($use_sonnet_button) <= 0)
    {
        foreach($arAllOptions as $option)
        {
            $name = $option[0];
            $val = $$name;
            if ($option[3][0] == "checkbox" && $val != "Y")
                $val = "N";
            COption::SetOptionString($module_id, $name, $val, $option[1]);
        }


    }

    $aTabs = array(
        array("DIV" => "edit3", "TAB" => 'Доступ', "ICON" => "blog_path", "TITLE" => 'Доступ'),
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

    <input type="submit" <?if ($TEST_CATALOG_RIGHT<"W") echo "disabled" ?> name="Update" value="<?echo GetMessage("MAIN_SAVE")?>">
    <input type="hidden" name="Update" value="Y">
    <input type="reset" name="reset" value="<?echo GetMessage("MAIN_RESET")?>">
    <input type="button" <?if ($TEST_CATALOG_RIGHT<"W") echo "disabled" ?> title="По умолчанию" OnClick="RestoreDefaults();" value="По умолчани">

    <?$tabControl->End();
    ?></form><?

endif;
