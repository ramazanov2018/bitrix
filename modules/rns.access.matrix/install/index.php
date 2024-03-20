<?php
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\EventManager;

Loc::loadMessages(__FILE__);

class rns_access_matrix extends CModule
{
    public $MODULE_ID = "rns.access.matrix";
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

        $this->MODULE_NAME = Loc::getMessage("ACCESS_MATRIX_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("ACCESS_MATRIX_MODULE_DESCRIPTION");
        $this->PARTNER_NAME = Loc::getMessage("ACCESS_MATRIX_REMINDER_MODULE_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("ACCESS_MATRIX_MODULE_PARTNER_URL");
    }

    public function DoInstall()
    {
        global $APPLICATION;
        $this->registerModule();
        $this->installFiles();
        $this->registerEvents();
        \Bitrix\Main\Loader::includeModule($this->MODULE_ID);

        $this->CreateHLBlock();
        $this->SetProjectsRight();

    }
    public function DoUninstall()
    {
        $this->unregisterEvents();
        $this->unRegisterModule();
        $this->ReturnProjectsRight();

    }

    public function registerModule(): void
    {
        RegisterModule($this->MODULE_ID);

    }
    public function installFiles()
    {
        if($_ENV["COMPUTERNAME"]!='BX')
        {
            //Admin files
            CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/".$this->MODULE_ID."/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", false);
        }
    }
    public function CreateHLBlock()
    {
        return \Rns\AccessMatrix\HLBlockAccessMatrix::CreateHLBlock();
    }
    public function DeleteHLBlock()
    {
        \Rns\AccessMatrix\HLBlockAccessMatrix::DeleteHLBlock();
    }

    public function SetProjectsRight()
    {
        $perms = [
            "view" => "L",
            "view_all" => "L",
            "sort" => "L",
            "create_tasks" => "L",
            "edit_tasks" => "L",
            "delete_tasks" => "L",

        ];
        \Bitrix\Main\Loader::includeModule("socialnetwork");
        $resGroup = \Bitrix\Socialnetwork\WorkgroupTable::getList([
            'select' => ['ID'],
        ]);
        while ($group = $resGroup->fetch())
        {
            $idTmp = CSocNetFeatures::setFeature("G", $group["ID"], "tasks", true);
            if ($idTmp){
                foreach($perms as $key => $perm){
                    $id1Tmp = CSocNetFeaturesPerms::SetPerm($idTmp, $key, $perm);
                }
            }
        }
    }

    public function ReturnProjectsRight()
    {
        $perms = [
            "view" => "L",
            "view_all" => "E",
            "sort" => "E",
            "create_tasks" => "E",
            "edit_tasks" => "E",
            "delete_tasks" => "E",

        ];
        \Bitrix\Main\Loader::includeModule("socialnetwork");
        $resGroup = \Bitrix\Socialnetwork\WorkgroupTable::getList([
            'select' => ['ID'],
        ]);
        while ($group = $resGroup->fetch())
        {
            $idTmp = CSocNetFeatures::setFeature("G", $group["ID"], "tasks", true);
            if ($idTmp){
                foreach($perms as $key => $perm){
                    $id1Tmp = CSocNetFeaturesPerms::SetPerm($idTmp, $key, $perm);
                }
            }
        }
    }

    public function registerEvents(): void
    {

        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler(
            'socialnetwork',
            'OnBeforeSocNetGroupAdd',
            $this->MODULE_ID,
            'Rns\\AccessMatrix\\Events',
            'OnProjectAdd'
        );
        $eventManager->registerEventHandler(
            'socialnetwork',
            'OnSocNetGroupAdd',
            $this->MODULE_ID,
            'Rns\\AccessMatrix\\Events',
            'OnAfterProjectAdd'
        );

        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler(
            'socialnetwork',
            'OnBeforeSocNetGroupUpdate',
            $this->MODULE_ID,
            'Rns\\AccessMatrix\\Events',
            'OnProjectUpdate'
        );

        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler(
            'socialnetwork',
            'OnBeforeSocNetGroupDelete',
            $this->MODULE_ID,
            'Rns\\AccessMatrix\\Events',
            'OnProjectDelete'
        );
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler(
            'socialnetwork',
            'onSocNetGroupDelete',
            $this->MODULE_ID,
            'Rns\\AccessMatrix\\Events',
            'OnAfterProjectDelete'
        );

        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler(
            'tasks',
            'OnBeforeTaskAdd',
            $this->MODULE_ID,
            'Rns\\AccessMatrix\\Events',
            'OnTaskAdd'
        );

        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler(
            'tasks',
            'OnBeforeTaskUpdate',
            $this->MODULE_ID,
            'Rns\\AccessMatrix\\Events',
            'OnTaskUpdate'
        );
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler(
            'tasks',
            'OnBeforeTaskDelete',
            $this->MODULE_ID,
            'Rns\\AccessMatrix\\Events',
            'OnTaskDelete'
        );

        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler(
            'tasks',
            'OnBeforeTaskAdd',
            $this->MODULE_ID,
            'Rns\\AccessMatrix\\Events',
            'OnResolutionAdd'
        );

        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler(
            'tasks',
            'OnBeforeTaskUpdate',
            $this->MODULE_ID,
            'Rns\\AccessMatrix\\Events',
            'OnResolutionUpdate'
        );
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler(
            'tasks',
            'OnBeforeTaskDelete',
            $this->MODULE_ID,
            'Rns\\AccessMatrix\\Events',
            'OnResolutionDelete'
        );
    }

    public function unregisterEvents(): void
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'socialnetwork',
            'OnBeforeSocNetGroupAdd',
            $this->MODULE_ID,
            'Rns\\AccessMatrix\\Events',
            'OnProjectAdd'
        );
        $eventManager->unRegisterEventHandler(
            'socialnetwork',
            'OnSocNetGroupAdd',
            $this->MODULE_ID,
            'Rns\\AccessMatrix\\Events',
            'OnAfterProjectAdd'
        );
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'socialnetwork',
            'OnBeforeSocNetGroupUpdate',
            $this->MODULE_ID,
            'Rns\\AccessMatrix\\Events',
            'OnProjectUpdate'
        );

        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'socialnetwork',
            'OnBeforeSocNetGroupDelete',
            $this->MODULE_ID,
            'Rns\\AccessMatrix\\Events',
            'OnProjectDelete'
        );
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'socialnetwork',
            'onSocNetGroupDelete',
            $this->MODULE_ID,
            'Rns\\AccessMatrix\\Events',
            'OnAfterProjectDelete'
        );

        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'tasks',
            'OnBeforeTaskAdd',
            $this->MODULE_ID,
            'Rns\\AccessMatrix\\Events',
            'OnTaskAdd'
        );

        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'tasks',
            'OnBeforeTaskUpdate',
            $this->MODULE_ID,
            'Rns\\AccessMatrix\\Events',
            'OnTaskUpdate'
        );
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'tasks',
            'OnBeforeTaskDelete',
            $this->MODULE_ID,
            'Rns\\AccessMatrix\\Events',
            'OnTaskDelete'
        );

        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'tasks',
            'OnBeforeTaskAdd',
            $this->MODULE_ID,
            'Rns\\AccessMatrix\\Events',
            'OnResolutionAdd'
        );

        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'tasks',
            'OnBeforeTaskUpdate',
            $this->MODULE_ID,
            'Rns\\AccessMatrix\\Events',
            'OnResolutionUpdate'
        );
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'tasks',
            'OnBeforeTaskDelete',
            $this->MODULE_ID,
            'Rns\\AccessMatrix\\Events',
            'OnResolutionDelete'
        );
    }

    public function unInstallFiles()
    {
        DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/".$this->MODULE_ID."/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
    }
    public function unRegisterModule()
    {
        UnRegisterModule($this->MODULE_ID);
    }
}
