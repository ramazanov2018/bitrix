<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
echo CAdminMessage::ShowNote(GetMessage("MOD_UNINST_OK"));
global $APPLICATION;
?>
<form action="<?= $APPLICATION->GetCurPage() ?>">
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="submit" name="" value="<?= GetMessage("MOD_BACK") ?>">
</form>
