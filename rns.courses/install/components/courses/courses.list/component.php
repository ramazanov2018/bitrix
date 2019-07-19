<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Courses\CoursesSubscribe;
use Courses\CoursesControl;

if(!CModule::IncludeModule("rns.courses"))
{
    ShowError(GetMessage("COURSES_MODULE_NOT_INSTALLED"));
    return;
}

$courseSub = new CoursesSubscribe();
$CoursesControl = new CoursesControl();

global $USER;
$USER_ID = "";
if ($USER->IsAuthorized()){
    $USER_ID = (int)$USER->GetID();
    if (!empty($_REQUEST["course_subscribe"]) && is_numeric($_REQUEST["course_subscribe"])) {
        $_REQUEST["course_subscribe"] = (int)$_REQUEST["course_subscribe"];
        if (is_int($_REQUEST["course_subscribe"])){
            $arFields = Array(
                "USER_ID" => $USER_ID,
                "COURSE_ID" => $_REQUEST["course_subscribe"],
            );
            $ID = $courseSub->add($arFields);
        }
    }
    if (!empty($_REQUEST["course_unsubscribe"]) && is_numeric($_REQUEST["course_unsubscribe"])) {
        $_REQUEST["course_unsubscribe"] = (int)$_REQUEST["course_unsubscribe"];
        if (is_int($_REQUEST["course_unsubscribe"])){
            $arFields = Array(
                "USER_ID" => $USER_ID,
                "COURSE_ID" => $_REQUEST["course_unsubscribe"],
            );
            //$ID = $courseSub->add($arFields);
            $ID = $courseSub->delete($arFields);
        }
    }
}

$arCourses = [];
$result = $CoursesControl->getList(array());

while ($course = $result->fetch())
{
    $arCourses[] = $course;
}

foreach($arCourses as $arCourse)
{
    $subscribe = 0;
    if (!empty($USER_ID)){

        $parameters = array(
            'filter' => array(
                'COURSE_ID' => (int)$arCourse["ID"],
                'USER_ID' => $USER_ID
            )
        );
        $resultSub = $courseSub->getList($parameters);
        while ($row = $resultSub->fetch())
        {
            $subscribe = 1;
        }
    }
    $arResult["COURSES"][]=array(
        "ID"=>$arCourse["ID"],
        "NAME"=>$arCourse["NAME"],
        "DESCRIPTION"=>$arCourse["DESCRIPTION"],
        "DATE_START"=>$arCourse["DATE_START"],
        "DATE_END"=>$arCourse["DATE_END"],
        "PRICE"=>$arCourse["PRICE"],
        "SUBSCRIBE"=>$subscribe
    );
}
$this->IncludeComponentTemplate();
?>