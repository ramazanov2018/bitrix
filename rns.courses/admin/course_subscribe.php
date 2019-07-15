<?php
use Courses\CoursesSubscribe;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");// первый общий пролог
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/rns.courses/include.php"); // инициализация модуля
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/rns.courses/prolog.php"); // пролог модуля


// подключим языковой файл
IncludeModuleLangFile(__FILE__);


// получим права доступа текущего пользователя на модуль
$POST_RIGHT = $APPLICATION->GetGroupRight("rns.courses");


// если нет прав - отправим к форме авторизации с сообщением об ошибке
if ($POST_RIGHT == "D")
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
?>



<?
// здесь будет вся серверная обработка и подготовка данных
$sTableID = "tbl_courses_subscribes"; // ID таблицы
$oSort = new CAdminSorting($sTableID, "ID", "desc"); // объект сортировки
$lAdmin = new CAdminList($sTableID, $oSort); // основной объект списка


$cData = new CoursesSubscribe();
$parameters = array(
    'filter' => array(
        'COURSE_ID' => $_REQUEST["course_id"],
    )
);
$rsData = $cData->getList($parameters);
// преобразуем список в экземпляр класса CAdminResult
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// отправим вывод переключателя страниц в основной объект $lAdmin
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("course_nav")));
$lAdmin->AddHeaders(array(
    array(
        "id"    =>"ID",
        "content"  =>"ID",
        "default"  =>true,
    ),
    array(
        "id"    =>"COURSE_ID",
        "content"  =>GetMessage("course_id"),
        "default"  =>true,
    ),
    array(
        "id"    =>"USER_ID",
        "content"  =>GetMessage("subscribe_user_id"),
        "default"  =>true,
    ),
    array(
        "id"    =>"DATE_CREATE",
        "content"  =>GetMessage("course_subscribe_date"),
        "default"  =>true,
    ),
));
while($arRes = $rsData->NavNext(true, "f_")):
    // создаем строку. результат - экземпляр класса CAdminListRow
    $row =& $lAdmin->AddRow($f_ID, $arRes);
    // далее настроим отображение значений при просмотре и редактировании списка

    // параметр NAME будет редактироваться как текст, а отображаться ссылкой
    //$row->AddInputField("NAME", array("size"=>20));
    $row->AddViewField("USER_ID", '<a class="course_subscribe" href="user_edit.php?lang='.LANG.'&ID='.$f_USER_ID.'">'.GetMessage("subscribe_user").'('.$f_USER_ID.')</a>');
endwhile;

$aContext = array(
    array(
        "TEXT"=>GetMessage("ALL_COURSES"),
        "LINK"=>"courses_list.php?lang=".LANG,
        "TITLE"=>GetMessage("COURSE_LIST"),
        //"ICON"=>"btn_new",
    ),
);
$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("course_title"));
?>




<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php"); // второй общий пролог
$lAdmin->DisplayList();
?>




<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
