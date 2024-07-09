
<?php

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ModuleManager;
use Rns\Bitrix24Examples\Migrations\HelperInstaller;

Loc::loadMessages(__FILE__);

class rns_bitrix24examples extends CModule
{
    public $MODULE_ID = "rns.bitrix24examples";
    public $error = false;

    public $MODULE_NAME;
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;
    const HIGHLOAD_BLOCKS = ['UserBirthdays'];

    public $MODULE_GROUP_RIGHTS = "Y";

    public function __construct()
    {
        $arModuleVersion = [];
        include(__DIR__ . "/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

        $this->MODULE_NAME = Loc::getMessage("BITRIX24_EXAMPLES_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("BITRIX24_EXAMPLES_MODULE_DESCRIPTION");
        $this->PARTNER_NAME = Loc::getMessage("BITRIX24_EXAMPLES_MODULE_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("BITRIX24_EXAMPLES_MODULE_PARTNER_URL");
    }

    /**
     * Встроенный метод, запускается при установке модуля
     *
     * @return bool
     * @throws \Bitrix\Main\LoaderException
     */
    public function DoInstall(): bool
    {
        $this->registerModule();
        \Bitrix\Main\Loader::includeModule($this->MODULE_ID);
        if (!$this->installMigrations()){
            $this->unRegisterModule();
            return false;
        }
        $this->installFiles();
        $this->registerEvents();
        $this->registerAgents();
        return true;

    }


    /**
     * Встроенный метод, запускает при удалении модуля
     *
     * @return bool
     * @throws \Bitrix\Main\LoaderException
     */
    public function DoUninstall(): bool
    {
        \Bitrix\Main\Loader::includeModule($this->MODULE_ID);
        global $APPLICATION, $step;

        $step = intval($step);

        #шаг при удалении модуля, спрашивает о сохранении таблиц
        if ($step < 2) {
            $APPLICATION->includeAdminFile(
                Loc::getMessage('BITRIX24EXAMPLES_UNINSTALL_TITLE'),
                __DIR__ . '/unstep1.php'
            );
        }else{
            $request = Context::getCurrent()->getRequest();
            if($request->get('savedata') != 'Y')
                $this->unInstallMigrations();

            $this->unInstallFiles();
            $this->unregisterEvents();
            $this->removeAgents();
            $this->unRegisterModule();
        }

        #Удаляет все (либо только устаревшие) файлы кеша по указанному пути
        BXClearCache(true, "/");
        return true;
    }

    /**
     * Устанавливает миграции
     *
     * @return bool
     */
    public function installMigrations(): bool
    {
            $HelperInstaller = new HelperInstaller();
            try {
                $HelperInstaller->initHighloadBlockData();
            } catch (Exception $e) {
                global $APPLICATION;
                $APPLICATION->ThrowException($e->getMessage());
                return false;
            }
            return true;
    }

    /**
     * Удаляем миграции
     *
     * @return bool
     */
    public function unInstallMigrations(): bool
    {
        $HelperInstaller = new HelperInstaller();
        try {
            $HelperInstaller->uninstallHighloadBlocks(self::HIGHLOAD_BLOCKS);
        } catch (Exception $e) {
            global $APPLICATION;
            $APPLICATION->ThrowException($e->getMessage());
            return false;
        }
        return false;
    }

    /**
     * Регистрируем модуль
     *
     * @return void
     */
    public function registerModule(): void
    {
        ModuleManager::registerModule($this->MODULE_ID);
    }

    /**
     * Удаляем регистрацию модуля
     *
     * @return void
     */
    public function unRegisterModule():void
    {
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    function registerAgents(){}

    function removeAgents(){}

    /**
     * Регистрация событий
     *
     * @return void
     */
    public function registerEvents(): void
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();

        #Добавляем пункт "Дни рождения" в верхнее меню на главной странице CRM
        $eventManager->registerEventHandler(
            "crm",
            "OnAfterCrmControlPanelBuild",
            $this->MODULE_ID,
            "Rns\Bitrix24Examples\Events",
            "addCrmMenuItemBirthdays"
        );

        #Подключаем js расширение модуля
        $eventManager->registerEventHandler(
            "main",
            "OnEpilog",
            $this->MODULE_ID,
            "Rns\Bitrix24Examples\Events",
            "loadCustomExtension"
        );

        #Расширяем стандартный модуль restApi
        $eventManager->registerEventHandler(
            'rest',
            'OnRestServiceBuildDescription',
            $this->MODULE_ID,
            '\Rns\Bitrix24Examples\RestBirthdays',
            'OnRestServiceBuildDescription'
        );
    }

    /**
     * Удаление событий
     *
     * @return void
     */
    public function unregisterEvents(): void
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();

        $eventManager->unRegisterEventHandler(
            "crm",
            "OnAfterCrmControlPanelBuild",
            $this->MODULE_ID,
            "Rns\Bitrix24Examples\Events",
            "addCrmMenuItemBirthdays"
        );

        $eventManager->unRegisterEventHandler(
            "main",
            "OnEpilog",
            $this->MODULE_ID,
            "Rns\Bitrix24Examples\Events",
            "loadCustomExtension"
        );

        $eventManager->unRegisterEventHandler(
            'rest',
            'OnRestServiceBuildDescription',
            $this->MODULE_ID,
            '\Rns\Bitrix24Examples\RestBirthdays',
            'OnRestServiceBuildDescription'
        );
    }

    /**
     * Копируем файлы, компоненты модуля
     *
     * @return bool
     */
    public function installFiles(): bool
    {
        CopyDirFiles(__DIR__ . '/js/', $_SERVER['DOCUMENT_ROOT'] . '/local/js', true, true);
        CopyDirFiles(__DIR__ . '/components/', $_SERVER["DOCUMENT_ROOT"] . "/local/components", true, true);
        CopyDirFiles(__DIR__ . '/public/', $_SERVER["DOCUMENT_ROOT"] . "/", true, true);
        return true;
    }

    /**
     * Удаляем файлы, компоненты модуля
     *
     * @return bool
     */
    public function unInstallFiles(): bool
    {
        Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . '/birthdays/');
        Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . '/local/js/rns/bitrix24examples');
        Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . '/local/components/rns/');
        return true;
    }
}
