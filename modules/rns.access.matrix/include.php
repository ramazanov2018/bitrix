<?php
CModule::IncludeModule('tasks');
CModule::IncludeModule('socialnetwork');
Bitrix\Main\Loader::registerAutoloadClasses(
    "rns.access.matrix",
    [
        '\Rns\AccessMatrix\AccessMatrix'	       => 'lib/AccessMatrix.php',
        '\Rns\AccessMatrix\Access'	       => 'lib/Access.php',
        '\Rns\AccessMatrix\HLBlockAccessMatrix'	       => 'lib/HLBlockAccessMatrix.php',
        '\Rns\AccessMatrix\DashboardControls'	       => 'lib/controls/DashboardControls.php',
        '\Rns\AccessMatrix\TimemanControls'	       => 'lib/controls/TimemanControls.php',
        '\Rns\AccessMatrix\ProjectControls'	       => 'lib/controls/ProjectControls.php',
        '\Rns\AccessMatrix\TasksControls'	       => 'lib/controls/TasksControls.php',
        '\Rns\AccessMatrix\CustomDashboardControls'	       => 'lib/controls/CustomDashboardControls.php',
        '\Rns\AccessMatrix\ResolutionsControls'	       => 'lib/controls/ResolutionsControls.php',
        '\Rns\AccessMatrix\Events'	       => 'lib/Events.php',
    ]
);