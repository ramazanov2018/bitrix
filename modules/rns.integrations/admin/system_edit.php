<?php /** @noinspection PhpUndefinedVariableInspection */

/** @noinspection PhpUndefinedVariableInspection */

use Bitrix\Main\Localization\Loc;
use RNS\Integrations\ExternalSystemTable;
use RNS\Integrations\Models\IntegrationOptionsTableWrapper;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/admin_tools.php');
global $APPLICATION;
global $USER;
if (!CModule::IncludeModule('rns.integrations')) {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

Loc::loadMessages(__FILE__);

$backUrl = 'rns_integrations_system_list.php?lang=' . LANGUAGE_ID;

if ($ID > 0) {
    $obj = ExternalSystemTable::getByPrimary($ID)
      ->fetchObject();
} else {
    $obj = ExternalSystemTable::createObject();
    $obj->setCreatedBy($USER->GetID());
}

if ((!empty($save) || !empty($apply)) && is_array($_POST)) {
    $fields = $_POST;

    $active = !empty($fields['active']);

    $obj->setName($fields['name']);
    $obj->setCode($fields['code']);
    $obj->setDescription($fields['description']);
    $obj->setActive($active ? 'Y' : 'N');
    $obj->setModifiedBy($USER->GetID());
    $obj->setModified(\Bitrix\Main\Type\DateTime::createFromTimestamp(time()));
    $obj->save();
    if (!empty($save)) {
        if (!$active) {
            IntegrationOptionsTableWrapper::activate($obj->getId(), $active);
        }
        LocalRedirect($backUrl);
    } else {
        LocalRedirect($_SERVER['PHP_SELF'] . '?ID=' . $obj->getId() . '&lang=' . LANGUAGE_ID);
    }
}

$tabs = [
  ['DIV' => 'tab-1', 'TAB' => Loc::getMessage('INTEGRATIONS_SYSTEM_EDIT_GENERAL'),'TITLE' => Loc::getMessage('INTEGRATIONS_SYSTEM_EDIT_GENERAL_TAB_TITLE')],
];
$tabControl = new CAdminTabControl("tabControl", $tabs);

$APPLICATION->SetTitle(Loc::getMessage('INTEGRATIONS_SYSTEM_EDIT_TITLE'));
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
?>
<div class="adm-detail-toolbar"><span style="position:absolute;"></span>
    <a href="/bitrix/admin/rns_integrations_system_list.php?lang=ru" class="adm-detail-toolbar-btn" title="Вернуться в список информационных систем" id="btn_list"><span class="adm-detail-toolbar-btn-l"></span><span class="adm-detail-toolbar-btn-text"><?= Loc::getMessage('INTEGRATIONS_SYSTEM_EDIT_BTN_LIST_TITLE')?></span><span class="adm-detail-toolbar-btn-r"></span></a>
    <script type="text/javascript"> if(window.BXHotKeys!==undefined) {  BXHotKeys.Add("", "var d=BX(\'btn_list\'); if (d) location.href = d.href;", 8, 'Кнопка для перехода в список', 0);  } </script>
</div>
<form method="POST" action="<?php
echo $APPLICATION->GetCurPage() ?>?lang=<?=LANG?>&ID=<?=$ID?>" name="form1">
    <?=bitrix_sessid_post()?>
    <?php
    $tabControl->Begin(); ?>
    <?php
    $tabControl->BeginNextTab(); ?>
    <tr>
        <td><?= Loc::getMessage('INTEGRATIONS_SYSTEM_EDIT_FIELD_NAME') ?></td>
        <td>
            <?= InputType('text', 'name', $obj->getName(), false) ?>
        </td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('INTEGRATIONS_SYSTEM_EDIT_FIELD_CODE') ?></td>
        <td>
            <?= InputType('text', 'code', $obj->getCode(), false) ?>
        </td>
    </tr>
    <tr>
        <td>
            <label for="description">
            <?= Loc::getMessage('INTEGRATIONS_SYSTEM_EDIT_FIELD_DESCRIPTION') ?>
            </label>
        </td>
        <td>
            <textarea name="description" id="description" rows="3" style="width:100%;"><?=htmlspecialcharsbx($obj->getDescription())?></textarea>
        </td>
    </tr>
    <tr>
        <td>
            <label for="active"><?= Loc::getMessage('INTEGRATIONS_SYSTEM_EDIT_FIELD_ACTIVE') ?></label>
        </td>
        <td>
            <?= InputType('checkbox', 'active', true, htmlspecialcharsbx($obj->getActive())) ?>
        </td>
    </tr>
</form>
<?php
$tabControl->Buttons();
$hkInst = CHotKeys::getInstance();
echo '<input'.($aParams["disabled"] === true? " disabled":"").' type="submit" name="save" value="'.GetMessage("admin_lib_edit_save").'" title="'.GetMessage("admin_lib_edit_save_title").$hkInst->GetTitle("Edit_Save_Button").'" class="adm-btn-save" />';
echo $hkInst->PrintJSExecs($hkInst->GetCodeByClassName("Edit_Save_Button"));
echo '<input'.($aParams["disabled"] === true? " disabled":"").' type="submit" name="apply" value="'.GetMessage("admin_lib_edit_apply").'" title="'.GetMessage("admin_lib_edit_apply_title").$hkInst->GetTitle("Edit_Apply_Button").'" />';
echo $hkInst->PrintJSExecs($hkInst->GetCodeByClassName("Edit_Apply_Button"));
echo '<input type="reset" name="reset" value="' . Loc::getMessage('MAIN_RESET'). '">';
$tabControl->End();
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';

