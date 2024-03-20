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
        <ul class="accordion g-0 mb-0 mt-0" id="list-accordion">
            <?php
            $i = 0;
            foreach ($arResult["CATEGORY_ITEMS"] as $categoryId => $itemS){?>
                <li class="accordion__item accordion__item-bg accordion__item-border">
                    <h2 class="accordion__header">
                        <button class="btn justify-content-between <?=$i != 0 ? "collapsed" : ""?>" type="button" data-toggle="collapse" data-target="#collapse-<?=$i?>" aria-expanded="<?=$i == 0 ? "true" : "false"?>" aria-controls="collapse-<?=$i?>">
                            <span class="accordion__header-text"><?=$arResult['DOCUMENT_CATEGORIES'][$categoryId]["UF_NAME"]?></span>
                            <svg class="accordion__header-arrow" role="img">
                                <use xlink:href="<?=SITE_TEMPLATE_PATH?>/build/img/icons/icons.svg#accordion"></use>
                            </svg>
                        </button>
                    </h2>
                    <div class="collapse <?=$i == 0 ? "show" : ""?>" id="collapse-<?=$i?>" aria-labelledby="heading-<?=$i?>" data-parent="#list-accordion">
                        <div class="accordion__body pb-30">
                            <div class="table-grid">
                                <?php
                                foreach ($itemS as $item){?>
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
                </li>
                <?php
                $i++;
            }
            ?>
        </ul>
    </div>
</div>