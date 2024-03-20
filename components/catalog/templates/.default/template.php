<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
$this->setFrameMode(true);?>
<?
$APPLICATION->SetPageProperty("CONTENT_SECTION_CLASS", "container catalog");
\Bitrix\Main\Page\Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/build/js/page/components/doubleRangeInput.js", true);
\Bitrix\Main\Page\Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/build/js/page/catalog.js", true);
\Bitrix\Main\Page\Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/build/css/catalog.css", true);
?>




    <a class="btn btn-success catalog__authorisation" href="/personal/">Подать заявку на включение в каталог</a>
    <div class="catalog__search-title">
        <h3 class="title-three">Поиск по параметрам</h3>
    </div>
    <form class="catalog__form">
        <div class="d-flex align-items-center g-20 mb-4 catalog__form-group-input">
            <div class="catalog__input-wrapper">
                <input class="w-100 form-control input" id="name-inn" placeholder="Название" name="name" type="text" value="<?=$arResult['PARAMS']['name']?>">
            </div>
            <div class="catalog__input-wrapper">
                <input class="w-100 form-control input" id="equipment-materials" placeholder="Оборудования/материалы" name="catalog-product" type="text" value="<?=$arResult['PARAMS']['catalog-product']?>">
            </div>
            <div class="inputBlock catalog__input-wrapper">
                <select class="select2 w-100" name="selectIndustry" id="selectIndustry">
                    <option></option>
                    <?foreach ($arResult["INDUSTRIAL"] as $key => $industrial):?>
                        <option <?=$arResult['PARAMS']['selectIndustry'] == $key ? 'selected':''?> value="<?=$key?>"><?=$industrial['UF_NAME']?></option>
                    <?endforeach;?>
                </select>
            </div>
            <div class="inputBlock catalog__input-wrapper">
                <select class="select2 w-100" name="selectRegion" id="selectRegion">
                    <option></option>
                    <?foreach ($arResult["REGION"] as $region):?>
                        <option <?=$arResult['PARAMS']['selectRegion'] == $region["UF_XML_ID"] ? 'selected':''?> value="<?=$region["UF_XML_ID"]?>"><?=$region["UF_NAME"]?></option>
                    <?endforeach;?>
                </select>
            </div>
        </div>
        <div class="d-flex align-items-end catalog__range-wrapper">
            <div class="slider-range catalog__slider-range catalog__slider-range-percent"><span class="slider-range__label">Процент импортных комплектующих</span>
                <div class="slider-range__wrapper">
                    <div class="slider-scale">
                        <div class="slider-scale-tick">0%</div>
                        <div class="slider-scale-tick">20%</div>
                        <div class="slider-scale-tick">40%</div>
                        <div class="slider-scale-tick">60%</div>
                        <div class="slider-scale-tick">80%</div>
                        <div class="slider-scale-tick">100%</div>
                    </div>
                    <input type="hidden" value="" id="range-percent-lower" name="range-percent-lower">
                    <input type="hidden" value="" id="range-percent-upper" name="range-percent-upper">
                    <div class="range" id="range-percent" data-option="{&quot;min&quot;:0,&quot;max&quot;:100,&quot;step&quot;:10,&quot;values&quot;:[<?=$arResult['PARAMS']['range-percent-lower']?>,<?=$arResult['PARAMS']['range-percent-upper']?>]}"></div>
                </div>
            </div>
            <div class="slider-range catalog__slider-range catalog__slider-range-rating"><span class="slider-range__label">Рейтинг</span>
                <div class="slider-range__wrapper">
                    <div class="slider-scale">
                        <div class="slider-scale-tick">0</div>
                        <div class="slider-scale-tick">20</div>
                        <div class="slider-scale-tick">40</div>
                        <div class="slider-scale-tick">60</div>
                        <div class="slider-scale-tick">80</div>
                        <div class="slider-scale-tick">100</div>
                    </div>
                    <input type="hidden" value="<?=$arResult['PARAMS']['range-rating-lower']?>" id="range-rating-lower" name="range-rating-lower">
                    <input type="hidden" value="<?=$arResult['PARAMS']['range-rating-upper']?>" id="range-rating-upper" name="range-rating-upper">
                    <div class="range" id="range-rating" data-option="{&quot;min&quot;:0,&quot;max&quot;:100,&quot;step&quot;:10,&quot;values&quot;:[<?=$arResult['PARAMS']['range-rating-lower']?>,<?=$arResult['PARAMS']['range-rating-upper']?>]}"></div>
                </div>
            </div>
            <button id="setFilter" class="btn btn-success pt-2 pb-2" type="submit">Найти</button>
        </div>
        <div class="d-flex align-items-center flex-wrap g-18">
            <?if(!empty($arResult['PARAMS']['catalog-inn'])):?>
                <span class="d-flex align-items-center justify-content-between w-fit-content tag tag-green"><?=$arResult['PARAMS']['catalog-inn']?>
                          <button class="ml-2 border-0 bg-transparent tag__clear bvi-no-styles filter-field-clear" type="button" data-clear="name-inn">&plus;</button>
                </span>
            <?endif;?>
            <?if(!empty($arResult['PARAMS']['catalog-product'])):?>
                <span class="d-flex align-items-center justify-content-between w-fit-content tag tag-green"><?=$arResult['PARAMS']['catalog-product']?>
                          <button class="ml-2 border-0 bg-transparent tag__clear bvi-no-styles filter-field-clear" type="button" data-clear="equipment-materials">&plus;</button>
                </span>
            <?endif;?>
            <?if(!empty($arResult['PARAMS']['selectIndustry'])):?>
                <span class="d-flex align-items-center justify-content-between w-fit-content tag tag-green"><?=$arResult['INDUSTRIAL'][$arResult['PARAMS']['selectIndustry']]['UF_NAME']?>
                      <button class="ml-2 border-0 bg-transparent tag__clear bvi-no-styles filter-field-clear" type="button" data-clear="selectIndustry">&plus;</button>
                </span>
            <?endif;?>
            <?if(!empty($arResult['PARAMS']['selectRegion'])):?>
                <span class="d-flex align-items-center justify-content-between w-fit-content tag tag-green"><?=$arResult['REGION'][$arResult['PARAMS']['selectRegion']]['UF_NAME']?>
                      <button class="ml-2 border-0 bg-transparent tag__clear bvi-no-styles filter-field-clear" type="button" data-clear="selectRegion">&plus;</button>
                </span>
            <?endif;?>
            <?if(!empty($arResult['PARAMS']['range-percent-upper'])):?>
                <span class="d-flex align-items-center justify-content-between w-fit-content tag tag-green"><?=$arResult['PARAMS']['range-percent-lower']. '% -' .$arResult['PARAMS']['range-percent-upper'].'%'?>
                      <button class="ml-2 border-0 bg-transparent tag__clear bvi-no-styles filter-field-clear" type="button" data-clear-lower="range-percent-lower" data-clear-upper="range-percent-upper">&plus;</button>
                </span>
            <?endif;?>
            <?if(!empty($arResult['PARAMS']['range-rating-upper'])):?>
                <span class="d-flex align-items-center justify-content-between w-fit-content tag tag-green"><?=$arResult['PARAMS']['range-rating-lower']. ' - ' .$arResult['PARAMS']['range-rating-upper']?>
                      <button class="ml-2 border-0 bg-transparent tag__clear bvi-no-styles filter-field-clear" type="button" data-clear-lower="range-rating-lower" data-clear-upper="range-rating-upper">&plus;</button>
                </span>
            <?endif;?>
        </div>
        <button id="form_reset" data-href="<?=$APPLICATION->GetCurPage()?>" class="link-green decoration-underline bg-transparent border-0 p-0 tag clear-filter" type="submit">Сбросить все фильтры</button>
        <div class="table-wrapper catalogPage__content">
            <div class="table-wrapper__inner mb-32 mb-xs-26">
                <table class="table catalog__table">
                    <thead class="table__thead">
                    <tr class="table__thead-tr">
                        <th class="table__thead-th thead-th">
                            <?
                            $sortSelected = false;
                            if ($arResult['PARAMS']['sort'] == "NAME"){
                                $sortSelected = true;
                                $order = $arResult['PARAMS']['order'] == "asc" ? "desc" : "asc";
                                $page = $APPLICATION->GetCurPageParam("sort=NAME&order=$order", array("sort", "order"));
                            }else
                                $page = $APPLICATION->GetCurPageParam("sort=NAME&order=desc", array("sort", "order"));
                            ?>
                            <button data-href="<?=$page?>" class="catalogSort d-flex align-items-center table__btn-control" type="button" data-sort-name="manufacturer">Производитель/поставщик<span class="table__sort">
                                       <svg class=" <?=($sortSelected && $order == "desc") ? "sort-active" : "" ?> bvi-img sort sort-up" role="img">
                                        <use xlink:href="<?=SITE_TEMPLATE_PATH?>/build/img/icons/icons.svg#sort"></use>
                                      </svg>
                                      <svg class=" <?=($sortSelected && $order == "asc") ? "sort-active" : "" ?> bvi-img sort sort-down" role="img">
                                        <use xlink:href="<?=SITE_TEMPLATE_PATH?>/build/img/icons/icons.svg#sort"></use>
                                      </svg></span></button>
                        </th>
                        <th class="table__thead-th thead-th">
                            <?
                            $sortSelected = false;
                            if ($arResult['PARAMS']['sort'] == "INDUSTRY_NAME"){
                                $sortSelected = true;
                                $order = $arResult['PARAMS']['order'] == "asc" ? "desc" : "asc";
                                $page = $APPLICATION->GetCurPageParam("sort=INDUSTRY_NAME&order=$order", array("sort", "order"));
                            }else
                                $page = $APPLICATION->GetCurPageParam("sort=INDUSTRY_NAME&order=desc", array("sort", "order"));
                            ?>
                            <span class="d-flex align-items-center table__btn-control">Область деятельности организации</span>
                        </th>
                        <th class="table__thead-th thead-th">
                            <?
                            $sortSelected = false;
                            if ($arResult['PARAMS']['sort'] == "PERCENTAGE_OF_IMPORTS_VALUE"){
                                $sortSelected = true;
                                $order = $arResult['PARAMS']['order'] == "asc" ? "desc" : "asc";
                                $page = $APPLICATION->GetCurPageParam("sort=PERCENTAGE_OF_IMPORTS_VALUE&order=$order", array("sort", "order"));
                            }else
                                $page = $APPLICATION->GetCurPageParam("sort=PERCENTAGE_OF_IMPORTS_VALUE&order=desc", array("sort", "order"));
                            ?>
                            <button data-href="<?=$page?>" class="catalogSort d-flex align-items-center table__btn-control" type="button" data-sort-name="percent">Процент импортн. комплектн.<span class="table__sort">
                                      <svg class=" <?=($sortSelected && $order == "desc") ? "sort-active" : "" ?> bvi-img sort sort-up" role="img">
                                        <use xlink:href="<?=SITE_TEMPLATE_PATH?>/build/img/icons/icons.svg#sort"></use>
                                      </svg>
                                      <svg class=" <?=($sortSelected && $order == "asc") ? "sort-active" : "" ?> bvi-img sort sort-down" role="img">
                                        <use xlink:href="<?=SITE_TEMPLATE_PATH?>/build/img/icons/icons.svg#sort"></use>
                                      </svg></span></button>
                        </th>
                        <th class="table__thead-th thead-th">
                            <?
                            $sortSelected = false;
                            if ($arResult['PARAMS']['sort'] == "RATING_VALUE"){
                                $sortSelected = true;
                                $order = $arResult['PARAMS']['order'] == "asc" ? "desc" : "asc";
                                $page = $APPLICATION->GetCurPageParam("sort=RATING_VALUE&order=$order", array("sort", "order"));
                            }else
                                $page = $APPLICATION->GetCurPageParam("sort=RATING_VALUE&order=desc", array("sort", "order"));
                            ?>
                            <button data-href="<?=$page?>" class="catalogSort d-flex align-items-center table__btn-control" type="button" data-sort-name="rating">Рейтинг<span class="table__sort">
                                      <svg class=" <?=($sortSelected && $order == "desc") ? "sort-active" : "" ?> bvi-img sort sort-up" role="img">
                                        <use xlink:href="<?=SITE_TEMPLATE_PATH?>/build/img/icons/icons.svg#sort"></use>
                                      </svg>
                                      <svg class=" <?=($sortSelected && $order == "asc") ? "sort-active" : "" ?> bvi-img sort sort-down" role="img">
                                        <use xlink:href="<?=SITE_TEMPLATE_PATH?>/build/img/icons/icons.svg#sort"></use>
                                      </svg></span></button>
                        </th>
                        <th class="table__thead-th thead-th">
                            <span class="d-flex align-items-center table__btn-control">ИНН</span>
                        </th>
                    </tr>
                    </thead>
                    <tbody class="table__tbody catalogPage__cards">
                    <?foreach ($arResult['ITEMS'] as $item):?>
                        <tr class="table__tbody-tr catalogCard">
                            <td class="table__tbody-td catalog__table-td-manufacturer" data-td-name="manufacturer">
                                <a href="/deyatelnost/katalog-otechestvennoy-produktsii/detail/?id=<?=$item['ID']?>" class="link-green decoration-underline" target="_self"><?=$item['NAME']?></a>
                            </td>
                            <td class="table__tbody-td catalog__table-td-activity" data-td-name="activity"><?=$item['INDUSTRY_NAME']?></td>
                            <td class="table__tbody-td catalog__table-cell-percent" data-td-name="percent"><?=$item['PERCENTAGE_OF_IMPORTS_VALUE']?></td>
                            <td class="table__tbody-td catalog__table-td-rating" data-td-name="rating">
                                <span class="d-flex align-items-center"><?=$item['RATING_VALUE']?>
                                    <?if(!empty($item["SHOW_ICON_VALUE"])):?>
                                        <svg role="img"><use xlink:href="<?=SITE_TEMPLATE_PATH?>/build/img/icons/icons.svg#rating"></use></svg>
                                    <?endif;?>
                                </span>
                            </td>
                            <td class="table__tbody-td catalog__table-td-inn" data-td-name="inn"><?=$item['INN_VALUE']?></td>
                        </tr>
                    <?endforeach;?>
                    </tbody>
                </table>
            </div>
            <?
            $APPLICATION->IncludeComponent(
                "bitrix:main.pagenavigation",
                "catalog",
                array(
                    "NAV_OBJECT" => $arResult['NAW'],
                ),
                false
            );
            ?>
        </div>
    </form>
<?php
//\Bitrix\Main\Page\Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/build/css/catalog.css");
?>



