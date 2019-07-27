<?php
use Courses\CoursesControl;
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
$sTableID = "tbl_courses"; // ID таблицы
$oSort = new CAdminSorting($sTableID, "ID", "desc"); // объект сортировки
$lAdmin = new CAdminList($sTableID, $oSort); // основной объект списка

$rsData = CoursesControl::getList(array());
// преобразуем список в экземпляр класса CAdminResult
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// отправим вывод переключателя страниц в основной объект $lAdmin
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("course_nav")));
$lAdmin->AddHeaders(array(
    array( "id"    =>"ID",
        "content"  =>"ID",
        "default"  =>true,
    ),
    array(  "id"    =>"NAME",
        "content"  =>GetMessage("course_name"),
        "default"  =>true,
    ),
    array(  "id"    =>"PRICE",
        "content"  =>GetMessage("course_price"),
        "default"  =>true,
    ),
    array(  "id"    =>"SUBSCRIBE",
        "content"  =>GetMessage("course_subscribe"),
        "default"  =>true,
    ),
));
while($arRes = $rsData->NavNext(true, "f_")):
    // создаем строку. результат - экземпляр класса CAdminListRow
    $row =& $lAdmin->AddRow($f_ID, $arRes);
    // далее настроим отображение значений при просмотре и редактировании списка

    // параметр NAME будет редактироваться как текст, а отображаться ссылкой
    //$row->AddInputField("NAME", array("size"=>20));
    $row->AddViewField("NAME", '<a href="course_edit.php?ID='.$f_ID.'&lang='.LANG.'">'.$f_NAME.'</a>');
    $row->AddViewField("SUBSCRIBE", '<a class="course_subscribe" href="course_subscribe.php?course_id='.$f_ID.'&lang='.LANG.'">'.GetMessage("course_subscribe").'</a>');
endwhile;
$aContext = array(
    array(
        "TEXT"=>GetMessage("MAIN_ADD"),
        "LINK"=>"course_edit.php?lang=".LANG,
        "TITLE"=>GetMessage("COURSE_ADD_TITLE"),
        "ICON"=>"btn_new",
    ),
);
$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("course_title"));
?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php"); // второй общий пролог
?>
<?
$lAdmin->DisplayList();
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
