<?
use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeTemplateLangFile(__FILE__);
?>
<div class="сrm_seance_block">

    <?if($arResult['error'] == 1):?>
    <? ShowError(Loc::getMessage("SEANCE_NOT_FOUND"));?>
    <?else:?>
        <form id="crm_seance_form" method="post">
        <?=bitrix_sessid_post()?>

        <table id="сrm_seance_table" class="сrm_seance">
            <tr>
                <th rowspan="2"><?=Loc::getMessage("SEANCE_TITLE_FORMAT");?></th>
                <?if ($arResult['WEEK_COUNT'] > '0'):?>
                    <th colspan="<?=$arResult['WEEK_COUNT']?>"><?=Loc::getMessage("SEANCE_TITLE_SEANCE");?></th>
                <?endif;?>
                <th colspan="2"><?=Loc::getMessage("SEANCE_TITLE_KEY");?></th>
            </tr>
            <tr>
                <?for ($i = 1; $arResult['WEEK_COUNT'] >= $i; $i++):?>
                    <td><?=$i.' '.Loc::getMessage("SEANCE_TITLE_WEEK")?></td>
                <?endfor;?>
                <td><?=Loc::getMessage("SEANCE_TITLE_DATE_START");?></td>
                <td><?=Loc::getMessage("SEANCE_TITLE_DATE_END");?></td>
            </tr>

            <?foreach ($arResult['SEANCES'] as $SEANCE):?>
                <tr>
                    <td><?=$SEANCE['TITLE']?></td>
                    <?for ($i = 1; $arResult['WEEK_COUNT'] >= $i; $i++):?>
                        <td><?=$SEANCE[$i.'_WEEK_PLAN']?></td>
                    <?endfor;?>
                    <td><?=$SEANCE['DATE_START']?></td>
                    <td><?=$SEANCE['DATE_END']?></td>
                </tr>
            <?endforeach;?>
        </table>
        <div id="div-crm-field-save" class="ui-button-panel-wrapper" style="text-align: center; margin:10px">
            <div class="ui-button-panel ">
                <button id="btn-crm-seance-save" name="save" value="Y" class="ui-btn ui-btn-success">Сохранить</button>
            </div>
        </div>
    </form>
    <?endif;?>
</div>

<script>
    BX.ready(function(){
        var Btn = BX('btn-crm-seance-save');

        BX.bind(Btn, 'click', function(event) {
            event.preventDefault();

            var form = BX('crm_seance_form');

            var data = serialize(form);
            BX.ajax({
                url: '/local/components/vlgfilm/crm.seance.table/ajax.php',
                data: data ,
                method: 'POST',
                timeout: 30,
                async: true,
                processData: true,
                scriptsRunFirst: true,
                emulateOnload: true,
                start: true,
                cache: false,
                onsuccess: function(data){
                   console.log(data);
                },
                onfailure: function(){
                }
            });
        });
    });
</script>
