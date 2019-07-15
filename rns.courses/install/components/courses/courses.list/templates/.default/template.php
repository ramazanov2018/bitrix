<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<div class="row row-flex">
    <? foreach ($arResult["COURSES"] as $courses) { ?>
        <div class=" col-lg-4 col-sm-6 col-sm-6 col-xs-12 course">
            <div class="name">
                <h3 class="course_name" id="course_name" onclick="courseDetail(<?=$courses["ID"]?>)"><?= $courses["NAME"] ?></h3>
            </div>
            <div class="description">
                <p>
                    <?
                    if (!empty($courses["DESCRIPTION"]))
                    {
                        echo mb_strimwidth($courses["DESCRIPTION"],0,60)."...";
                    }
                    ?>
                </p>
            </div>
            <div class="subscribe_general">

                <?
                global $USER;
                if ($USER->IsAuthorized()) {
                    if ($courses["SUBSCRIBE"]) { ?>
                        <div class="unsubscribe">
                            <a id="course_subscribe"href="?course_unsubscribe=<?= $courses["ID"] ?>">
                                <button class="btn btn-primary"><?= GetMessage("UNSUBSCRIBE_COURSE") ?></button>
                            </a>
                        </div>
                    <? } else { ?>
                        <div class="subscribe">
                            <a id="course_unsubscribe" href="?course_subscribe=<?= $courses["ID"] ?>">
                                <button class="btn btn-success"><?= GetMessage("SUBSCRIBE_COURSE") ?></button>
                            </a>
                        </div>
                    <? }
                }else{?>
                    <div class="subscribe">
                        <a onclick="no_authorize(this)" data-text="<?=GetMessage("NO_AUTHORIZE") ?>">
                            <button class="btn btn-success"><?= GetMessage("SUBSCRIBE_COURSE")?></button>
                        </a>
                    </div>
                <?}?>

            </div>
        </div>
        <div class="course_detail_hide" id="<?=$courses["ID"]?>">
            <div class="closed" onclick="close_course(this)">
            </div>
            <div class="name">
                <h3><?= $courses["NAME"] ?></h3>
            </div>
            <div class="description">
                <p><span class="course_desc"><?=GetMessage("DESCRIPTION_COURSE")?></span><?= $courses["DESCRIPTION"] ?></p>
            </div>
            <div class="date_start">
                <p><span class="course_desc"><?=GetMessage("DATE_START_COURSE")?></span><?= $courses["DATE_START"] ?></p>
            </div>
            <div class="date_end">
                <p><span class="course_desc"><?=GetMessage("DATE_END_COURSE")?></span><?= $courses["DATE_END"] ?></p>
            </div>
            <div class="price">
                <p><span class="course_desc"><?=GetMessage("PRICE_COURSE")?></span><?= $courses["PRICE"]?>р</p>
            </div>
        </div>
    <? } ?>
</div>
