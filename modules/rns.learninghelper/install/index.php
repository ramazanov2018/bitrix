<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class rns_learninghelper extends CModule
{
    public $MODULE_ID = "rns.learninghelper";
    public $error = false;

    public $MODULE_NAME;
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;

    public $MODULE_GROUP_RIGHTS = "Y";

    public function __construct()
    {
        $arModuleVersion = [];
        include(__DIR__ . "/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

        $this->MODULE_NAME = Loc::getMessage("LEARNING_HELPER_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("LEARNING_HELPER_MODULE_DESCRIPTION");
        $this->PARTNER_NAME = Loc::getMessage("LEARNING_HELPER_MODULE_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("LEARNING_HELPER_MODULE_PARTNER_URL");
    }

    public function DoInstall()
    {
        global $APPLICATION;
        if (!CModule::IncludeModule('learning')){
            $APPLICATION->ThrowException("<br>".GetMessage("LEARNING_MODULE_NOT_INSTALL"));
            return false;
        }
        if (!$this->InstallDB())
            return false;

        $this->registerModule();
        $this->InstallFiles();
        $this->registerEvents();
    }
    public function registerModule(): void
    {
        RegisterModule($this->MODULE_ID);

    }
    public function registerEvents(): void
    {

        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->registerEventHandler(
            'main',
            'OnBuildGlobalMenu',
            $this->MODULE_ID,
            'LearningHelper\\Events',
            'CreateAdminMenu'
        );

        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->registerEventHandler(
            'learning',
            'OnBeforeLessonAdd',
            $this->MODULE_ID,
            'LearningHelper\\Events',
            'OnLearningAdd'
        );
    }
    function InstallDB()
    {
        global $DB, $APPLICATION;

        if (!$DB->TableExists('b_learn_h_comparison_answer') || !$DB->TableExists('b_learn_status'))
        {
            $this->error = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/local/modules/".$this->MODULE_ID."/install/db/mysql/install.sql");

            if($this->error)
            {
                $APPLICATION->ThrowException("<br>".GetMessage("LEARNING_TABLE_NOT_INSTALL"));
                return false;
            }
        }
        //Database tables create
        if ($DB->TableExists('b_learn_groups_member') && !$DB->Query("SELECT DATE_CREATE FROM b_learn_groups_member WHERE 1=0", true))
        {
            $this->error = $DB->query("ALTER TABLE `b_learn_groups_member` ADD `DATE_CREATE` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `USER_ID`", true);
            if(!$this->error)
            {
                $APPLICATION->ThrowException("<br>".GetMessage("LEARNING_TABLE_NOT_INSTALL"));
                return false;
            }
        }
        return true;
    }

    public function DoUninstall()
    {
        $this->unregisterEvents();
        $this->unRegisterModule();
    }

    public function InstallFiles()
    {
        //Admin files
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/local/modules/$this->MODULE_ID/install/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin", false);
    }

    public function unRegisterModule()
    {
        UnRegisterModule($this->MODULE_ID);
    }

    public function unregisterEvents(): void
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'main',
            'OnBuildGlobalMenu',
            $this->MODULE_ID,
            'LearningHelper\\Events',
            'CreateAdminMenu'
        );
        $eventManager->unRegisterEventHandler(
            'learning',
            'OnBeforeLessonAdd',
            $this->MODULE_ID,
            'LearningHelper\\Events',
            'OnLearningAdd'
        );
    }
}
