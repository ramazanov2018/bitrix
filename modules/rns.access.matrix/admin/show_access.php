<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");// первый общий пролог
require_once($_SERVER["DOCUMENT_ROOT"] . "/local/modules/rns.access.matrix/prolog.php"); // пролог модуля
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");


use Bitrix\Main\Localization\Loc,
    Rns\AccessMatrix\Access,
    Bitrix\Highloadblock as HL,
    Bitrix\Main\Loader;
use Rns\AccessMatrix\HLBlockAccessMatrix;

?>
    <style>
        div.access_filter {
            margin: 30px 70px;
        }
    </style>
<?php
$APPLICATION->SetTitle('Сохраненные права');

\Bitrix\Main\UI\Extension::load("ui.buttons");
Loader::includeModule("highloadblock");

if (!Loader::includeModule(ADMIN_MODULE_NAME)) {
    return;
}
/**
 * @var CMain
 */
global $APPLICATION;
\Bitrix\Main\UI\Extension::load('ui.entity-selector');
if ($APPLICATION->GetGroupRight(ADMIN_MODULE_NAME) < "R") {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

$filterParam = [];
$request = \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();
if ($request->getRequestMethod() == "GET" && $request['filterBtn'] == 'Y') {
    $form = trim($request['access_filter']);
    if ($form != "")
        $filterParam = explode(';', $form);
}

$aTabs = [];
if ($request['access_code'] == Access::OPTION_PROJECTS_FIELD)
    $aTabs[] = ["DIV" => "edit1", "TAB" => 'Права на проекты', "ICON" => "blog_path", "TITLE" => 'Права на проекты'];
elseif ($request['access_code'] == Access::OPTION_TASKS_FIELD)
    $aTabs[] = ["DIV" => "edit1", "TAB" => 'Права на задачи', "ICON" => "blog_path", "TITLE" => 'Права на задачи'];
elseif ($request['access_code'] == Access::OPTION_RESOLUTION_FIELD)
    $aTabs[] = ["DIV" => "edit1", "TAB" => 'Права на поручения', "ICON" => "blog_path", "TITLE" => 'Права на поручения'];

$tabControl = new CAdminTabControl("tabControl", $aTabs);
Access::ShowFilter($filterParam, $request['access_code']);
?>
    <div class="adm-detail-toolbar"><span style="position:absolute;"></span>
        <a href="/bitrix/admin/access_matrix.php?lang=<?= LANGUAGE_ID ?>" class="adm-detail-toolbar-btn" title=""
           id="btn_list"><span class="adm-detail-toolbar-btn-l"></span><span class="adm-detail-toolbar-btn-text">Матрица прав</span><span
                    class="adm-detail-toolbar-btn-r"></span></a>
    </div>
<?

if (!empty($filterParam)) {
    foreach ($filterParam as $key => $param) {
        if ($request['access_code'] == Access::OPTION_PROJECTS_FIELD)
            $filterParam[$key] = $request['access_code'] . "_" . explode('_', $param)[1];
        else
            $filterParam[$key] = $request['access_code'] . "_" . str_replace("R", "T", $param,);
    }
} else {
    $filterParam = $request['access_code'];
}

$nav = new \Bitrix\Main\UI\PageNavigation("navAccess");
$nav->allowAllRecords(false)
    ->setPageSize(15)
    ->initFromUri();

$arAccess = [];
$param = [
    'filter' => [
        '%UF_ACCESS_GROUP_CODE' => $filterParam,
    ],
    'select' => ['*'],
    "offset" => $nav->getOffset(),
    "count_total" => true,
    "limit" => $nav->getLimit(),
    'order' => ['UF_ACCESS_GROUP_CODE' => 'asc']
];
$hlblock = HL\HighloadBlockTable::getList(['filter' => ['NAME' => HLBlockAccessMatrix::$HLBlockName]])->fetch();
if ($hlblock) {
    $Release = HL\HighloadBlockTable::compileEntity($hlblock);
    $Release_data_class = $Release->getDataClass();
    $r = $Release_data_class::getList($param);
    while ($arRes = $r->fetch()) {
        $arAccess[] = $arRes;
    }
    $nav->setRecordCount($r->getCount());

}

$tabControl->Begin(); ?>
<? $tabControl->BeginNextTab(); ?>

<?
//Постраничная навигация
$APPLICATION->IncludeComponent(
    "bitrix:main.pagenavigation",
    "",
    [
        "NAV_OBJECT" => $nav,
        "SEF_MODE" => "N",
    ],
    false
);

if ($request['access_code'] == Access::OPTION_PROJECTS_FIELD)
    Access::ShowSavedAccessProject($arAccess);//Права на проекты
elseif ($request['access_code'] == Access::OPTION_TASKS_FIELD)
    Access::ShowSavedAccessTasks($arAccess);//Права на задачи
elseif ($request['access_code'] == Access::OPTION_RESOLUTION_FIELD)
    Access::ShowSavedAccessResolutions($arAccess);//Права на поручения
?>
<? $tabControl->EndTab(); ?>
<? $tabControl->End();

//Постраничная навигация
$APPLICATION->IncludeComponent(
    "bitrix:main.pagenavigation",
    "",
    [
        "NAV_OBJECT" => $nav,
        "SEF_MODE" => "N",
    ],
    false
);
?>

<? require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin.php"); ?>