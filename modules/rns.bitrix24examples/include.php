<?php
\Bitrix\Main\Loader::includeModule('highloadblock');

Bitrix\Main\Loader::registerAutoloadClasses(
    "rns.bitrix24examples",
    [
        'Rns\Bitrix24Examples\Helpers\UserBirthdaysEntity'	       => 'lib/helpers/UserBirthdaysEntity.php',
        'Rns\Bitrix24Examples\Migrations\HelperInstaller'	       => 'lib/migrations/HelperInstaller.php',
        'Rns\Bitrix24Examples\RestBirthdays'	       => 'lib/RestBirthdays.php',
    ]
);