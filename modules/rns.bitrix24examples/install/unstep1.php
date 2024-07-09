<?php
use Bitrix\Main\Localization\Loc;

#Метод загружает языковые сообщения для указанного файла
Loc::loadMessages(__FILE__);
global $APPLICATION;
?>

<form action="<?= $APPLICATION->GetCurPage(); ?>">
    <?= bitrix_sessid_post() ?>
    <input type="hidden" name="lang" value="<?= LANG; ?>">
    <input type="hidden" name="id" value="rns.bitrix24examples">
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="step" value="2">
    <p><?= Loc::getMessage('MOD_UNINST_SAVE'); ?></p>
    <p><input type="checkbox" name="savedata" id="savedata" value="Y" checked><label
            for="savedata"><?= Loc::getMessage('BITRIX24EXAMPLES_MOD_UNINSTALL_SAVE_TABLES'); ?></label></p>
    <input type="submit" name="inst" value="<?= Loc::getMessage('MOD_UNINST_DEL'); ?>">
</form>