<?php
$arResult["CATEGORY_ITEMS"] = [];
foreach ($arResult["ITEMS"] as $item){
    if(!empty($item["PROPERTIES"]["FILE_CATEGORY"]['VALUE'])){
        $arResult["CATEGORY_ITEMS"][$item["PROPERTIES"]["FILE_CATEGORY"]['VALUE']][] = $item;
    }
}