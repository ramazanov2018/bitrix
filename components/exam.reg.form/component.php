<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Loader,
    Bitrix\Highloadblock as HL;
Loader::includeModule("highloadblock");
Loader::includeModule("iblock");
global $USER;

$res = CIBlock::GetList(
    [],
    [
        'TYPE'=>'exams',
        'ACTIVE'=>'Y',
        "CNT_ACTIVE"=>"Y",
        "CODE"=>'exam_registration',
        "CHECK_PERMISSIONS" => "N"
    ], true
);
if($ar_res = $res->Fetch())
{
    $res = CIBlock::GetProperties($ar_res['ID'], ["SORT" => "asc"], ["ID", "NAME", "CODE", "PROPERTY_TYPE", "LINK_IBLOCK_ID", "USER_TYPE"]);
    while($res_arr = $res->Fetch()){
        if($res_arr['CODE'] == "USER_LINK") continue;
        $arResult['PROPERTIES'][$res_arr['CODE']]['TITLE'] = $res_arr['NAME'];
        $arResult['PROPERTIES'][$res_arr['CODE']]['CODE'] = $res_arr['CODE'];
        $arResult['PROPERTIES'][$res_arr['CODE']]['PROPERTY_TYPE'] = $res_arr['PROPERTY_TYPE'];
        $arResult['PROPERTIES'][$res_arr['CODE']]['USER_TYPE'] = $res_arr['USER_TYPE'];
        if($res_arr['LINK_IBLOCK_ID']){
            $arResult['PROPERTIES'][$res_arr['CODE']]['LINK_IBLOCK_ID'] = $res_arr['LINK_IBLOCK_ID'];
        }
    }
}
if(isset($arResult['PROPERTIES'])){
    foreach ($arResult['PROPERTIES'] as $k => $property) {
        if ($property['LINK_IBLOCK_ID']) {
            $rsElement = CIBlockElement::GetList(
                $arOrder = ["SORT" => "ASC"],
                $arFilter = [
                    "IBLOCK_ID" => $property['LINK_IBLOCK_ID'],
                    ">=PROPERTY_EXAM_DATE" => ConvertDateTime(date("d.m.Y"), "YYYY-MM-DD")
                ],
                false,
                false,
                $arSelectFields = ["ID", "NAME", "PREVIEW_TEXT", "IBLOCK_SECTION_ID", "DATE_CREATE",
                    "PROPERTY_EXAM_LOCATION",
                    "PROPERTY_EXAM_LEVEL",
                    "PROPERTY_EXAM_DATE",
                ]
            );
            while ($arElement = $rsElement->GetNext()) {
                $arResult['PROPERTIES'][$k]['ELEMENTS'][$arElement['ID']] = $arElement;
            }
        }
    }
}

$hlblock_region = HL\HighloadBlockTable::getList(['filter' => ['=TABLE_NAME' => 'b_hlb_regions']])->fetch();
if($hlblock_region) {
    $entity = HL\HighloadBlockTable::compileEntity($hlblock_region);
    $entityClass = $entity->getDataClass();
    $resRegion = $entityClass::getList([
        'select' => ['ID', 'UF_NAME'],
        'order' => ['UF_SORT' => 'ASC'],
        'filter' => []
    ]);
    while($row = $resRegion->fetch()) {
        $arRegions[$row['ID']]['ID'] = $row['ID'];
        $arRegions[$row['ID']]['NAME'] = $row['UF_NAME'];
    }

    if(isset($arRegions)){
        $arResult['DIRECTORY']['REGION']['TITLE'] = "Регион экзамена";
        $arResult['DIRECTORY']['REGION']['CODE'] = "REGION";
        $arResult['DIRECTORY']['REGION']['ELEMENTS'] = $arRegions;
    }
}

$hlblock_inst = HL\HighloadBlockTable::getList(['filter' => ['=TABLE_NAME' => 'b_hlb_institutions']])->fetch();
if($hlblock_inst){
    $entity = HL\HighloadBlockTable::compileEntity($hlblock_inst);
    $entityClass = $entity->getDataClass();
    $resInstitute = $entityClass::getList([
        'select' => ['ID', 'UF_NAME', 'UF_ADDRESS', 'UF_REGION'],
        'order' => ['ID' => 'ASC'],
        'filter' => []
    ]);
    while($row = $resInstitute->fetch()) {
        $arInstitutes[$row['ID']]['ID'] = $row['ID'];
        $arInstitutes[$row['ID']]['NAME'] = $row['UF_NAME'];
        $arInstitutes[$row['ID']]['ADDRESS'] = $row['UF_ADDRESS'];
        $arInstitutes[$row['ID']]['REGION'] = $row['UF_REGION'];

    }

    if(isset($arInstitutes)){
        $arResult['DIRECTORY']['EXAM_PLACE']['TITLE'] = "Место проведения";
        $arResult['DIRECTORY']['EXAM_PLACE']['CODE'] = "EXAM_PLACE";
        $arResult['DIRECTORY']['EXAM_PLACE']['ELEMENTS'] = $arInstitutes;
    }
}

