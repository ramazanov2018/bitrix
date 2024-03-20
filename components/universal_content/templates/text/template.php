<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);
$this->setFrameMode(true);
$APPLICATION->SetPageProperty("CONTENT_SECTION_CLASS", "d-flex flex-column g-60 g-xs-40 mb-60 mb-xs-40");
$APPLICATION->SetPageProperty("CONTENT_TITLE_CLASS", "mb-0");
?>

<div class="container">
    <div class="page__default">
        <div>
            <?=$arResult["ITEMS"][0]["PREVIEW_TEXT"]?>
        </div>
    </div>
</div>
