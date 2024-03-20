<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);
$this->setFrameMode(true);
$APPLICATION->SetPageProperty("CONTENT_SECTION_CLASS", "d-flex flex-column g-60 g-xs-40 mb-60 mb-xs-40");
$APPLICATION->SetPageProperty("CONTENT_TITLE_CLASS", "mb-0");

?>
<div class="container-1920 p-0 container overflow-hidden">
    <div class="swiper no-gutters max-width-1624 overflow-visible" id="swiper" data-id="swiper" data-swiper="{}">

        <div class="swiper-wrapper d-grid grid-columns-auto">
            <?php
            foreach ($arResult["ITEMS"] as $item){?>
                <div class="d-flex flex-column img-signature swiper-slide ">
                    <img class="img d-block w-100 flex-grow-1" src="<?=$item["PREVIEW_PICTURE"]?>" alt="<?=$item["NAME"]?>">
                    <div class="bg-dark-red signature" title="<?=$item["NAME"]?>"><?=$item["NAME"]?></div>
                </div>
                <?php
            }
            ?>
        </div>
        <div class="swiper-pagination"></div>
        <button class="bg-transparent swiper-button-prev swiper-button text-white">
            <svg role="img">
                <use xlink:href="<?=SITE_TEMPLATE_PATH?>/build/img/icons/icons.svg#prev"></use>
            </svg>
        </button>
        <button class="bg-transparent swiper-button-next swiper-button text-white">
            <svg role="img">
                <use xlink:href="<?=SITE_TEMPLATE_PATH?>/build/img/icons/icons.svg#next"></use>
            </svg>
        </button>
    </div>
</div>