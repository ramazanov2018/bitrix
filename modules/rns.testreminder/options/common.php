<?php
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

$TEST_REMAINDER_USE = Option::get($module_id, 'TEST_REMAINDER_USE', 'N');
$TEST_REMAINDER_PERIOD_FROM = Option::get($module_id, 'TEST_REMAINDER_PERIOD_FROM', '09:00');
$TEST_REMAINDER_TEST_URL = Option::get($module_id, 'TEST_REMAINDER_TEST_URL', '');
$TEST_REMAINDER_NOTIFY = Option::get($module_id, 'TEST_REMAINDER_NOTIFY', '');
$TEST_REMAINDER_PERIOD_TO = Option::get($module_id, 'TEST_REMAINDER_PERIOD_TO', '23:59');
$TEST_REMAINDER_GROUPS = (array)unserialize(Option::get($module_id, 'TEST_REMAINDER_GROUPS', ""));

?>

<tr>
	<td width="50%" class="adm-detail-content-cell-l">
		<label for="TEST_REMAINDER_USE"><?=Loc::getMessage("TEST_REMAINDER_USE")?></label>
	</td>
	<td width="50%" class="adm-detail-content-cell-r">
		<input type="hidden" name="TEST_REMAINDER_USE" value="N">
		<input type="checkbox" <?if($TEST_REMAINDER_USE=="Y"):?>checked="checked"<?endif;?> id="TEST_REMAINDER_USE" name="TEST_REMAINDER_USE" value="Y" class="adm-designed-checkbox">
		<label class="adm-designed-checkbox-label" for="TEST_REMAINDER_USE" title=""></label>
	</td>
</tr>

<tr>
    <td width="50%" class="adm-detail-content-cell-l">
        <label for="TEST_REMAINDER_PERIOD"><?=Loc::getMessage("TEST_REMAINDER_PERIOD")?></label>
    </td>
    <td width="50%" class="adm-detail-content-cell-r">
        <input type="text" size="7" maxlength="255" id="TEST_REMAINDER_PERIOD_FROM" name="TEST_REMAINDER_PERIOD_FROM" value="<?=$TEST_REMAINDER_PERIOD_FROM?>">
        -
        <input type="text" size="7" maxlength="255" id="TEST_REMAINDER_PERIOD_TO" name="TEST_REMAINDER_PERIOD_TO" value="<?=$TEST_REMAINDER_PERIOD_TO?>">
    </td>
</tr>

<tr>
    <td width="50%" class="adm-detail-content-cell-l">
        <label for="TEST_REMAINDER_TEST_URL"><?=Loc::getMessage("TEST_REMAINDER_TEST_URL")?></label>
    </td>
    <td width="50%" class="adm-detail-content-cell-r">
        <input size="100" type="text" name="TEST_REMAINDER_TEST_URL" value="<?=$TEST_REMAINDER_TEST_URL?>">
    </td>
</tr>
<tr>
    <td width="50%" class="adm-detail-content-cell-l">
        <label for="TEST_REMAINDER_NOTIFY"><?=Loc::getMessage("TEST_REMAINDER_NOTIFY")?></label>
    </td>
    <td width="50%" class="adm-detail-content-cell-r">
        <textarea rows="10" cols="100" name="TEST_REMAINDER_NOTIFY"><?=$TEST_REMAINDER_NOTIFY?> </textarea>
    </td>
</tr>

<tr>
    <td width="50%" class="adm-detail-content-cell-l">
        <label for="TEST_REMAINDER_GROUPS"><?=Loc::getMessage("TEST_REMAINDER_GROUPS")?></label>
    </td>
    <td width="50%" class="adm-detail-content-cell-r">
        <select id="TEST_REMAINDER_GROUPS" name="TEST_REMAINDER_GROUPS[]" multiple size="10">
            <?foreach ($arGroups as $arGroup):?>
                <option <?if(in_array($arGroup["ID"], $TEST_REMAINDER_GROUPS)):?>selected="selected"<?endif;?> value="<?=$arGroup["ID"]?>">
                    <?=$arGroup["NAME"]?> (<?=$arGroup["ID"].":".$arGroup["NAME"]?>)
                </option>
            <?endforeach;?>
        </select>
    </td>
</tr>