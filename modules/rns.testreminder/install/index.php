<?php
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\EventManager,
    Rns\TestReminder\HLBlockTestRemind;

Loc::loadMessages(__FILE__);

class rns_testreminder extends CModule
{
    public $MODULE_ID = "rns.testreminder";
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

        $this->MODULE_NAME = Loc::getMessage("TEST_REMINDER_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("TEST_REMINDER_MODULE_DESCRIPTION");
        $this->PARTNER_NAME = Loc::getMessage("TEST_REMINDER_MODULE_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("TEST_REMINDER_MODULE_PARTNER_URL");
    }

    public function DoInstall()
    {
        global $APPLICATION;

        $this->registerModule();

        \Bitrix\Main\Loader::includeModule($this->MODULE_ID);
        if (!$this->CreateHLBlock()){
            $this->unRegisterModule();
            return false;
        }
        $this->InstallFiles();
        $this->registerEvents();
        $this->RegisterAgent();

    }
    public function DoUninstall()
    {
        \Bitrix\Main\Loader::includeModule($this->MODULE_ID);
        $this->DeleteHLBlock();
        $this->unregisterEvents();
        $this->RemoveAgent();
        $this->unRegisterModule();
    }

    public function registerModule(): void
    {
        RegisterModule($this->MODULE_ID);

    }
    public function unRegisterModule()
    {
        UnRegisterModule($this->MODULE_ID);
    }

    public function CreateHLBlock()
    {
        return HLBlockTestRemind::CreateHLBlock();
    }
    public function DeleteHLBlock()
    {
        HLBlockTestRemind::DeleteHLBlock();
    }

    function RegisterAgent()
    {
        //agents
        \CAgent::AddAgent("Rns\\TestReminder\\Event::RemindTableClearAgent();", $this->MODULE_ID, "N",86400 );

        return true;
    }

    function RemoveAgent()
    {
        //agents
        \CAgent::RemoveModuleAgents($this->MODULE_ID);
        return true;
    }

    public function registerEvents(): void
    {

        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler(
            'main',
            'OnEpilog',
            $this->MODULE_ID,
            'Rns\\TestReminder\\Event',
            'OnRemind'
        );
    }

    public function unregisterEvents(): void
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'main',
            'OnEpilog',
            $this->MODULE_ID,
            'Rns\\TestReminder\\Event',
            'OnRemind'
        );
    }

    public function InstallFiles()
    {
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/local/modules/".$this->MODULE_ID."/install/components",
            $_SERVER["DOCUMENT_ROOT"]."/local/components",
            true, true
        );
        return true;
    }
}
