<?php
IncludeModuleLangFile(__FILE__);
if ($APPLICATION->GetGroupRight("test.catalog")!="D"){
    $aMenu = array(
        "parent_menu" => "global_menu_services",
        "sort" => 200,
        "text" => GetMessage("TEST_CATALOG_MENU_MAIN"),
        "title" => GetMessage("TEST_CATALOG_MENU_MAIN_TITLE"),
        "items_id" => "menu_test_catalog",
        "items" => array(
            array(
                "text" => GetMessage("TEST_PRODUCT_MENU_LIST"),
                "url" => "test_product_list.php?lang=".LANGUAGE_ID ,
                "title" => GetMessage("TEST_PRODUCT_MENU_LIST_TITLE"),
            ),
            array(
                "text" => GetMessage("TEST_CATEGORY_MENU_LIST"),
                "url" => "test_category_list.php?lang=".LANGUAGE_ID ,
                "title" => GetMessage("TEST_CATEGORY_MENU_LIST_TITLE"),
            )
        )
    );
}
return $aMenu;