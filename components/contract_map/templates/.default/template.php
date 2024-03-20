<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);
$this->setFrameMode(true);
$APPLICATION->SetPageProperty("CONTENT_SECTION_CLASS", "container-1920 overflow-hidden p-0 m-auto");
\Bitrix\Main\Page\Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/build/css/mapAreasPage.css", true);
?>
<script>
    var currentSiteLang = localStorage.getItem("langSite");
    if(currentSiteLang === 'en')
        document.write("<script src='https://api-maps.yandex.ru/2.1/?apikey=25af6466-04ea-4a44-9a46-a31836f41428&amp;lang=en_RU' type='text/javascript'><\/script>");
    else
        document.write("<script src='https://api-maps.yandex.ru/2.1/?apikey=25af6466-04ea-4a44-9a46-a31836f41428&amp;lang=ru_RU' type='text/javascript'><\/script>");

</script>

<div class="container">
    <div class="area-filter d-flex g-20">
        <div class="inputBlock max-width-418 w-100">
            <select class="select2 w-100" name="area" id="areaRegion">
                <option value="0" id="area-0"><?=Loc::getMessage("CONTRACT_MAP_AREA_SPB")?></option>
                <?$i = 1;?>
                <?foreach ($arResult['LIST_DISTRICT'] as $area):?>
                    <option value="<?=$i?>" id="<?=$area['UF_XML_ID']?>"><?=$area['UF_NAME']?></option>
                <?$i++?>
                <?endforeach;?>
            </select>
        </div>
        <div class="area-switch" style="display:none;">
            <label class="switch m-0 page__text" id="areaObject"><span class="switch__label"><?=Loc::getMessage("CONTRACT_MAP_SHOW_OBJECT")?></span>
                <input class="switch__checkbox" type="checkbox"><span class="switch__slider"></span>
            </label>
        </div>
        <div class="mt-05 area-filter__wrapper-checkbox">
            <div class="d-flex flex-wrap align-items-center g-50 g-row-20 g-xs-column-20">
                <div class="checkboxInput">
                    <input type="checkbox" name="Школы" id="school" checked>
                    <label class="mb-0" for="school"><?=Loc::getMessage("CONTRACT_TMP_SCHOOL")?></label>
                </div>
                <div class="checkboxInput">
                    <input type="checkbox" name="Медицинские учреждения" id="medic" checked>
                    <label class="mb-0" for="medic"><?=Loc::getMessage("CONTRACT_TMP_MED")?></label>
                </div>
                <div class="checkboxInput">
                    <input type="checkbox" name="Детские сады" id="kindergartens" checked>
                    <label class="mb-0" for="kindergartens"><?=Loc::getMessage("CONTRACT_TMP_KIDS")?></label>
                </div>
                <div class="checkboxInput">
                    <input type="checkbox" name="Прочие организации" id="other" checked>
                    <label class="mb-0" for="other"><?=Loc::getMessage("CONTRACT_TMP_OTHER")?></label>
                </div>
                <button class="link-green border-0 bg-transparent p-0 show-all" type="button"><?=Loc::getMessage("CONTRACT_TMP_SHOW_ALL")?></button>
                <button class="link-green border-0 bg-transparent p-0 hide-all" type="button"><?=Loc::getMessage("CONTRACT_TMP_HIDE_ALL")?></button>
            </div>
        </div>
    </div>
</div>
<div class="area-wrapper">
    <div class="area-map" id="area-map">
        <div class="area-map-legend">
            <ul class="d-flex g-10 area-map-legend__list page__text m-0">
                <li class="area-map-legend__item d-flex g-12"><span class="d-block rounded-circle bg-yellow"></span><span><?=Loc::getMessage("CONTRACT_TMP_ELECTRO")?></span></li>
                <li class="area-map-legend__item d-flex g-12"><span class="d-block rounded-circle bg-orange"></span><span><?=Loc::getMessage("CONTRACT_TMP_TOPLO")?></span></li>
                <li class="area-map-legend__item d-flex g-12"><span class="d-block rounded-circle bg-orange-yellow"></span><span><?=Loc::getMessage("CONTRACT_TMP_CMESH")?></span></li>
            </ul>
        </div>
    </div>
    <div class="area-description">
        <button class="area-description__show hide">
            <svg class="bvi-svg" role="img">
                <use xlink:href="<?=SITE_TEMPLATE_PATH?>/build/img/icons/icons.svg#mapArrow"></use>
            </svg>
        </button>
        <div class="area-description__content page__text d-flex flex-column g-20">
            <p class="area-description__title font-weight-bold pr-25"><?=Loc::getMessage("CONTRACT_MAP_AREA_INFO")?>
            <ul class="m-0 d-flex flex-column g-20 area-description__list">
                <?$i = 1;?>
                <?foreach ($arResult['LIST_DISTRICT'] as $area):?>
                    <li class="area-description__item">
                        <p class="font-weight-bold"><?=$area['UF_NAME']?></p>
                        <div class="area-description__item-wrapper"><span><?=Loc::getMessage("CONTRACT_MAP_CONTRACT_SUM")?></span><span><?=$area['UF_CONTRACT_SUMM']?></span></div>
                        <div class="area-description__item-wrapper"><span><?=Loc::getMessage("CONTRACT_MAP_CONTRACT_POTENTIAL_SUM")?></span><span><?=$area['UF_SUM_CONTRACT_POTENTIAL']?></span></div>
                        <div class="area-description__item-wrapper"><span><?=Loc::getMessage("CONTRACT_MAP_CONTRACT_COUNT_label")?></span><span><?=Loc::getMessage("CONTRACT_MAP_CONTRACT_COUNT", ["#COUNT#" => $area['UF_CONTRACT_COUNT']])?></span></div>
                        <div class="area-description__item-wrapper"><span><?=Loc::getMessage("CONTRACT_MAP_CONTRACT_COUNT_POTENTIAL_label")?></span><span><?=$area['UF_COUNT_CONTRACT_POTENTIAL']?></span></div>
                    </li>
                <?endforeach;?>
            </ul>
            </p>
        </div>
    </div>
</div>

<script>
    BX.ready(function(){
        var ContractMap = BX.ContractMap.create(
            {
                urlJsonPolygon:{
                    mapCoordinate:<?=CUtil::PhpToJSObject($arResult['mapCoordinate'])?>,
                },
                coordsObject:<?=CUtil::PhpToJSObject($arResult['coordsObject'])?>,
            }
        );


        $('.header__link_lang').on('click', function (e) {
            if(e.screenX && e.screenX != 0 && e.screenY && e.screenY != 0){
                setTimeout(function () {
                    location.reload();
                }, 600);
            }

        });
    });
</script>
