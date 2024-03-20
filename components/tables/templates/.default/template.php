<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);
$this->setFrameMode(true);?>

<?php
$APPLICATION->SetPageProperty("CONTENT_SECTION_CLASS", "container mb-44 mb-xs-61");
$lang = "RU";
if (LANGUAGE_ID == "en")
    $lang = "EN";

$mainSection = $arResult["MAIN_SECTION"];
?>

<div class="page__text mb-4">
    <p><?=$mainSection["~UF_DESCRIPTION_".$lang]?></p>
</div>
<?if(!empty($mainSection["UF_FILE_".$lang])){?>
    <a class="w-fit-content link-green decoration-underline link-download mb-4" href="<?=$mainSection["UF_FILE_".$lang]?>" download=""><?=Loc::GetMessage("TABLE_FILE_DOWNLOAD")?>
        <svg class="download" role="img">
            <use xlink:href="<?=SITE_TEMPLATE_PATH?>/build/img/icons/icons.svg#download"></use>
        </svg>
    </a>
<?}?>


<?

$count = 1;
foreach ($arResult["ITEMS"] as $sectId => $SectionItem){
    $isSubSect = false;
    $ElemCount = 0;
    ?>
    <div class="table-wrapper">
        <?if($sectId != $arParams["IBLOCK_SECTION_ID"]){
            $isSubSect = true;
            ?>
            <span class="table-title"><?=$count?>. <?=$arResult["SECTIONS"][$sectId]["NAME_".$lang]?></span>
        <?}?>
        <div class="table-wrapper__inner mb-10">
            <table class="table">
                <thead class="table__thead">
                <tr class="table__thead-tr">
                    <th class="table__thead-th thead-th">
                        <span class="d-flex align-items-center table__th-padding"><?=Loc::GetMessage("TABLE_COL_NUMBER")?></span>
                    </th>
                    <?
                    if ($mainSection["UF_COL_COUNT"] > 0)
                    for ($i = 1; $i <=  $mainSection["UF_COL_COUNT"]; $i++){?>
                        <th class="table__thead-th thead-th">
                            <span class="d-flex align-items-center table__th-padding"><?=htmlspecialchars_decode($mainSection["UF_TABLE_COL_".$i."_".$lang])?></span>
                        </th>
                    <?}
                    ?>
                </tr>
                </thead>
                <tbody class="table__tbody">
                <?foreach ($SectionItem as $item){
                    $ElemCount++?>
                    <tr class="table__tbody-tr">
                        <td class="table__tbody-td" data-td-name="number"><?=$isSubSect?$count.".":""?><?=$ElemCount?></td>
                        <?
                        for ($i = 1; $i <=  $mainSection["UF_COL_COUNT"]; $i++){?>
                            <td class="table__tbody-td"><?=$item["TABLE_COL_".$i."_".$lang]["~VALUE"]["TEXT"]?></td>
                        <?}
                        ?>
                    </tr>
                <?}?>
                </tbody>
            </table>
        </div>
    </div>
<?
    $count++;
}?>