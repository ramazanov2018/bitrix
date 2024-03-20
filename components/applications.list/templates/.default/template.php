<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
?>
    <script src="<?=$templateFolder?>/script.js"></script>
    <link rel="stylesheet" href="<?=$templateFolder?>/style.css">

<? if ($arResult['APPLICATIONS']) { ?>
    <div class="popup-linksApp-header ">
        <div class="popup-linksApp-header__btnAll">
            <span data-tab_id="all" class="AppBtn-default AppBtn-default__act">Приложения</span>
        </div>
        <div id="tabsElement" class="popup-linksApp-header__btnItems">
            <?
            foreach ($arResult['APP_CATEGORIES'] as $item){?>
                <div class="popup-linksApp-header__btnItem">
                    <span data-tab_id="<?= $item["ID"] ?>"  class="AppBtn-default AppBtn-default__disact"><?=$item['NAME']?></span>
                </div>
            <?}
            ?>
        </div>
        <div class="popup-linksApp-header__btnSwitch">
            <img id="btn-preview_tab" src="<?=$templateFolder?>/image/chevron-left.svg" alt="">
            <img id="btn-next_tab" src="<?=$templateFolder?>/image/chevron-right.svg" alt="">
        </div>
        <div class="TabsClearBoth"></div>
    </div>
    <div data-tab_id="all" class="popup-linksApp__tabContent">
        <div class="popup-linksApp">
            <?foreach ($arResult['ALL_APPLICATIONS'] as $arApplication) { ?>
                <div class="popup-linksApp__item">
                    <div data-app_id="<?= $arApplication['ID'] ?>" class="popup-linksApp-item__info infoDisable"></div>
                    <div data-app_id="<?= $arApplication['ID'] ?>" class="popup-linksApp-item__favorite<?=$arApplication['FAV']?' active':''?>"></div>
                    <!--href="--><?/*= $arApplication['APP_URL_VALUE'] */?>
                    <a href="<?= $arApplication['APP_URL_VALUE'] ?>"
                       class="js_app_link"
                       data-link_id="<?= $arApplication['ID'] ?>"
                       target="_blank">
                        <? if ($arApplication['APP_ICON_VALUE']) { ?>
                            <div class="popup-linksApp-item__image"
                                 style="background-image: url('<?= $arApplication['APP_ICON_VALUE'] ?>')"></div>
                        <? } ?>
                        <div class="popup-linksApp-item__title"><?= $arApplication['NAME'] ?></div>
                    </a>
                </div>
                <div data-app_id="<?= $arApplication['ID'] ?>" class="popup-linksApp__item__desc <?=isset($arApplication['APP_POPUP_VALUE']) ?'popup--big':'popup--small'?> block-hidden">
                    <div class="item--desc__text">
                        <p class="desc__textTitle"><?= $arApplication['NAME'] ?></p>
                        <?=$arApplication['PREVIEW_TEXT']?>
                    </div>
                    <span class="linksApp__item__desc-close-icon"></span>
                    <span class="linksApp__item__desc-close-btn AppBtn-default AppBtn-default__act">Закрыть</span>
                </div>
            <? } ?>
        </div>
    </div>
    <?
    foreach ($arResult['APP_CATEGORIES'] as $item){?>
        <div data-tab_id="<?= $item["ID"] ?>" class="popup-linksApp__tabContent block-hidden">
            <div class="popup-linksApp">
                <?foreach ($arResult['APPLICATIONS'][$item["ID"]] as $arApplication):?>
                    <div class="popup-linksApp__item">
                        <div data-app_id="<?= $arApplication['ID'] ?>" class="popup-linksApp-item__info infoDisable"></div>
                        <div data-app_id="<?= $arApplication['ID'] ?>" class="popup-linksApp-item__favorite<?=$arApplication['FAV']?' active':''?>"></div>
                        <!--href="--><?/*= $arApplication['APP_URL_VALUE'] */?>
                        <a href="<?= $arApplication['APP_URL_VALUE'] ?>"
                           class="js_app_link"
                           data-link_id="<?= $arApplication['ID'] ?>"
                           target="_blank">
                            <? if ($arApplication['APP_ICON_VALUE']) { ?>
                                <div class="popup-linksApp-item__image"
                                     style="background-image: url('<?= $arApplication['APP_ICON_VALUE'] ?>')"></div>
                            <? } ?>
                            <div class="popup-linksApp-item__title"><?= $arApplication['NAME'] ?></div>
                        </a>
                    </div>
                    <div data-app_id="<?= $arApplication['ID'] ?>" class="popup-linksApp__item__desc block-hidden">
                        <div class="item--desc__text">
                            <h5><?= $arApplication['NAME'] ?></h5>
                            <?=$arApplication['PREVIEW_TEXT']?>
                        </div>
                        <span class="linksApp__item__desc-close-icon"></span>
                        <span class="linksApp__item__desc-close-btn AppBtn-default AppBtn-default__act">Закрыть</span>
                    </div>
                <?endforeach;?>
            </div>
        </div>
    <?}
    ?>
<? } ?>