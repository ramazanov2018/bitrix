<?php
IncludeModuleLangFile(__FILE__);

if(class_exists("test_catalog")) return;
class test_catalog extends CModule{
    var $MODULE_ID = "test.catalog";
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
            $this->MODULE_VERSION_DATE ="2019-08-09 00:00:00";
        }

        $this->MODULE_NAME = GetMessage("TEST_CATALOG_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("TEST_CATALOG_MODULE_DESC");
    }

    function InstallDB()
    {
        global $DB, $APPLICATION;
        $this->errors = false;

        //Database tables create
        if (!$DB->Query("SELECT 'x' FROM b_test_category_list", true))
        {
            $this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/db/mysql/install.sql");
        }
        if($this->errors !== false)
        {
            $APPLICATION->ThrowException(implode("<br>", $this->errors));
            return false;
        }
        else
        {
            return true;
        }
    }

    function UnInstallDB()
    {
        global $DB, $APPLICATION;
        $this->errors = false;
        $this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/db/mysql/uninstall.sql");

        if($this->errors !== false)
        {
            $APPLICATION->ThrowException(implode("<br>", $this->errors));
            return false;
        }
        return true;
    }

    function InstallFiles()
    {
        //admin files
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");

        return true;
    }

    function UnInstallFiles()

    {
        //admin files
        DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
        return true;
    }

    function RegisterAgent()
    {
        //agents
        \CAgent::AddAgent("Test\\Catalog\\CatalogHelper::TestAgent();", $this->MODULE_ID, "N",5400 );

        return true;
    }

    function RemoveAgent()
    {
        //agents
        \CAgent::RemoveModuleAgents($this->MODULE_ID);
        return true;
    }

    function DoInstall()
    {
        $this->InstallDB();
        $this->InstallFiles();
        \Bitrix\Main\ModuleManager::RegisterModule($this->MODULE_ID);
        $this->RegisterAgent();
        return true;
    }

    function DoUninstall()
    {
        $this->UnInstallDB();
        $this->UnInstallFiles();
        $this->RemoveAgent();
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
        return true;
    }

}
?>