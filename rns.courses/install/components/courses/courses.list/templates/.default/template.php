<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<div class="row row-flex">
    <? foreach ($arResult["ITEMS"] as $arItem) { ?>
        <div class=" col-lg-4 col-sm-6 col-sm-6 col-xs-12 course">
            <div class="name">
                <h3 class="course_name" id="course_name"
                    onclick="courseDetail(<?= $arItem["ID"] ?>)"><?= $arItem["NAME"] ?></h3>
            </div>
            <div class="description">
                <p>
                    <?
                    if (!empty($arItem["DESCRIPTION"])) {
                        echo mb_strimwidth($arItem["DESCRIPTION"], 0, 60) . "...";
                    }
                    ?>
                </p>
            </div>
            <div class="subscribe_general">
                <div class="subscribe">
                    <? if ($arItem["MEMBER"] == "Y") {?>
                        <a class="btn btn-default" href="javascript:void(0);" data-role="removeMember"
                           data-course="<?= $arItem['ID'] ?>" data-url="<?= $APPLICATION->GetCurPage() ?>">
                            <?= GetMessage("UNSUBSCRIBE_COURSE") ?>
                        </a>
                    <?}else{?>
                        <a class="btn btn-primary" href="javascript:void(0);" data-role="addMember"
                           data-course="<?= $arItem['ID'] ?>" data-url="<?= $APPLICATION->GetCurPage() ?>">
                            <?= GetMessage("SUBSCRIBE_COURSE") ?>
                        </a>
                    <?}?>
                </div>
            </div>
        </div>

        <div class="course_detail_hide" id="<?= $arItem["ID"] ?>">
            <div class="closed" onclick="close_course(this)">
            </div>
            <div class="name">
                <h3><?= $arItem["NAME"] ?></h3>
            </div>
            <div class="description">
                <p><span class="course_desc"><?= GetMessage("DESCRIPTION_COURSE") ?></span><?= $arItem["DESCRIPTION"] ?>
                </p>
            </div>
            <div class="date_start">
                <p><span class="course_desc"><?= GetMessage("DATE_START_COURSE") ?></span><?= $arItem["DATE_START"] ?>
                </p>
            </div>
            <div class="date_end">
                <p><span class="course_desc"><?= GetMessage("DATE_END_COURSE") ?></span><?= $arItem["DATE_END"] ?></p>
            </div>
            <div class="price">
                <p><span class="course_desc"><?= GetMessage("PRICE_COURSE") ?></span><?= $arItem["PRICE"] ?>р</p>
            </div>
        </div>
    <? } ?>
</div>