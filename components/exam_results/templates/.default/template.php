<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
use \Bitrix\Main\Localization\Loc;

$this->setFrameMode(true);?>
<?
\Bitrix\Main\Page\Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/css/gradePage.css");
\Bitrix\Main\Page\Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/js/chart.min.js", true);
\Bitrix\Main\Page\Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/js/chartjs-plugin-datalabels.js", true);
//\Bitrix\Main\Page\Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/js/pages/gradeAnalitcsStatsPage.js", true);
?>
<section class="gradePage gradeRegistryPage">
    <div class="gradePage-container">
        <p class="title-page"><?=Loc::getMessage("exam_result_page_title")?></p>
        <div class="mb-44">
            <div class="filter">
                <form action="">
                    <p class="mb-24 fw700"><?=Loc::getMessage("exam_result_page_block1_title")?></p>

                    <div class="filter-row">
                        <div class="inputBlock select">
                            <select class="select2" name="reg" id="reg">
                                <option value=""></option>
                                <?foreach ($arResult['REGION'] as $key => $value):?>
                                    <option <?=$arResult['PARAMS']['reg'] == $value['UF_XML_ID']?'selected':''?> value="<?=$value['UF_XML_ID']?>"><?=$value['UF_NAME']?></option>
                                <?endforeach?>
                            </select>
                        </div>
                    </div>

                    <div class="filter-row">
                        <div class="inputBlock select">
                            <select class="select2" name="loc" id="loc" <?=$arResult['PARAMS']['loc']?'':'disabled'?> >
                                <option value=""></option>
                                <?foreach ($arResult['LOCATIONS'] as $key => $value):?>
                                    <option <?=$arResult['PARAMS']['loc'] == $value['UF_XML_ID']?'selected':''?> value="<?=$value['UF_XML_ID']?>"><?=$value['UF_NAME']?></option>
                                <?endforeach?>
                            </select>
                        </div>
                    </div>
                    <div class="filter-row">
                        <div class="inputBlock select">
                            <select  class="select2" name="type" id="type">
                                <option value=""></option>
                                <?foreach ($arResult['TYPE_EXAM'] as $key => $value):?>
                                    <option <?=$arResult['PARAMS']['type'] == $value['UF_XML_ID']?'selected':''?> value="<?=$value['UF_XML_ID']?>"><?=$value['UF_NAME']?></option>
                                <?endforeach?>
                            </select>
                        </div>
                    </div>

                    <div class="filter-row">
                        <div class="inputBlock select">
                            <select class="select2" name="res" id="res">
                                <option value=""></option>
                                <?foreach ($arResult['EXAM_RESULT'] as $key => $value):?>
                                    <option <?=$arResult['PARAMS']['res'] == $value['UF_XML_ID']?'selected':''?> value="<?=$value['UF_XML_ID']?>"><?=$value['UF_NAME']?></option>
                                <?endforeach?>
                            </select>
                        </div>
                    </div>


                    <div class="filter-row">
                        <div class="inputBlock dateTakeOff withoutMonth">
                            <div class="checkboxInput">
                                <input type="checkbox" id="dateCheck" name="dateCheck">
                                <label for="dateCheck"><?=Loc::getMessage("exam_result_date_from")?>
                                    <div class="datepicker-container">
                                        <input class="datepicker" type="text" name="date_from" id="date_from" value="<?=$arResult['PARAMS']['date_from']?>" placeholder="<?=Loc::getMessage("exam_result_placeholder_date_from")?>">
                                    </div>
                                    <p><?=Loc::getMessage("exam_result_date_to")?></p>
                                    <div class="datepicker-container">
                                        <input class="datepicker" type="text" name="date_to" id="date_to" value="<?=$arResult['PARAMS']['date_to']?>" placeholder="<?=Loc::getMessage("exam_result_placeholder_date_from")?>">
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="btns">
                        <button class="btn-primary" name="set_filter_result" value="Y"><?=Loc::getMessage("exam_result_btn_applay")?></button>
                        <button class="btn-secondary cancelClick"><?=Loc::getMessage("exam_result_btn_reset")?></button>
                    </div>
                </form>
            </div>
        </div>
        <?if(!empty($arResult['TABLE'])):?>
            <div class="table-container mb-122">
                <p class="mb-12 fw700"><?=Loc::getMessage("exam_result_page_block2_title")?></p><table class="mb-44">
                    <thead>
                    <tr>
                        <th>№</th>
                        <th>
                            <div class="containerWithArrows">
                                <?=Loc::getMessage("exam_result_table_region")?>
                                <div class="arrows">
                                    <div class="arrow-top"></div>
                                    <div class="arrow-bottom"></div>
                                </div>
                            </div>
                        </th>
                        <th>
                            <div class="containerWithArrows">
                                <?=Loc::getMessage("exam_result_table_location")?>
                                <div class="arrows">
                                    <div class="arrow-top"></div>
                                    <div class="arrow-bottom">  </div>
                                </div>
                            </div>
                        </th>
                        <th>
                            <div class="containerWithArrows">
                                <?=Loc::getMessage("exam_result_table_type_exam")?>
                                <div class="arrows">
                                    <div class="arrow-top"></div>
                                    <div class="arrow-bottom"></div>
                                </div>
                            </div>
                        </th>
                        <th>
                            <div class="containerWithArrows">
                                <?=Loc::getMessage("exam_result_table_result_exam")?>
                                <div class="arrows">
                                    <div class="arrow-top"></div>
                                    <div class="arrow-bottom"></div>
                                </div>
                            </div>
                        </th>
                        <th>
                            <div class="containerWithArrows">
                                <?=Loc::getMessage("exam_result_table_date_exam")?>
                                <div class="arrows">
                                    <div class="arrow-top"></div>
                                    <div class="arrow-bottom"></div>
                                </div>
                            </div>
                        </th>
                        <th>
                            <div class="containerWithArrows">
                                <?=Loc::getMessage("exam_result_table_count_exam")?>
                                <div class="arrows">
                                    <div class="arrow-top"></div>
                                    <div class="arrow-bottom">   </div>
                                </div>
                            </div>
                        </th></tr>
                    </thead>


                    <tbody>
                    <?$i = 0;
                    foreach($arResult["TABLE"] as $arItem):
                        $i++
                        ?>
                        <?
                        $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                        $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
                        ?>
                        <tr id="<?=$this->GetEditAreaId($arItem['ID']);?>">
                            <td><?=$i?></td>
                            <td>
                                <?=$arItem["REGION_NAME"]?>
                            </td>
                            <td>
                                <?=$arItem["LOCATION_NAME"]?>
                            </td>
                            <td>
                                <?=$arResult['TYPE_EXAM'][$arItem["EXAM_LEVEL"]]['UF_NAME']?>
                            </td>
                            <td>
                                <?=$arItem["EXAM_RESULT"]?>
                            </td>
                            <td>
                                <?=$arItem["EXAM_DATE"]?>
                            </td>
                            <td>
                                <?=$arItem["QUANTITY"]?>
                            </td>
                        </tr>
                    <?endforeach;?>
                    </tbody>
                </table>

                <?
                $APPLICATION->IncludeComponent(
                    "bitrix:main.pagenavigation",
                    "",
                    array(
                        "NAV_OBJECT" => $arResult['NAW'],
                        //"SEF_MODE" => "N",
                    ),
                    false
                );
                ?>
            </div>
        <?endif?>
    </div>
</section>