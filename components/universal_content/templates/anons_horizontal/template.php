<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);
$this->setFrameMode(true);
$APPLICATION->SetPageProperty("CONTENT_SECTION_CLASS", "d-flex flex-column g-60 g-xs-40 mb-60 mb-xs-40");
$APPLICATION->SetPageProperty("CONTENT_TITLE_CLASS", "mb-0");

?>
<div class="container-1920 container overflow-hidden">
    <?php
    foreach ($arResult["ITEMS"] as $item){?>
        <div class="d-flex flex-column img-signature flex-1"><img class="img d-block w-100 flex-grow-1" src="<?=$item["PREVIEW_PICTURE"]?>" alt="<?=$item["NAME"]?>">
            <div class="bg-dark-red signature" title="<?=$item["NAME"]?>"><?=$item["NAME"]?></div>
        </div>
        <?php
    }
    ?>
</div>
