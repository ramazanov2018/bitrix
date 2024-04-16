<?php
global $APPLICATION;?>
<form action="<?php
echo $APPLICATION->GetCurPage()?>">
    <?=bitrix_sessid_post()?>
    <input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
    <input type="hidden" name="id" value="rns.integrations">
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="step" value="2">
    <?php
    CAdminMessage::ShowMessage(GetMessage("INTEGRATIONS_MODULE_UNINST_WARN"))?>
    <p><?php
        echo GetMessage("INTEGRATIONS_MODULE_UNINST_SAVE_DATA")?></p>
    <p><input type="checkbox" name="savedata" id="savedata" value="Y" checked><label for="savedata"><?php
            echo GetMessage("INTEGRATIONS_MODULE_UNINST_SAVE_DATA_TITLE")?></label></p>
    <p><input type="checkbox" name="savelog" id="savelog" value="Y" checked><label for="savelog"><?php
            echo GetMessage("INTEGRATIONS_MODULE_UNINST_SAVE_LOG_TITLE")?></label></p>
    <input type="submit" name="inst" value="<?php
    echo GetMessage("INTEGRATIONS_MODULE_UNINST")?>">
</form>