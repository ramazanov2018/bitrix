<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use RNS\Integrations\Migrations\HelperInstaller;

Loc::loadMessages(__FILE__);

if (class_exists('rns_integrations')) {
    return;
}

class rns_integrations extends CModule
{
    /** @var string */
    public $MODULE_ID;

    /** @var string */
    public $MODULE_VERSION;

    /** @var string */
    public $MODULE_VERSION_DATE;

    /** @var string */
    public $MODULE_NAME;

    /** @var string */
    public $MODULE_DESCRIPTION;

    /** @var string */
    public $PARTNER_NAME;

    /** @var string */
    public $PARTNER_URI;

    /** @var string */
    public $MODULE_GROUP_RIGHTS = 'Y';

    const HIGHLOAD_BLOCKS = ['TaskSource', 'ExternalEntity', 'ExternalEntityProperty', 'ExternalEntityStatus', 'ExportLog', 'IntegrationSettings', 'ImportLog'];
    const USER_FIELDS = [
        'UF_TASK_SOURCE' => 'TASKS_TASK',
        'UF_EXTERNAL_ID' => 'TASKS_TASK',
        'UF_CALENDAR_EVENT_ID' => 'TASKS_TASK',
    ];

    public function __construct()
    {
        $this->MODULE_ID = 'rns.integrations';
        $this->MODULE_VERSION = '2.0.0';
        $this->MODULE_VERSION_DATE = '2023-01-31 13:00:00';
        $this->MODULE_NAME = Loc::getMessage('INTEGRATIONS_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('INTEGRATIONS_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = 'RuNetSoft';
        $this->PARTNER_URI = 'http://www.rns-soft.ru';
    }

    /**
     * @return bool|void
     */
    public function doInstall()
    {
        global $APPLICATION, $USER, $step;
        if ($USER->IsAdmin()) {
            $step = intval($step);
            if ($step < 2) {
                $APPLICATION->IncludeAdminFile(GetMessage('INTEGRATIONS_MODULE_INSTALL_TITLE'), __DIR__ . "/step1.php");
            } elseif ($step == 2) {
                try {
                    $this->installDB();
                    ModuleManager::registerModule($this->MODULE_ID);
                    if (Loader::IncludeModule($this->MODULE_ID)){
                        $HelperInstaller = new HelperInstaller();
                        if ($HelperInstaller) {
                            try {
                                $HelperInstaller->initHighloadBlockData();
                            } catch (Exception $e) {
                                global $APPLICATION;
                                $this->unInstallDB();
                                $HelperInstaller->uninstallHighloadBlocks(self::HIGHLOAD_BLOCKS);
                                ModuleManager::unRegisterModule($this->MODULE_ID);
                                $APPLICATION->ThrowException($e->getMessage());
                                return false;
                            }
                            $HelperInstaller->initUserFieldsData();
                        }
                    }

                    $this->installFiles();
                    $this->installEvents();

                    RegisterModuleDependences('main', 'OnPageStart', $this->MODULE_ID);

                    COption::SetOptionString($this->MODULE_ID, 'active', 'Y');

                    return true;
                } catch(\Exception $ex) {
                    CAdminMessage::ShowMessage(['MESSAGE' => Loc::getMessage('INTEGRATION_MODULE_INSTALL_ERROR'), 'TYPE' => 'ERROR']);
                    return false;
                }
            }
        }
        return false;
    }

    public function installFiles($arParams = [])
    {
        CopyDirFiles(__DIR__ . '/admin/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin', true, true);
        CopyDirFiles(__DIR__ . '/components/', $_SERVER["DOCUMENT_ROOT"] . "/local/components", true, true);
        return true;
    }

    public function installDB()
    {
        global $APPLICATION;
        global $DB;
        global $errors;

        if (!$DB->Query("SELECT 'x' FROM integration_external_system", true)) {
            $errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . "/local/modules/rns.integrations/install/db/" . mb_strtolower($DB->type) . "/install.sql");
        }

        if (!empty($errors)) {
            $APPLICATION->ThrowException(implode('. ', $errors));
            return false;
        }

        $arCount = $DB->Query("select count(id) as CNT from integration_exchange_type", true)->Fetch();
        if (is_array($arCount) && isset($arCount['CNT']) && intval($arCount['CNT']) <= 0) {
            $types = [
              'api' => Loc::getMessage('INTEGRATIONS_MODULE_TYPE_REST_API'),
              'database' => Loc::getMessage('INTEGRATIONS_MODULE_TYPE_DATABASE'),
              'files' => Loc::getMessage('INTEGRATIONS_MODULE_TYPE_FILES')
            ];
            $id = 1;
            foreach ($types as $key => $name) {
                $DB->Query("insert into integration_exchange_type (id, name, code) values({$id}, '{$name}', '{$key}')", true);
                $id++;
            }
        }

        $systemsToInstall = $this->getSystemsToInstall();

        $systems = [
          'jira' => Loc::getMessage('INTEGRATIONS_MODULE_SYSTEM_JIRA'),
          'ms_project' => Loc::getMessage('INTEGRATIONS_MODULE_SYSTEM_MS_PROJECT'),
          'sap' => Loc::getMessage('INTEGRATIONS_MODULE_SYSTEM_SAP'),
        ];
        $id = 1;
        foreach ($systems as $key => $name) {
            if (!in_array($key, $systemsToInstall)) {
                continue;
            }
            $arCount = $DB->Query(
              "select count(id) as CNT from integration_external_system WHERE name = '{$name}'",
              true)
              ->Fetch();
            if (is_array($arCount) && isset($arCount['CNT']) && intval($arCount['CNT']) <= 0) {
                $DB->Query("insert into integration_external_system (id, name, code, created_by, modified_by) values({$id}, '{$name}', '{$key}', 1, 1)", true);
                $id++;
            }
        }

        if ($_REQUEST['option_jira'] == 'Y') {
            $name = Loc::getMessage('INTEGRATIONS_MODULE_OPTIONS_JIRA');
            $arCount = $DB->Query("select count(id) as CNT from integration_options WHERE name='{$name}'", true)->Fetch();
            if (is_array($arCount) && isset($arCount['CNT']) && intval($arCount['CNT']) <= 0) {
                $DB->Query("insert into integration_options (system_id, exchange_type_id, direction, name, schedule, created_by, modified_by, options) values(1, 2, 0, '{$name}', 900, 1, 1, '{\"taskLevel\":3}')", true);
            }
        }
        if ($_REQUEST['option_sap'] == 'Y') {
            $name = Loc::getMessage('INTEGRATIONS_MODULE_OPTIONS_SAP');
            $arCount = $DB->Query("select count(id) as CNT from integration_options WHERE name='{$name}'", true)->Fetch();
            if (is_array($arCount) && isset($arCount['CNT']) && intval($arCount['CNT']) <= 0) {
                $DB->Query("insert into integration_options (system_id, exchange_type_id, direction, name, schedule, created_by, modified_by, options) values(3, 2, 0, '{$name}', 900, 1, 1, '{\"taskLevel\":3}')", true);
            }
        }
        if ($_REQUEST['option_ms_project'] == 'Y') {
            $name = Loc::getMessage('INTEGRATIONS_MODULE_OPTIONS_MS_PROJECT');
            $arCount = $DB->Query("select count(id) as CNT from integration_options WHERE name='{$name}'", true)->Fetch();
            if (is_array($arCount) && isset($arCount['CNT']) && intval($arCount['CNT']) <= 0) {
                $DB->Query("insert into integration_options (system_id, exchange_type_id, direction, name, created_by, modified_by, options) values(2, 3, 0, '{$name}', 1, 1, '{\"taskLevel\":3}')", true);
            }
        }

        return true;
    }

