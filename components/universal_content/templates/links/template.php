<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);
$this->setFrameMode(true);
$APPLICATION->SetPageProperty("CONTENT_SECTION_CLASS", "d-flex flex-column g-60 g-xs-40 mb-60 mb-xs-40");
$APPLICATION->SetPageProperty("CONTENT_TITLE_CLASS", "mb-0");
?>
<div class="container">
    <div class="d-flex flex-column flex-sm-row g-20">
        <?php
        foreach ($arResult["ITEMS"] as $item){?>
            <a class="link-black fs-18 max-width-464 flex-1" href="<?=$item["PROPERTIES"]["PROP_LINK"]["VALUE"]?>">
                <div class="link-img-wrap">
                    <img class="d-block w-100" src="<?=$item["PREVIEW_PICTURE"]?>" alt="<?=$item["NAME"]?>">
                </div>
                <span class="d-block mt-12"><?=$item["NAME"]?></span>
            </a>
            <?php
        }
        ?>

    </div>
</div>
