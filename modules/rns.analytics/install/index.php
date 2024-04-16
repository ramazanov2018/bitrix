<?php

use Bitrix\Main\ArgumentException;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class rns_analytics extends \CModule
{
    const HL_LOG_NAME = 'RnsAnalyticsLog';
    const HL_LOG_TABLE_NAME = 'rns_analytics_log';
    const HL_DYNAMIC_URL_NAME = 'RnsAnalyticsDynamicUrl';
    const HL_DYNAMIC_URL_TABLE = 'rns_analytics_dynamic_url';

    /**
     * @var string
     */
    public $MODULE_ID = "rns.analytics";
    /**
     * @var string
     */
    public $MODULE_VERSION;
    /**
     * @var string
     */
    public $MODULE_VERSION_DATE;
    /**
     * @var string
     */
    public $MODULE_NAME;
    /**
     * @var string
     */
    public $MODULE_DESCRIPTION;

    /**
     * rns_analytics constructor.
     */
    function __construct()
    {
        $arModuleVersion = [];

        include(__DIR__ . '/version.php');

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

        $this->MODULE_NAME = "Аналитика";
        $this->MODULE_DESCRIPTION = "Аналитика посещений страниц портала";
        $this->PARTNER_NAME = 'RuNetSoft';
        $this->PARTNER_URI = 'http://www.rns-soft.ru';
    }

    /**
     * @return bool|void
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    function DoInstall()
    {
        RegisterModule($this->MODULE_ID);
        if (!Loader::includeModule($this->MODULE_ID)) {
            return false;
        }

        $this->InstallFiles();
        $this->InstallEvents();
        $this->createLogHL();
        $this->createDynamicUrlHL();
        $this->AddMenuItem();

        BXClearCache(true, "/");

    }

    /**
     * @return bool|void
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    function DoUninstall()
    {
        if (!Loader::includeModule($this->MODULE_ID)) {
            return false;
        }
        global $APPLICATION, $step;

        $step = intval($step);
        if ($step < 2) {
            $APPLICATION->includeAdminFile(
                getMessage('RNSANALYTICS_UNINSTALL_TITLE'),
                __DIR__ . '/unstep1.php'
            );
        } elseif ($step == 2) {
            $this->UnInstallFiles();
            $this->UnInstallEvents();
            if ($_REQUEST['savedata'] !== 'Y') {
                $this->deleteLogHL();
                $this->deleteDynamicUrlHL();
            }
            UnRegisterModule($this->MODULE_ID);
            $APPLICATION->includeAdminFile(
                getMessage('RNSANALYTICS_UNINSTALL_TITLE'),
                __DIR__ . '/unstep2.php'
            );
        }
        BXClearCache(true, "/");
    }

    /**
     * @return bool|void
     */
    public function InstallEvents()
    {
        EventManager::getInstance()->registerEventHandler(
            "main",
            "OnEpilog",
            $this->MODULE_ID,
            "Rns\Analytics\Heatmap",
            "onEpilogAction"
        );
        EventManager::getInstance()->registerEventHandler(
            "main",
            "OnEventLogGetAuditTypes",
            $this->MODULE_ID,
            "Rns\Analytics\Heatmap",
            "onEventLogGetAuditTypesAction"
        );
        EventManager::getInstance()->registerEventHandler(
            "main",
            "OnProlog",
            $this->MODULE_ID,
            "Rns\Analytics\Heatmap",
            "CheckMenuItemShow"
        );

        return true;
    }

    /**
     * @return bool|void
     */
    public function UnInstallEvents()
    {
        EventManager::getInstance()->unRegisterEventHandler(
            "main",
            "OnEpilog",
            $this->MODULE_ID,
            "Rns\Analytics\Heatmap",
            "onEpilogAction"
        );
        EventManager::getInstance()->unRegisterEventHandler(
            "main",
            "OnEventLogGetAuditTypes",
            $this->MODULE_ID,
            "Rns\Analytics\Heatmap",
            "onEventLogGetAuditTypesAction"
        );
        EventManager::getInstance()->unRegisterEventHandler(
            "main",
            "OnProlog",
            $this->MODULE_ID,
            "Rns\Analytics\Heatmap",
            "CheckMenuItemShow"
        );
        return true;
    }

    /**
     * @return bool|void
     */
    public function InstallFiles()
    {
        CopyDirFiles(__DIR__ . '/js/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/js', true, true);
        CopyDirFiles(__DIR__ . '/components/', $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components", true, true);
        CopyDirFiles(__DIR__ . '/public/', $_SERVER["DOCUMENT_ROOT"] . "/timeman", true, true);
        return true;
    }

    /**
     * @return bool|void
     */
    public function UnInstallFiles()
    {
        DeleteDirFilesEx('/bitrix/js/rns/analytics/');
        DeleteDirFilesEx('/timeman/analytics/');
        DeleteDirFilesEx('/bitrix/components/rns/analyticsview/');
        return true;
    }

    /**
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function createLogHL()
    {
        $settingsTable = HL\HighloadBlockTable::getList([
            'filter' => [
                'NAME' => self::HL_LOG_NAME
            ]
        ]);
        if ($hldata = $settingsTable->fetch()) {
            return;
        }
        $result = HL\HighloadBlockTable::add([
            'NAME' => self::HL_LOG_NAME,
            'TABLE_NAME' => self::HL_LOG_TABLE_NAME,
        ]);
        $hlId = $result->getId();
        HL\HighloadBlockLangTable::add([
            'ID' => $hlId,
            'LID' => 'ru',
            'NAME' => Loc::getMessage('RNSANALYTICS_LOG_HL_NAME')
        ]);
        $UFObject = 'HLBLOCK_' . $hlId;
        $arCartFields = [
            'UF_RNS_USER_ID' => [
                'ENTITY_ID' => $UFObject,
                'FIELD_NAME' => 'UF_RNS_USER_ID',
                'USER_TYPE_ID' => 'integer',
                'MANDATORY' => 'Y',
                "EDIT_FORM_LABEL" => ['ru' => 'ИД Пользователя', 'en' => 'User ID'],
                "LIST_COLUMN_LABEL" => ['ru' => 'ИД Пользователя', 'en' => 'User ID'],
                "LIST_FILTER_LABEL" => ['ru' => 'ИД Пользователя', 'en' => 'User ID']
            ],
            'UF_RNS_EVENT_NAME' => [
                'ENTITY_ID' => $UFObject,
                'FIELD_NAME' => 'UF_RNS_EVENT_NAME',
                'USER_TYPE_ID' => 'string',
                'MANDATORY' => 'Y',
                "EDIT_FORM_LABEL" => ['ru' => 'Название события', 'en' => 'Event name'],
                "LIST_COLUMN_LABEL" => ['ru' => 'Название события', 'en' => 'Event name'],
                "LIST_FILTER_LABEL" => ['ru' => 'Название события', 'en' => 'Event name']
            ],
            'UF_RNS_URL' => [
                'ENTITY_ID' => $UFObject,
                'FIELD_NAME' => 'UF_RNS_URL',
                'USER_TYPE_ID' => 'string',
                'MANDATORY' => 'Y',
                "EDIT_FORM_LABEL" => ['ru' => 'URL страницы', 'en' => 'Page url'],
                "LIST_COLUMN_LABEL" => ['ru' => 'URL страницы', 'en' => 'Page url'],
                "LIST_FILTER_LABEL" => ['ru' => 'URL страницы', 'en' => 'Page url']
            ],
            'UF_RNS_DATE' => [
                'ENTITY_ID' => $UFObject,
                'FIELD_NAME' => 'UF_RNS_DATE',
                'USER_TYPE_ID' => 'datetime',
                'MANDATORY' => 'Y',
                "EDIT_FORM_LABEL" => ['ru' => 'Дата просмотра', 'en' => 'Date'],
                "LIST_COLUMN_LABEL" => ['ru' => 'Дата просмотра', 'en' => 'Date'],
                "LIST_FILTER_LABEL" => ['ru' => 'Дата просмотра', 'en' => 'Date']
            ],
            'UF_RNS_RESULT' => [
                'ENTITY_ID' => $UFObject,
                'FIELD_NAME' => 'UF_RNS_RESULT',
                'USER_TYPE_ID' => 'string',
                'MANDATORY' => 'Y',
                "EDIT_FORM_LABEL" => ['ru' => 'Результат', 'en' => 'Result'],
                "LIST_COLUMN_LABEL" => ['ru' => 'Результат', 'en' => 'Result'],
                "LIST_FILTER_LABEL" => ['ru' => 'Результат', 'en' => 'Result']
            ]
        ];
        foreach ($arCartFields as $arCartField) {
            $obUserField = new \CUserTypeEntity;
            $obUserField->Add($arCartField);
        }
    }

    /**
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function deleteLogHL()
    {
        $settingsTable = HL\HighloadBlockTable::getList([
            'filter' => [
                'NAME' => self::HL_LOG_NAME
            ]
        ]);
        if ($hldata = $settingsTable->fetch()) {
            HL\HighloadBlockTable::delete($hldata['ID']);
        }
    }

    public static function createDynamicUrlHL()
    {
        $settingsTable = HL\HighloadBlockTable::getList([
            'filter' => [
                'NAME' => self::HL_DYNAMIC_URL_NAME
            ]
        ]);
        if ($hldata = $settingsTable->fetch()) {
            return;
        }
        $arLangs = [
            'ru' => Loc::getMessage("RNS_HL_DYNAMIC_URL_NAME"),
        ];
        $result = HL\HighloadBlockTable::add([
            'NAME' => self::HL_DYNAMIC_URL_NAME,
            'TABLE_NAME' => self::HL_DYNAMIC_URL_TABLE,
        ]);

        if ($result->isSuccess()) {
            $id = $result->getId();
            foreach($arLangs as $lang_key => $lang_val){
                HL\HighloadBlockLangTable::add([
                    'ID' => $id,
                    'LID' => $lang_key,
                    'NAME' => $lang_val
                ]);
            }
            $UFObject = 'HLBLOCK_' . $id;

            $arCartFields = [
                'UF_RNS_DYNAMIC_URL' => [
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_RNS_DYNAMIC_URL',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => 'Y',
                    "EDIT_FORM_LABEL" => ['ru' => 'URL динамической страницы', 'en' => 'Dynamic url'],
                    "LIST_COLUMN_LABEL" => ['ru' => 'URL динамической страницы', 'en' => 'Dynamic url'],
                    "LIST_FILTER_LABEL" => ['ru' => 'URL динамической страницы', 'en' => 'Dynamic url']
                ],
                'UF_RNS_DYNAMIC_URL_PARAM' => [
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_RNS_DYNAMIC_URL_PARAM',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => 'Y',
                    "EDIT_FORM_LABEL" => ['ru' => 'Параметры динамической страницы', 'en' => 'Dynamic url param'],
                    "LIST_COLUMN_LABEL" => ['ru' => 'Параметры динамической страницы', 'en' => 'Dynamic url param'],
                    "LIST_FILTER_LABEL" => ['ru' => 'Параметры динамической страницы', 'en' => 'Dynamic url param']
                ],
                'UF_RNS_DYNAMIC_URL_ACTIVE' => [
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_RNS_DYNAMIC_URL_ACTIVE',
                    'USER_TYPE_ID' => 'boolean',
                    'XML_ID' => 'UF_RNS_DYNAMIC_URL_ACTIVE',
                    'SORT' => '200',
                    'MULTIPLE' => 'N',
                    'MANDATORY' => 'N',
                    'SHOW_FILTER' => 'I',
                    'SHOW_IN_LIST' => 'Y',
                    'EDIT_IN_LIST' => 'Y',
                    'IS_SEARCHABLE' => 'N',
                    'SETTINGS' =>
                        [
                            'DEFAULT_VALUE' => 1,
                            'DISPLAY' => 'CHECKBOX',
                            'LABEL' =>
                                [
                                    0 => 'Нет',
                                    1 => 'Да',
                                ],
                            'LABEL_CHECKBOX' => 'Да',
                        ],
                    'EDIT_FORM_LABEL' => ['en' => 'Active', 'ru' => 'Активность',],
                    'LIST_COLUMN_LABEL' => ['en' => 'Active', 'ru' => 'Активность',],
                    'LIST_FILTER_LABEL' => ['en' => 'Active', 'ru' => 'Активность',],
                ],
            ];
            foreach ($arCartFields as $arCartField) {
                $obUserField = new \CUserTypeEntity;
                $obUserField->Add($arCartField);
            }
        }
    }

    public static function deleteDynamicUrlHL()
    {
        $settingsTable = HL\HighloadBlockTable::getList([
            'filter' => [
                'NAME' => self::HL_DYNAMIC_URL_NAME
            ]
        ]);
        if ($hldata = $settingsTable->fetch()) {
            HL\HighloadBlockTable::delete($hldata['ID']);
        }
    }

    public function AddMenuItem($pos = -1)
    {
        require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/wizard.php");
        require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/wizard_sol/utils.php");
        $siteID = WizardServices::GetCurrentSiteID();

        $menuItem = 	[
            Loc::getMessage('TOP_MENU_ANALYTICS'),
            '/timeman/analytics/',
            [],
            [],
            "\$_SESSION['menuItemShow'] == 'Y'"

        ];
        $menuFile = '/timeman/.sub.menu_ext.php';

        if (CModule::IncludeModule("fileman")) {
            $arResult = CFileMan::GetMenuArray(\Bitrix\Main\Application::getDocumentRoot().$menuFile);
            $arMenuItems = $arResult["aMenuLinks"];
            $menuTemplate = $arResult["sMenuTemplate"];

            $bFound = false;

            foreach($arMenuItems as $k=>$item){
                if(substr($arMenuItems[$k][1], 0,1) != '/' && strlen($arMenuItems[$k][1]) > 1){
                    $arMenuItems[$k][1] =  '/'.$arMenuItems[$k][1];
                }
            }
            foreach($arMenuItems as $item) {
                if($item[1] == $menuItem[1]) {
                    $bFound = true;
                    break;
                }
            }

            if(!$bFound) {
                if($pos < 0 || $pos >= count($arMenuItems))
                    $arMenuItems[] = $menuItem;
                else {
                    for($i = count($arMenuItems); $i > $pos; $i--)
                        $arMenuItems[$i] = $arMenuItems[$i - 1];

                    $arMenuItems[$pos] = $menuItem;
                }

                CFileMan::SaveMenu([$siteID, $menuFile], $arMenuItems, $menuTemplate);
            }
        }

    }
}

