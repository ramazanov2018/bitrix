<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use \Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__);
$this->setFrameMode(true);
$APPLICATION->SetPageProperty("CONTENT_SECTION_CLASS", "d-flex flex-column g-60 g-xs-40 mb-60 mb-xs-40");
$APPLICATION->SetPageProperty("CONTENT_TITLE_CLASS", "mb-0");
?>
<div class="container">
    <div class="d-flex flex-column g-40">
        <?if($arParams["TITLE_SHOW"] == "Y" && !empty($arResult["MAIN_SECTION"]["PAGE_TITLE"])){?>
            <p class="title-second-subtitle mb-0"><?=$arResult["MAIN_SECTION"]["PAGE_TITLE"]?></p>
        <?}?>
        <div class="table-grid">
            <?php
            foreach ($arResult["ITEMS"] as $item){?>
                <?
                $fType = pathinfo($item["PROPERTIES"]["FILE"]["FILE_DATA"]["SRC"], PATHINFO_EXTENSION);
                ?>
                <div class="table-grid__tr">
                    <div class="table-grid__td"><?=$item["NAME"]?></div>
                    <div class="table-grid__td"><?=$fType?></div>
                    <div class="table-grid__td"><?=number_format($item["PROPERTIES"]["FILE"]["FILE_DATA"]["FILE_SIZE"] / 1048576, 2)?>mb</div>
                    <div class="table-grid__td d-flex align-items-center justify-content-end g-12">
                        <?if($fType == "pdf"):?>
                            <a class="btn link-btn btn-danger" href="<?=$item["PROPERTIES"]["FILE"]["FILE_DATA"]["SRC"]?>" target="_blank" title="Открыть документ">
                                <svg class="look" role="img">
                                    <use xlink:href="<?=SITE_TEMPLATE_PATH?>/build/img/icons/icons.svg#look"></use>
                                </svg>
                            </a>
                        <?endif;?>
                        <a class="btn link-btn btn-danger" href="<?=$item["PROPERTIES"]["FILE"]["FILE_DATA"]["SRC"]?>" download="<?=$item["PROPERTIES"]["FILE"]["FILE_DATA"]["SRC"]?>" title="Скачать документ">
                            <svg class="download" role="img">
                                <use xlink:href="<?=SITE_TEMPLATE_PATH?>/build/img/icons/icons.svg#download"></use>
                            </svg>
                        </a>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>

