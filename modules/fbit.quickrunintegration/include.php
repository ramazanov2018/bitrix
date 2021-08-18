<?php
use \Bitrix\Main\Loader;

if(Loader::includeModule('crm')){
    Loader::registerAutoLoadClasses("fbit.quickrunintegration", array(
            "Fbit\Quickrunintegration\PortalToQuickrun" => "/lib/PortalToQuickrun.php",
            "Fbit\Quickrunintegration\QuickrunToPortal" => "/lib/QuickrunToPortal.php",
            "Fbit\Quickrunintegration\IntegrationHandler" => "/lib/IntegrationHandler.php",
            "Fbit\Quickrunintegration\quickrunLog" => "/lib/quickrunLog.php",
        )
    );
}

?>