    private function getSystemsToInstall()
    {
        $result = [];
        if ($_REQUEST['option_jira'] == 'Y') {
            $result[] = 'jira';
        }
        if ($_REQUEST['option_sap'] == 'Y') {
            $result[] = 'sap';
        }
        if ($_REQUEST['option_ms_project'] == 'Y') {
            $result[] = 'ms_project';
        }
        return $result;
    }

    public function installEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler(
          "main",
          "OnEpilog",
          $this->MODULE_ID,
          "RNS\Integrations\Handlers\Epilog",
          "initializeJs"
        );

        return true;
    }

    /**
     * @throws LoaderException
     */
    public function doUninstall()
    {
        global $APPLICATION, $USER, $step;
        if ($USER->IsAdmin()) {
            $step = intval($step);
            if ($step < 2) {
                $APPLICATION->IncludeAdminFile(GetMessage('INTEGRATIONS_MODULE_UNINSTALL') , __DIR__ . '/unstep1.php');
            } elseif ($step == 2) {
                $this->uninstallEvents();

                if ($_REQUEST['savedata'] != 'Y' || !isset($_REQUEST['savedata'])) {
                    $this->unInstallDB();
                }
                if ($_REQUEST['savelog'] != 'Y' || !isset($_REQUEST['savelog'])) {
                    $this->unInstallMigrations();
                }

                $this->uninstallFiles();

                UnRegisterModuleDependences('main', 'OnPageStart', $this->MODULE_ID);
                ModuleManager::unregisterModule($this->MODULE_ID);
                $APPLICATION->IncludeAdminFile(GetMessage('INTEGRATIONS_MODULE_UNINSTALL') , __DIR__ . '/unstep2.php');

                COption::RemoveOption($this->MODULE_ID, 'active');
            }
        }
    }

    public function uninstallFiles(array $arParams = [])
    {
        DeleteDirFiles(__DIR__ . '/admin/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/');
        DeleteDirFilesEx('local/components/bitrix/tasks.import/');
        return true;
    }

    /**
     * @return bool|void
     */
    public function unInstallDB()
    {
        global $APPLICATION, $DB, $errors;

        $errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/local/modules/rns.integrations/install/db/".mb_strtolower($DB->type)."/uninstall.sql");

        if (!empty($errors)) {
            $APPLICATION->ThrowException(implode("", $errors));
            return false;
        }

        return true;
    }

    public function uninstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
          "main",
          "OnEpilog",
          $this->MODULE_ID,
          "RNS\Integrations\Handlers\Epilog",
          "initializeJs"
        );

        return true;
    }

    /**
     * Метод откатывания миграций, удаление элементов модуля
     * @return bool
     * @throws LoaderException
     */
    function unInstallMigrations(): bool
    {
        $HelperInstaller = new HelperInstaller();
        if($HelperInstaller) {
            try {
                $HelperInstaller->uninstallHighloadBlocks(self::HIGHLOAD_BLOCKS);
                $HelperInstaller->uninstallUserTypeEntity(self::USER_FIELDS);
                return true;
            } catch (Exception $e) {
                global $APPLICATION;
                $APPLICATION->ThrowException($e->getMessage());
                return false;
            }
        }
        return false;
    }
}
