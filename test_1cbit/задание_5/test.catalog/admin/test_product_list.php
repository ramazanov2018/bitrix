<?php
use Test\Catalog\TestCategory;
use Test\Catalog\TestProduct;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");// первый общий пролог
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/test.catalog/include.php"); // инициализация модуля

// подключим языковой файл
IncludeModuleLangFile(__FILE__);

// получим права доступа текущего пользователя на модуль
$POST_RIGHT = $APPLICATION->GetGroupRight("test.catalog");

// если нет прав - отправим к форме авторизации с сообщением об ошибке
if ($POST_RIGHT == "D")
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
?>

<?
// здесь будет вся серверная обработка и подготовка данных
$sTableID = "tbl_product"; // ID таблицы
$oSort = new CAdminSorting($sTableID, "ID", "desc"); // объект сортировки
$lAdmin = new CAdminList($sTableID, $oSort); // основной объект списка

$parameters = array(
    "order" => ["CATEGORY_ID" => "ASC"]
);
$rsData = TestProduct::getList($parameters);

// преобразуем список в экземпляр класса CAdminResult
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// отправим вывод переключателя страниц в основной объект $lAdmin
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("test_catalog_nav")));
$lAdmin->AddHeaders(array(
    array( "id"    =>"PRODUCT_ID",
        "content"  =>"ID",
        "default"  =>true,
    ),
    array(  "id"    =>"PRODUCT_NAME",
        "content"  =>GetMessage("product_name"),
        "default"  =>true,
    ),
    array(  "id"    =>"PRICE",
        "content"  =>GetMessage("product_price"),
        "default"  =>true,
    ),
    array(  "id"    =>"CATEGORY_ID",
        "content"  =>GetMessage("category_name"),
        "default"  =>true,
    ),
));
while($arRes = $rsData->NavNext(true, "f_")):
    // создаем строку. результат - экземпляр класса CAdminListRow
    $row =& $lAdmin->AddRow($f_ID, $arRes);

    $category = TestCategory::getById((int)$f_CATEGORY_ID)->fetch();
    $row->AddViewField("CATEGORY_ID", '<p>'.($category["CATEGORY_NAME"])? $category["CATEGORY_NAME"]:$f_CATEGORY_ID.'</p>');
endwhile;

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("product_title"));
?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php"); // второй общий пролог
?>

<?
$lAdmin->DisplayList();
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
