<?php
IncludeModuleLangFile(__FILE__);
if ($APPLICATION->GetGroupRight("rns.access.matrix")!="D"){
    $aMenu = array(
        "parent_menu" => "global_menu_settings",
        "sort" => 200,
        "text" => GetMessage("ACCESS_MATRIX_MENU_MAIN"),
        "title" => GetMessage("ACCESS_MATRIX_MAIN_TITLE"),
        "items_id" => "access_matrix",
        "url" => "access_matrix.php?lang=".LANGUAGE_ID ,

    );
}
return $aMenu;