$hlblock_examlevel = HL\HighloadBlockTable::getList(['filter' => ['=TABLE_NAME' => 'b_hlb_exam_level']])->fetch();
if($hlblock_examlevel) {
    $entity = HL\HighloadBlockTable::compileEntity($hlblock_examlevel);
    $entityClass = $entity->getDataClass();
    $resExamLevel = $entityClass::getList([
        'select' => ['ID', 'UF_NAME', "UF_XML_ID"],
        'order' => ['ID' => 'ASC'],
        'filter' => []
    ]);
    while($row = $resExamLevel->fetch()) {
        $arExamLevel[$row['ID']]['ID'] = $row['ID'];
        $arExamLevel[$row['ID']]['XML_ID'] = $row['UF_XML_ID'];
        $arExamLevel[$row['ID']]['NAME'] = $row['UF_NAME'];
    }

    if(isset($arExamLevel)){
        $arResult['DIRECTORY']['EXAM_LEVEL']['TITLE'] = "Вариант экзамена";
        $arResult['DIRECTORY']['EXAM_LEVEL']['CODE'] = "EXAM_LEVEL";
        $arResult['DIRECTORY']['EXAM_LEVEL']['ELEMENTS'] = $arExamLevel;
    }
}

$filter = ["ID" => $USER->GetID()];
$rsUsers = CUser::GetList(($by="ID"), ($order="ASC"), $filter,
    ["FIELDS" => ["*"]]);
if ($arUser = $rsUsers->Fetch())
{
    if($arUser['NAME']){
        $userData['STUDENT_NAME'] = $arUser['NAME'];
        $arParams = ["replace_space" => " ", "replace_other" => "-", "change_case" => false];
        $translitName = Cutil::translit($arUser['NAME'],"ru", $arParams);

        $userData['STUDENT_NAME_LATIN'] = $translitName;
    }
    if($arUser['LAST_NAME']){
        $userData['STUDENT_SURNAME'] = $arUser['LAST_NAME'];
        $arParams = ["replace_space" => " ", "replace_other" => "-", "change_case" => false];
        $translitLastName = Cutil::translit($arUser['LAST_NAME'],"ru", $arParams);

        $userData['STUDENT_SURNAME_LATIN'] = $translitLastName;
    }

    $userData['STUDENT_PATRONYMIC'] = $arUser['SECOND_NAME'];
    $userData['STUDENT_BIRTHDAY'] = $arUser['PERSONAL_BIRTHDAY'];
    $userData['STUDENT_EMAIL'] = $arUser['EMAIL'];
    $userData['LOGIN'] = $arUser['LOGIN'];
    $userData['STUDENT_PHONE'] = $arUser['PERSONAL_PHONE'];
    $userData['WORK_PLACE'] = $arUser['WORK_COMPANY'];
    $userData['WORK_POSITION'] = $arUser['WORK_POSITION'];
    $userData['STUDENT_NATIONALITY'] = $arUser['UF_NATIONALITY'];
    $userData['INSTITUTE'] = $arUser['UF_STUDY'];
}
if(isset($userData)) {
    if ($userData['INSTITUTE']) {
        $hlblock = HL\HighloadBlockTable::getList(['filter' => ['=TABLE_NAME' => 'b_hlb_institutions']])->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $entityClass = $entity->getDataClass();
        $resInstitute = $entityClass::getList([
            'select' => ['ID', 'UF_NAME', 'UF_ADDRESS'],
            'order' => ['UF_XML_ID' => 'ASC'],
            'filter' => ['ID' => $userData['INSTITUTE']]
        ]);
        if ($row = $resInstitute->fetch()) {
            $userData['INSTITUTE_NAME'] = $row['UF_NAME'];
        }
    }

    $arResult['USER_DATA'] = $userData;
}

$this->IncludeComponentTemplate();
