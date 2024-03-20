<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?php
use Bitrix\Main\Loader;
use  Bitrix\Main\Context;
global $USER;

$request = Context::getCurrent()->getRequest();
$params = $request->get('regData');

if (!Loader::includeModule("iblock") || empty($params)) return false;

foreach($params as $regDataElem){
    if($regDataElem['value']){
        $regDataProps[$regDataElem['name']] = $regDataElem['value'];
    }
}
//свойство тек. пользователь
$regDataProps['USER_LINK'] = $USER->GetID();

$arFields = [
    "IBLOCK_ID"         => NicaHelpers::getIdByCode("exam_registration"),
    "NAME"              => $regDataProps['STUDENT_SURNAME'].' '.$regDataProps['STUDENT_NAME'],
    "PROPERTY_VALUES"   => $regDataProps, // Передаем массив значений свойств
];

$regForm = new CIBlockElement();
if($newId = $regForm->Add($arFields)) {?>
    <p>Заявка успешно добавлена!</p>
<?php } else {?>
    <p>Ошибка добавления заявки!</p>
<?php }
