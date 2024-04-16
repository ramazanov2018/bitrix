<?php /** @noinspection ALL */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

Loc::loadMessages(__FILE__);

$helper = $arResult['HELPER'];

Extension::load(['ui.entity-selector']);
global $APPLICATION;
?>

<?php $helper->displayFatals();?>
<?php if(!$helper->checkHasFatals()):?>

	<div id="<?=$helper->getScopeId()?>" class="tasks task-form-field <?=$arParams['DISPLAY']?> <?=($arParams['READ_ONLY'] ? 'readonly' : '')?>"
         <?php if($arParams['MAX_WIDTH'] > 0):?>style="max-width: <?=$arParams['MAX_WIDTH']?>px"<?php endif?>>

        <?php $helper->displayWarnings();?>

		<?php
		if (Loader::IncludeModule('bitrix24'))
		{
			$APPLICATION->IncludeComponent("bitrix:bitrix24.limit.lock", "");
		}
		?>
<!---->
<!--		<div class="js-member-selector-container"></div>-->

		<span class="js-id-tdp-mem-sel-is-items tasks-h-invisible">
		    <script type="text/html" data-bx-id="tdp-mem-sel-is-item">
			    <?php ob_start();?>
				<span class="js-id-tdp-mem-sel-is-item js-id-tdp-mem-sel-is-item-{{VALUE}} task-form-field-item {{ITEM_SET_INVISIBLE}}"
					  data-item-value="{{VALUE}}" data-bx-type="{{TYPE_SET}}">
					<a class="task-form-field-item-text task-options-destination-text" href="{{URL}}" target="_blank">
						{{DISPLAY}}
					</a>
					<span class="js-id-tdp-mem-sel-is-item-delete task-form-field-item-delete" title="<?=Loc::getMessage('TASKS_COMMON_CANCEL_SELECT')?>"></span>

				    <?php if(!$arResult['JS_DATA']['inputSpecial']):?>
                        <?php // being usually embedded into a form, this control can produce some inputs ?>
                        <?php foreach($arParams['ATTRIBUTE_PASS'] as $to):?>
							<input type="hidden" name="<?=htmlspecialcharsbx($arParams["INPUT_PREFIX"])?>[{{VALUE}}][<?=htmlspecialcharsbx($to)?>]" value="{{<?=htmlspecialcharsbx($to)?>}}" />
                        <?php endforeach?>
                    <?php endif?>
				</span>
                <?php $template = trim(ob_get_flush());?>
		    </script>
			<?php
			foreach($arParams['DATA'] as $item)
			{
				print($helper->fillTemplate($template, $item));
			}
			?>
		</span>

		<span class="task-form-field-controls">
	        <span class="task-form-field-loading"><?=Loc::getMessage('TASKS_COMMON_LOADING')?>...</span>
	        <input
					class="js-id-tdp-mem-sel-is-search js-id-network-selector-search task-form-field-search task-form-field-input"
					type="text"
					value=""
					autocomplete="off"
					data-groupId="<?= array_key_exists('GROUP_ID', $arParams) ? $arParams['GROUP_ID'] : 0 ?>"
					data-role="<?= array_key_exists('ROLE_KEY', $arParams) ? $arParams['ROLE_KEY'] : 0 ?>"
			/>

		    <?php if($arParams['MAX'] == 1 && $arParams['MIN'] == 1): // single and required?>
				<a href="javascript:void(0);" class="js-id-tdp-mem-sel-is-control task-form-field-link">
				    <?=Loc::getMessage('TASKS_COMMON_CHANGE')?>
			    </a>
            <?php else:?>
                <?php $add = $arParams['MAX'] > 1;?>
				<a href="javascript:void(0);" class="js-id-tdp-mem-sel-is-control task-form-field-when-filled task-form-field-link <?php if($add):?>add<?php endif?>">
				    <?=Loc::getMessage($add ? 'TASKS_COMMON_ADD_MORE' : 'TASKS_COMMON_CHANGE')?>
			    </a>
				<a href="javascript:void(0);" class="js-id-tdp-mem-sel-is-control task-form-field-when-empty task-form-field-link add">
				    <?=Loc::getMessage('TASKS_COMMON_ADD')?>
			    </a>
            <?php endif?>
	    </span>

        <?php if($arResult['JS_DATA']['inputSpecial']):?>
			<input
					class="js-id-tdp-mem-sel-sole-input"
					type="hidden"
					name="<?=htmlspecialcharsbx($arParams["INPUT_PREFIX"])?><?=htmlspecialcharsbx($arParams['SOLE_INPUT_POSTFIX'])?>"
					value="<?=intval($arResult['TEMPLATE_DATA']['IDS'][0])?>"
			/>
        <?php else:?>
            <?php // in case of all items removed, the field should be sent anyway?>
			<input type="hidden" name="<?=htmlspecialcharsbx($arParams["INPUT_PREFIX"])?>[]" value="" />
        <?php endif?>

	</div>

    <?php $helper->initializeExtension();?>

<?php endif?>
