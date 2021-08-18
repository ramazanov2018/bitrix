<?php
IncludeModuleLangFile(__FILE__);
if ($APPLICATION->GetGroupRight("rns.courses")!="D"){
    $aMenu = array(
        "parent_menu" => "global_menu_services",
        "sort" => 200,
        "text" => GetMessage("COURSES_MENU_MAIN"),
        "title" => GetMessage("COURSES_MENU_MAIN_TITLE"),
        "items_id" => "menu_courses",
        "items" => array(
            array(
                "text" => GetMessage("COURSES_MENU_LIST"),
                "url" => "courses_list.php?lang=".LANGUAGE_ID ,
                "more_url" => array("course_edit.php","course_subscribe.php"),
                "title" => GetMessage("COURSES_MENU_LIST_TITLE"),
            )
        )
    );
}
return $aMenu;