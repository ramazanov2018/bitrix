<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);
$this->setFrameMode(true);
$APPLICATION->SetPageProperty("CONTENT_SECTION_CLASS", "d-flex flex-column g-60 g-xs-40 mb-60 mb-xs-40");
$APPLICATION->SetPageProperty("CONTENT_TITLE_CLASS", "mb-0");
?>

<div class="container pl-min-lg-10 p-0">
    <?if($arParams["TITLE_SHOW"] == "Y"){?>
        <p class="title-second-subtitle mb-0 pr-xs-20 pl-xs-20 pl-max-lg-10 pr-max-lg-10"><?=$arResult["MAIN_SECTION"]["PAGE_TITLE"]?></p>
    <?}?>
    <div class="d-flex flex-lg-row flex-column g-20 mt-40 mt-xs-3">
        <ol class="d-flex flex-column g-60 mt-0 mb-0 w-61 w-lg-100 number">
            <?php
            foreach ($arResult["ITEMS"] as $item){?>
                <li class="d-flex flex-column g-10">
                    <p class="title-second-subtitle mb-0"><?=$item["NAME"]?></p>
                    <div class="page__text fs-18 font-weight-normal"><p><?=$item["PREVIEW_TEXT"]?></p></div>
                </li>
                <?php
            }
            ?>
        </ol>
        <div class="mt-5 pt-30 pb-30 pr-25 pl-25 bg-white-light w-40 w-lg-100 fs-18 font-weight-normal page__text d-flex flex-column g-20">
            <?=$arResult["MAIN_SECTION"]['DESCRIPTION']?>
        </div>
    </div>
</div>

