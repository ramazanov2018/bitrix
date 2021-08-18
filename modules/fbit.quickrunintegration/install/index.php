<?php
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

if(class_exists("fbit_quickrunintegration")) return;

class fbit_quickrunIntegration extends CModule{
    var $MODULE_ID = "fbit.quickrunintegration";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_GROUP_RIGHTS = "Y";

    var $errors;

    function __construct()
    {
        $arModuleVersion = array();
        $path = str_repeat("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path."/version.php");

        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)){
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }else {
            $this->MODULE_VERSION = "1.0.0";
            $this->MODULE_VERSION_DATE ="2020-09-04 00:00:00";
        }

        $this->MODULE_NAME = Loc::getMessage('QUICRUN_INTEGRATION_MODULE_NAME');
        $this->MODULE_DESCRIPTION =Loc::getMessage('QUICRUN_INTEGRATION_MODULE_DESC');
        $this->MODULE_PATH = $this->getModulePath();
    }

    protected function getModulePath()
    {
        $modulePath = explode('/', __FILE__);
        $modulePath = array_slice($modulePath, 0, array_search($this->MODULE_ID, $modulePath) + 1);

        return join('/', $modulePath);
    }

    function RegisterAgent()
    {
        //agents
        //\CAgent::AddAgent("Fbit\\Quickrunintegration\\IntegrationHandler::ToQuickrunAgent();", $this->MODULE_ID, "N",5400 );
        //\CAgent::AddAgent("Fbit\\Quickrunintegration\\IntegrationHandler::FromQuickrunAgent();", $this->MODULE_ID, "N",5400 );

        return true;
    }

    function RemoveAgent()
    {
        //agents
        //\CAgent::RemoveModuleAgents($this->MODULE_ID);
        return true;
    }

    function CreateLogFolder()
    {
        $dir = $_SERVER['DOCUMENT_ROOT']."/quickrun_exchange_log";
        if(!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        return true;
    }

    function DoInstall()
    {
        $this->CreateLogFolder();
        \Bitrix\Main\ModuleManager::RegisterModule($this->MODULE_ID);
        //$this->RegisterAgent();
        return true;
    }

    function DoUninstall()
    {
        //$this->RemoveAgent();
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
        return true;
    }
}
?>