<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);
$this->setFrameMode(true);
CJSCore::Init(array("jquery"));

\Bitrix\Main\Page\Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/build/css/documentsMain.css", true);
\Bitrix\Main\Page\Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/build/js/page/components/controllerSlider.js", true);
\Bitrix\Main\Page\Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/build/js/page/components/imageZoom.js", true);
\Bitrix\Main\Page\Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/build/js/page/components/datepickerCustom.js", true);
\Bitrix\Main\Page\Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/build/js/page/components/pushkinListWithSlider.js", true);
\Bitrix\Main\Page\Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/build/js/page/components/pushkinStickyMenu.js", true);
\Bitrix\Main\Page\Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/build/js/page/getBvi.js", true);
\Bitrix\Main\Page\Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/build/js/page/documentsMain.js", true);
?>
<form class="container documentsMain" action="#"  method="get">
    <h3 class="documentsMain__title"><?=Loc::getMessage("DCD_PAGE_TITLE")?></h3>
    <div class="documentsMain__filter datepicker">
        <label class="documentsMain__label datepicker__wrap">
            <input class="documentsMain__input datepicker__date" id="dateStart" name="date_from" readonly value="<?=$arResult['REQUEST_PARAM']['date_from']?>">
        </label>
        <label class="documentsMain__label datepicker__wrap">
            <input class="documentsMain__input datepicker__date" id="dateEnd" name="date_to" readonly value="<?=$arResult['REQUEST_PARAM']['date_to']?>">
        </label>
        <button class="documentsMain__filterButton btn-primary" id="showDocFilter" type="submit" name="date_filter" value="Y"><?=Loc::getMessage("DCD_FILTER_BTN")?></button>
    </div>
    <div class="documentsMain__view">
        <div class="filterListDoc">
            <div class="filterListDoc__block">
                <ul class="filterListDoc__tabs">
                    <input id="activeTab" type="hidden" name="activeTab" value="<?=$arResult['REQUEST_PARAM']['activeTab']?>">
                    <?foreach ($arResult['SECTIONS'] as $key => $section):?>
                        <li class="filterListDoc__tabItem">
                            <button class="filterListDoc__tab <?=($section['CURRENT'] == 'Y') ? 'filterListDoc__tab_active':''?> " data-cur = "<?=($section['CURRENT'] == 'Y') ? 'Y':'N'?>"  data-id="<?=$key?>" type="button"><?=$section['NAME']?></button>
                        </li>
                    <?endforeach;?>
                </ul>
            </div>
            <?foreach ($arResult['SECTIONS'] as $key => $section):?>
                <?if($section['ITEMS']):?>
                        <ul class="filterListDoc__list" data-doc="<?=$key?>">
                            <?foreach ($section['ITEMS'] as $item):?>
                                <?
                                $icon = 'unknown';
                                switch ($item['DOCUMENT']['TYPE']){
                                    case 'PDF':
                                    case 'pdf':
                                        $icon = 'pdf';
                                        break;
                                    case 'doc':
                                    case 'docx':
                                        $icon = 'word';
                                        break;
                                    case 'xls':
                                    case 'xlsx':
                                        $icon = 'excel';
                                        break;
                                    case 'pptx':
                                        $icon = 'powerpoint';
                                        break;
                                    case 'url':
                                        $icon = 'link';
                                        break;
                                }
                                ?>
                                <li class="filterListDoc__item word_break">
                                    <a class="filterListDoc__item" href="<?=$item['DOCUMENT']['SRC']?>" <?=($item['DOCUMENT']['IS_FILE'] == 'Y') ? 'download=""' :'target="_blank"'?>>
                                        <svg class="filterListDoc__svg" role="img">
                                            <use class="filterListDoc__use" xlink:href="<?=$templateFolder?>/icons/icons.svg#<?=$icon?>"></use>
                                        </svg>
                                        <p style="word-wrap: break-word" title="<?=$item['TITLE']?>" class="filterListDoc__text"><?=mb_strimwidth($item['TITLE'], 0, 35, "...")?></p>
                                    </a>
                                </li>
                            <?endforeach;?>
                        </ul>
                <?else:?>
                        <div class="filterListDoc__list content-empty " style="display: block" data-doc="<?=$key?>">
                            <p><?=Loc::getMessage("DCD_NO_RESULTS")?></p>
                        </div>
                <?endif;?>
            <?endforeach;?>
        </div>
    </div>
    <p class="documentsMain__description"><?=Loc::getMessage("DCD_PAGE_DESCRIPTION")?></p>
</form>