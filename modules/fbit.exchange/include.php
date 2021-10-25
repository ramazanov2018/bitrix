<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/local/modules/fbit.exchange/lib/HLBlockMigration.php";

use \Bitrix\Main\Loader;

Loader::registerAutoLoadClasses("fbit.exchange", array(
        "Fbit\Exchange\ExchangeLogsControler" => "/lib/ExchangeLogsController.php",
    )
);
?>