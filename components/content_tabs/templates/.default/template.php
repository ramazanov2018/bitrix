<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);
$this->setFrameMode(true);
$APPLICATION->SetPageProperty("CONTENT_SECTION_CLASS", "aboutCenter");

\Bitrix\Main\Page\Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/build/css/aboutCenter.css", true);?>

<?php
if (isset($arResult["ITEM"]["TOP_IMAGE_URL"])):
    ?>
    <div class="carousel slide" id="carouselExampleSlidesOnly" data-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active"><img class="d-block w-100" src="<?=$arResult["ITEM"]["TOP_IMAGE_URL"]?>" alt="..."></div>
        </div>
    </div>
<?php endif;?>
<div class="container">
    <div class="aboutCenter__content">
        <?$APPLICATION->IncludeComponent(
            "bitrix:menu",
            "content_tabs",
            Array(
                "ALLOW_MULTI_SELECT" => "N",
                "CHILD_MENU_TYPE" => "left",
                "DELAY" => "N",
                "MAX_LEVEL" => "1",
                "MENU_CACHE_GET_VARS" => array(""),
                "MENU_CACHE_TIME" => "3600",
                "MENU_CACHE_TYPE" => "N",
                "MENU_CACHE_USE_GROUPS" => "Y",
                "ROOT_MENU_TYPE" => "left",
                "USE_EXT" => "N"
            )
        );?>
        <div class="listInfo page__default" data-index="">
            <div>
                <?=$arResult["ITEM"]["CONTENT"]?>
            </div>
        </div>
    </div>
</div>
<?if (isset($arResult["ITEM"]["BOTTOM_IMAGE_URL"])):
    ?>
    <div class="carousel slide" id="carouselExampleSlidesOnly" data-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active"><img class="d-block w-100" src="<?=$arResult["ITEM"]["BOTTOM_IMAGE_URL"]?>" alt="..."></div>
        </div>
    </div>
<?php endif;?>



