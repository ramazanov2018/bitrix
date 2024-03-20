<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();
$file = trim(preg_replace("'[\\\\/]+'", "/", (dirname(__FILE__)."/lang/".LANGUAGE_ID."/result_modifier.php")));
__IncludeLang($file);

function cmp($a, $b) {
    $cmpRez = strcmp($a["DEP"], $b["DEP"]);
    if ($cmpRez == 0) {
        if ($a["DEP_LEVEL"] < $b["DEP_LEVEL"])
            return -1;
        elseif ($a["DEP_LEVEL"] > $b["DEP_LEVEL"])
            return 1;
        else
            return strcmp($a["VALUE"], $b["VALUE"]);
    }
    else
        return $cmpRez;
}

if (!CModule::IncludeModule("iblock"))
    return;
if (count($arParams['~FIELDS_ORDER']) > 1) {
    $newArr = array();
    $i = 0;
    foreach ($arResult["PROPERTY_LIST"] as $propertyID) {
        $keyA = array_search($propertyID, $arParams['~FIELDS_ORDER']);
        if ($keyA !== false)
            $newArr[$keyA] = $propertyID;
        else
            $newArr[count($arParams['~FIELDS_ORDER']) + $i] = $propertyID;
        $i++;
        if ($arResult["PROPERTY_LIST_FULL"][$propertyID]['USER_TYPE'] == "UserID") {
            $arResult["PROPERTY_LIST_FULL"][$propertyID]['PROPERTY_TYPE'] = 'L';
            $arResult["PROPERTY_LIST_FULL"][$propertyID]['~PROPERTY_TYPE'] = 'L';
            $arResult["PROPERTY_LIST_FULL"][$propertyID]['ENUM'] = array();
            if ($arResult["PROPERTY_LIST_FULL"][$propertyID]['IS_REQUIRED'] == "N")
                $arResult["PROPERTY_LIST_FULL"][$propertyID]['ENUM'][0] = array('ID' => 0, "VALUE" => GetMessage("NULL_VALUE"));
            $rsGroups = CGroup::GetList(($by = "c_sort"), ($order = "desc"), array("STRING_ID" => $arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE'])); // выбираем группы
            if ($arGroups = $rsGroups->GetNext()) {
                $rsUsers = CUser::GetList(
                    ($by = "LAST_NAME"),
                    ($order = "asc"),
                    array(
                        "GROUPS_ID" => Array($arGroups['ID']),
                    ),
                    array(
                        "SELECT" =>
                        array("UF_DEPARTMENT")
                    )
                ); // выбираем пользователей
                while ($arUser = $rsUsers->GetNext()) {
                    $arResult["PROPERTY_LIST_FULL"][$propertyID]['ENUM'][$arUser['ID']] = array('ID' => $arUser['ID'], "VALUE" => $arUser['LAST_NAME']." ".$arUser['NAME']." ".$arUser['SECOND_NAME']);
                    if ($_GET[$arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE']] == $arUser['ID'])
                        $arResult["PROPERTY_LIST_FULL"][$propertyID]['ENUM'][$arUser['ID']]["DEF"] = "Y";
                    if ($arUser["UF_DEPARTMENT"] && $arParams['~IBLOCK_ID_DEPARTMENT']) {
                        if (!$arResult["PROPERTY_LIST_FULL"][$propertyID]['ENUM']["D".$arUser["UF_DEPARTMENT"]]) {
                            $resSect = CIBlockSection::GetByID($arUser["UF_DEPARTMENT"]);
                            if ($ar_resSect = $resSect->GetNext()) {
                                $arResult["PROPERTY_LIST_FULL"][$propertyID]['ENUM']["D".$arUser["UF_DEPARTMENT"]] = array('ID' => $arUser["UF_DEPARTMENT"], "VALUE" => $ar_resSect['NAME'], "DISABLED" => "Y", "DEP_LEVEL" => 1, 'DEP' => $ar_resSect['NAME']);
                            }
                        }
                        $arResult["PROPERTY_LIST_FULL"][$propertyID]['ENUM'][$arUser['ID']]['DEP'] = $arResult["PROPERTY_LIST_FULL"][$propertyID]['ENUM']["D".$arUser["UF_DEPARTMENT"]]['VALUE'];
                        $arResult["PROPERTY_LIST_FULL"][$propertyID]['ENUM'][$arUser['ID']]['DEP_LEVEL'] = 2;
                        $arResult["PROPERTY_LIST_FULL"][$propertyID]['ENUM'][$arUser['ID']]['VALUE'] = "&nbsp;&nbsp;".$arResult["PROPERTY_LIST_FULL"][$propertyID]['ENUM'][$arUser['ID']]['VALUE'];
                    }
                }
                usort($arResult["PROPERTY_LIST_FULL"][$propertyID]['ENUM'], "cmp");
            }
        }
    }
    ksort($newArr);
    $arResult["PROPERTY_LIST"] = $newArr;
}

foreach ($arResult["PROPERTY_LIST_FULL"] as $key => $value){
    switch ($value["CODE"]){
        case "TCSO_TYPE":
            $arResult["PROPERTY_LIST_FULL"][$key]["NAME"] = "Наименование ТЦСО";
            break;
        case "PATIENT_LAST_NAME":
            $arResult["PROPERTY_LIST_FULL"][$key]["NAME"] = "Фамилия контактного лица";
            break;
        case "PATIENT_NAME":
            $arResult["PROPERTY_LIST_FULL"][$key]["NAME"] = "Имя контактного лица";
            break;
        case "PATIENT_SECOND_NAME":
            $arResult["PROPERTY_LIST_FULL"][$key]["NAME"] = "Отчество контактного лица";
            break;
        case "NOTIFY_PATIENT":
            $arResult["PROPERTY_LIST_FULL"][$key]["NAME"] = "Уведомить о записи по СМС";
            break;
        case "REGISTRY_TIME":
            $arResult["REGISTER_TIME_PROP_ID"] =  $key;
            break;
        case "REGISTRY_DATE":
            $arResult["REGISTRY_DATE_PROP_ID"] =  $key;
            break;
    }
}

if (isset($arResult["ELEMENT_PROPERTIES"][$arResult["REGISTRY_DATE_PROP_ID"]][0]) && isset($arResult["ELEMENT_PROPERTIES"][$arResult["REGISTER_TIME_PROP_ID"]][0])){
    $arResult["DATE_TIME_REGISTER"] = $arResult["ELEMENT_PROPERTIES"][$arResult["REGISTRY_DATE_PROP_ID"]][0]["VALUE"] . " ".trim($arResult["ELEMENT_PROPERTIES"][$arResult["REGISTER_TIME_PROP_ID"]][0]["VALUE"]) . ":00";
}
?>