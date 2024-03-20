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
        <div class="mb-96">
            <div class="filter">
                <form action="">
                    <div class="filter-block">
                        <p class="mb-24 fw700"><?=Loc::getMessage("exam_statistics_page_title")?></p>
                        <div class="filter-row d-flex">
                            <div class="radioInput radioBlock">
                                <input <?=$arResult['PARAMS']['filterDate'] == 'filterDate1'?'checked':''?> type="radio" name="filterDate" id="filterDate1" value="filterDate1">
                                <label for="filterDate1">
                                    <div class="inputBlock select monthTakeOff">
                                        <p><?=Loc::getMessage("exam_statistics_date_month")?></p>
                                        <select class="select2" name="stat_month">
                                            <option></option>
                                            <option <?=$arResult['PARAMS']['stat_month'] == '01' ?'selected':''?> value="01"><?=Loc::getMessage("exam_statistics_date_month_01")?></option>
                                            <option <?=$arResult['PARAMS']['stat_month'] == '02' ?'selected':''?> value="02"><?=Loc::getMessage("exam_statistics_date_month_02")?></option>
                                            <option <?=$arResult['PARAMS']['stat_month'] == '03' ?'selected':''?> value="03"><?=Loc::getMessage("exam_statistics_date_month_03")?></option>
                                            <option <?=$arResult['PARAMS']['stat_month'] == '04' ?'selected':''?> value="04"><?=Loc::getMessage("exam_statistics_date_month_04")?></option>
                                            <option <?=$arResult['PARAMS']['stat_month'] == '05' ?'selected':''?> value="05"><?=Loc::getMessage("exam_statistics_date_month_05")?></option>
                                            <option <?=$arResult['PARAMS']['stat_month'] == '06' ?'selected':''?> value="06"><?=Loc::getMessage("exam_statistics_date_month_06")?></option>
                                            <option <?=$arResult['PARAMS']['stat_month'] == '07' ?'selected':''?> value="07"><?=Loc::getMessage("exam_statistics_date_month_07")?></option>
                                            <option <?=$arResult['PARAMS']['stat_month'] == '08' ?'selected':''?> value="08"><?=Loc::getMessage("exam_statistics_date_month_08")?></option>
                                            <option <?=$arResult['PARAMS']['stat_month'] == '09' ?'selected':''?> value="09"><?=Loc::getMessage("exam_statistics_date_month_09")?></option>
                                            <option <?=$arResult['PARAMS']['stat_month'] == '10' ?'selected':''?> value="10"><?=Loc::getMessage("exam_statistics_date_month_10")?></option>
                                            <option <?=$arResult['PARAMS']['stat_month'] == '11' ?'selected':''?> value="11"><?=Loc::getMessage("exam_statistics_date_month_11")?></option>
                                            <option <?=$arResult['PARAMS']['stat_month'] == '12' ?'selected':''?> value="12"><?=Loc::getMessage("exam_statistics_date_month_12")?></option>
                                        </select>
                                        <select class="select2" name="stat_years">
                                            <option></option>
                                            <?foreach ($arResult['YEARS'] as $key => $value):?>
                                                <option <?=$arResult['PARAMS']['stat_years'] == $key ?'selected':''?> value="<?=$key?>"><?=$value?></option>
                                            <?endforeach?>
                                           </select>
                                    </div>
                                </label>
                            </div>
                            <div class="radioInput radioBlock">
                                <input <?=$arResult['PARAMS']['filterDate'] == 'filterDate2'?'checked':''?> type="radio" name="filterDate" id="filterDate2" value="filterDate2">
                                <label for="filterDate2">
                                    <div class="inputBlock dateTakeOff">
                                        <p><?=Loc::getMessage("exam_statistics_date_from")?></p>
                                        <div class="datepicker-container">
                                            <input class="datepicker" type="text" name="statics_date_from" id="dateFrom2" value="<?=$arResult['PARAMS']['statics_date_from']?>" placeholder="<?=Loc::getMessage("exam_statistics_placeholder_date_from")?>">
                                        </div>
                                        <p><?=Loc::getMessage("exam_statistics_date_to")?></p>
                                        <div class="datepicker-container">
                                            <input class="datepicker" type="text" name="statics_date_to" id="dateTo2" value="<?=$arResult['PARAMS']['statics_date_to']?>" placeholder="<?=Loc::getMessage("exam_statistics_placeholder_date_from")?>">
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="filter-row">
                            <div class="radioInput radioBlock">
                                <input <?=$arResult['PARAMS']['filterItem'] == 'filterItemDistrict'?'checked':''?> type="radio" name="filterItem" id="filterItem0" value="filterItemDistrict">
                                <label for="filterItem0">
                                    <div class="inputBlock select">
                                        <select class="select2" name="stat_districts[]" multiple id="stat_districts">
                                            <option value=""></option>
                                            <?foreach ($arResult['DISTRICTS'] as $key => $value):?>
                                                <option <?=in_array($value['UF_XML_ID'], $arResult['PARAMS']['stat_districts'])?'selected':''?> value="<?=$value['UF_XML_ID']?>"><?=$value['UF_NAME']?></option>
                                            <?endforeach?>
                                        </select>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="filter-row">
                            <div class="radioInput radioBlock">
                                <input <?=$arResult['PARAMS']['filterItem'] == 'filterItemRegion'?'checked':''?> type="radio" name="filterItem" id="filterItem1" value="filterItemRegion">
                                <label for="filterItem1">
                                    <div class="inputBlock select">
                                        <select class="select2" name="stat_reg[]" multiple id="stat_reg">
                                            <option value=""></option>
                                            <?foreach ($arResult['REGION'] as $key => $value):?>
                                                <option <?=in_array($value['UF_XML_ID'], $arResult['PARAMS']['stat_reg'])?'selected':''?> value="<?=$value['UF_XML_ID']?>"><?=$value['UF_NAME']?></option>
                                            <?endforeach?>
                                        </select>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="filter-row">
                            <div class="radioInput radioBlock">
                                <input <?=$arResult['PARAMS']['filterItem'] == 'filterItemLocation'?'checked':''?> type="radio" name="filterItem" id="filterItem2" value="filterItemLocation">
                                <label for="filterItem2">
                                    <div class="inputBlock select">
                                        <select class="select2" name="stat_loc[]" multiple id="stat_loc">
                                            <option value=""></option>
                                            <?foreach ($arResult['LOCATIONS'] as $key => $value):?>
                                                <option <?=in_array($value['UF_XML_ID'], $arResult['PARAMS']['stat_loc'])?'selected':''?> value="<?=$value['UF_XML_ID']?>"><?=$value['UF_NAME']?></option>
                                            <?endforeach?>
                                        </select>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="filter-row">
                            <div class="radioInput radioBlock">
                                <input <?=$arResult['PARAMS']['filterItem'] == 'filterItemLevel'?'checked':''?> type="radio" name="filterItem" id="filterItem3" value="filterItemLevel">
                                <label for="filterItem3">
                                    <div class="inputBlock select">
                                        <select  class="select2" name="stat_type[]" multiple id="stat_type" >
                                            <option value=""></option>
                                            <?foreach ($arResult['TYPE_EXAM'] as $key => $value):?>
                                                <option <?=in_array($value['UF_XML_ID'], $arResult['PARAMS']['stat_type'])?'selected':''?> value="<?=$value['UF_XML_ID']?>"><?=$value['UF_NAME']?></option>
                                            <?endforeach?>
                                        </select>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="btns">
                        <button name="set_filter_statistics" value="Y" class="btn-primary"><?=Loc::getMessage("exam_statistics_btn_applay")?></button>
                        <button class="btn-secondary cancelClick"><?=Loc::getMessage("exam_statistics_btn_reset")?></button>
                    </div>
                </form>
            </div>
        </div>
        <?if (!empty($arResult['ITEMS'])):?>
            <div class="diagramms-container">
                <div class="diagramms-item">
                    <canvas id="statisticsDiagrammBlue" height="190px"></canvas>
                    <p>Столбцовая диаграмма распредения числа прошедших экзаменов</p>
                </div>
                <div class="diagramms-item">
                    <canvas id="statisticsDiagrammGreen" height="190px"></canvas>
                    <p>
                        Гистограмма распределения усредненного балла и каждой из четырех координат
                        вектора результатов за экзамен
                    </p>
                </div>
                <div class="diagramms-item">
                    <canvas id="statisticsDiagrammRed" height="190px"></canvas>
                    <p>Столбцовая диаграмма распредения средних баллов по четырем компетенциям</p>
                </div>
            </div>
        <?endif;?>
    </div>
</section>

<?if (!empty($arResult['ITEMS'])):?>
    <script>
        (function ($) {
            $(document).ready(function () {
                var diagrams = new Diagrams(<?=CUtil::PhpToJSObject($arResult['ITEMS']['DIAGRAM_BY_QUANTITY'])?>, <?=CUtil::PhpToJSObject($arResult['ITEMS']['HISTOGRAM'])?>, <?=CUtil::PhpToJSObject($arResult['ITEMS']['DIAGRAM_BY_SCORES'])?>);
            });
        })(jQuery);
    </script>
<?endif;?>


