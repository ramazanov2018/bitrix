<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
Loc::loadMessages(__FILE__);

$module_id = "fbit.quickrunintegration";
$QUICRUN_INTEGRATION = $APPLICATION->GetGroupRight($module_id);

if ($QUICRUN_INTEGRATION <"R")
    return;

Loader::includeModule($module_id);

$arAllOptions = array(
    array("QUICRUN_INTEGRATION_SERVER_IP", Loc::getMessage('QUICRUN_INTEGRATION_SERVER_IP'), "", Array("text", 30)),
    array("QUICRUN_INTEGRATION_TOKEN", Loc::getMessage('QUICRUN_INTEGRATION_TOKEN'), "",   Array("text", 30)),
);

$strWarning = "";
if ($REQUEST_METHOD=="POST" && strlen($Update)>0 && $QUICRUN_INTEGRATION=="W" && check_bitrix_sessid() && strlen($use_sonnet_button) <= 0)
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
    array("DIV" => "edit1", "TAB" =>  Loc::getMessage('QUICRUN_EDIT1_TAB_NAME'), "ICON" => "blog_settings", "TITLE" => Loc::getMessage('QUICRUN_EDIT1_TAB_NAME')),
    array("DIV" => "edit2", "TAB" => Loc::getMessage('QUICRUN_EDIT2_TAB_NAME'), "ICON" => "blog_settings", "TITLE" => Loc::getMessage('QUICRUN_EDIT2_TAB_NAME')),
    array("DIV" => "edit3", "TAB" => Loc::getMessage('QUICRUN_EDIT3_TAB_NAME'), "ICON" => "blog_path", "TITLE" => Loc::getMessage('QUICRUN_EDIT3_TAB_NAME')),
);

$logFiles = array(
        array('title' => Loc::getMessage('QUICRUN_LOG_PortalToQuickrun'), 'class' => Fbit\Quickrunintegration\PortalToQuickrun::GetClassName()),
        array('title' => Loc::getMessage('QUICRUN_LOG_QuickrunToPortal'), 'class' => Fbit\Quickrunintegration\QuickrunToPortal::GetClassName()),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
?><form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($mid)?>&lang=<?=LANGUAGE_ID?>"><?
bitrix_sessid_post();
$tabControl->Begin();?>

<?$tabControl->BeginNextTab();?>

<?foreach($arAllOptions as $Option)
{
    $val = COption::GetOptionString($module_id, $Option[0], $Option[2]);
    $type = $Option[3];
    $type[0] = ($Option[0] == "QUICRUN_INTEGRATION_TOKEN") ? 'token' : $type[0];
    ?>
    <tr>
        <td valign="top" width="50%"><?
            if ($type[0]=="checkbox")
                echo "<label for=\"".htmlspecialcharsbx($Option[0])."\">".$Option[1]."</label>";
            else
                echo $Option[1];
            ?></td>
        <td valign="middle" width="50%">
            <?if($type[0]=="checkbox"):?>
                <input type="checkbox" name="<?echo htmlspecialcharsbx($Option[0])?>" id="<?echo htmlspecialcharsbx($Option[0])?>" value="Y"<?if($val=="Y")echo" checked";?>>
            <?elseif($type[0]=="text"):?>
                <input type="text" size="<?echo $type[1]?>" value="<?echo htmlspecialcharsbx($val)?>" name="<?echo htmlspecialcharsbx($Option[0])?>">
            <?elseif($type[0]=="token"):?>
                <input  type="password" size="<?echo $type[1]?>" value="<?echo htmlspecialcharsbx($val)?>" name="<?echo htmlspecialcharsbx($Option[0])?>">
            <?elseif($type[0]=="textarea"):?>
                <textarea rows="<?echo $type[1]?>" cols="<?echo $type[2]?>" name="<?echo htmlspecialcharsbx($Option[0])?>"><?echo htmlspecialcharsbx($val)?></textarea>
            <?elseif($type[0]=="selectbox"):?>
                <select name="<?echo htmlspecialcharsbx($Option[0])?>" id="<?echo htmlspecialcharsbx($Option[0])?>">
                    <?foreach($Option[4] as $v => $k)
                    {
                        ?><option value="<?=$v?>"<?if($val==$v)echo" selected";?>><?=$k?></option><?
                    }
                    ?>
                </select>
            <?endif?>
        </td>
    </tr>
    <?
}?>
<?$tabControl->BeginNextTab();?>
<div>
    <div>
        <h3><?=Loc::getMessage('QUICRUN_LOG_BLOCK_TITLE')?></h3>
    </div>
    <?
    /*foreach ($logFiles as $file){?>
        <div>
            <p><?=$file['title']?></p>
            <?Fbit\Quickrunintegration\quickrunLog::GetLog($file['class'])?>
        </div>
    <?} */?>
</div>




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

<input type="submit" <?if ($QUICRUN_INTEGRATION<"W") echo "disabled" ?> name="Update" value="<?echo Loc::getMessage("MAIN_SAVE")?>">
<input type="hidden" name="Update" value="Y">
<input type="reset" name="reset" value="<?echo Loc::getMessage("MAIN_RESET")?>">

<?$tabControl->End();
?></form>

