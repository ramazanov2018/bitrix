<?php
if (!check_bitrix_sessid()) {
    return;
}
global $APPLICATION;
if ($ex = $APPLICATION->GetException()) {
    CAdminMessage::ShowMessage([
        "TYPE" => "ERROR",
        "MESSAGE" => GetMessage("MOD_INST_ERR"),
        "DETAILS" => $ex->GetString(),
        "HTML" => true,
    ]);
} else {
    CAdminMessage::ShowNote(GetMessage("MOD_INST_OK"));
    ?>
    <div class="adm-info-message-wrap">
        <div class="adm-info-message">
            <pre><?= GetMessage("RNSANALYTICS_INSTALL_NOTE") ?></pre>
        </div>
    </div>
    <?php
}
?>
<form action="<?= $APPLICATION->GetCurPage() ?>">
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="submit" name="" value="<?= GetMessage("MOD_BACK") ?>">
</form>
