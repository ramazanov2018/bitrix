<?php

Bitrix\Main\Loader::registerAutoloadClasses(
    "rns.testreminder",
    [
        '\Rns\TestReminder\HLBlockTestRemind'	       => 'lib/HLBlockTestRemind.php',
        '\Rns\TestReminder\TestRemindTable'	       => 'lib/TestRemindTable.php',
        '\Rns\TestReminder\Event'	       => 'lib/Event.php',
        //'\Rns\TestReminder\Controller\RemindController'	       => 'lib/RemindController.php',
    ]
);