<?php
global $APPLICATION;
$integrations = ['JIRA', 'SAP', 'MS_PROJECT'];
?>
<form action="<?php
echo $APPLICATION->GetCurPage()?>">
    <?=bitrix_sessid_post()?>
    <input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
    <input type="hidden" name="id" value="rns.integrations">
    <input type="hidden" name="install" value="Y">
    <input type="hidden" name="step" value="2">
    <?php
    CAdminMessage::ShowMessage(['MESSAGE' => GetMessage('INTEGRATIONS_MODULE_INSTALL_TITLE'), 'TYPE' => 'OK'])?>
    <p>
        <?= GetMessage('INTEGRATIONS_MODULE_INSTALL_OPTIONS') ?>
    </p>
    <?php foreach ($integrations as $integration): ?>
        <div>
            <input type="checkbox" name="option_<?= strtolower($integration)?>" id="option_<?= strtolower($integration)?>" value="Y" checked>
            <label for="option_<?= strtolower($integration)?>"><?= GetMessage('INTEGRATIONS_MODULE_INSTALL_' . $integration)?></label>
        </div>
    <?php endforeach;?>
    <input type="submit" name="inst" value="<?= GetMessage('INTEGRATIONS_MODULE_INSTALL')?>">
</form>