<?require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
$userFieldDispatcher = \Bitrix\Main\UserField\Dispatcher::instance();
$params = $_REQUEST;

function returnResult($result){
    header('Content-Type: application/json');
    echo \CUtil::PhpToJSObject($result);
    die();
}

\CModule::IncludeModule('iblock');



if (trim($params['sessid']) == bitrix_sessid()){
    $PROP = array();
    foreach ($params['SEANCE']['FORMAT'] as $elId => $arProps){
        foreach ($arProps as $propId => $propValue){
            $PROP[$propId] = $propValue;
        }
        \CIBlockElement::SetPropertyValuesEx($elId, SEANCE_IBLOCK_ID, $PROP);
    }
    returnResult(['status'=>'success']);
}else{
    returnResult(['status'=>'error', 'error' => 'некорректный запрос']);
}


