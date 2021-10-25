<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/local/modules/qsoft.committees/include.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/local/modules/fbit.exchange/include.php";

use Bitrix\Main\Localization\Loc;
use Fbit\Exchange\HLBlockMigration;

Loc::loadMessages(__FILE__);

class fbit_exchange extends CModule
{
    public $MODULE_ID = "fbit.exchange";

    public $MODULE_NAME;
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;
    public $MODULE_PATH;

    public $MODULE_GROUP_RIGHTS = "Y";

    public function __construct()
    {
        $arModuleVersion = [];
        include(__DIR__ . "/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        
        $this->MODULE_NAME = Loc::getMessage("FBIT_EXCH_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("FBIT_EXCH_MODULE_DESCRIPTION");
        $this->PARTNER_NAME = Loc::getMessage("FBIT_EXCH_MODULE_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("FBIT_EXCH_MODULE_PARTNER_URL");
    }

   
    public function DoInstall()
    {

        $this->registerModule();
        $this->registerEvents();
        $this->CreateHLBlock();
    }

    public function registerModule(): void
    {
        RegisterModule($this->MODULE_ID);

    }
    public function registerEvents(): void
    {
        
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->registerEventHandler(
            'rest',
            'OnRestServiceBuildDescription',
            $this->MODULE_ID,
            '\Fbit\Exchange\RestExp',
            'OnRestServiceBuildDescription'
            );
    }
    public function DoUninstall()
    {
        
        $this->unregisterEvents();
        $this->unRegisterModule();
        $this->DeleteHLBlock();

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
            'OnPageStart',
            $this->MODULE_ID,
            '\Fbit\Exchange\RestExp',
            'OnRestServiceBuildDescription'
            );
    }

    public function CreateHLBlock()
    {
        HLBlockMigration::CreateHLBlock();
    }
    public function DeleteHLBlock()
    {
        HLBlockMigration::DeleteHLBlock();
    }
}
