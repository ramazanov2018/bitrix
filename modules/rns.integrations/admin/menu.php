<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

return [
  [
    'parent_menu' => 'global_menu_services',
    'text' => Loc::getMessage('INTEGRATIONS_MENU_TITLE'),
    'section' => 'rns_integrations',
    'module_id' => 'rns.integrations',
    'items_id' => 'menu_rns_integrations',
    'icon' => 'workflow_menu_icon',
    'page_icon' => 'workflow_menu_icon',
    'sort' => 1,
    'items' => [
      [
        'text'     => Loc::getMessage('INTEGRATIONS_OPTIONS_LIST_TITLE'),
        'url'      => 'rns_integrations_options_list.php?lang=' . LANGUAGE_ID,
        'more_url' => [
          'rns_integrations_options_edit.php?lang=' . LANGUAGE_ID
        ],
        'title'    => Loc::getMessage('INTEGRATIONS_OPTIONS_LIST_TITLE'),
      ],
      [
        'text'     => Loc::getMessage('INTEGRATIONS_SYSTEM_LIST_TITLE'),
        'url'      => 'rns_integrations_system_list.php?lang=' . LANGUAGE_ID,
        'more_url' => [
          'rns_integrations_system_edit.php?lang=' . LANGUAGE_ID
        ],
        'title'    => Loc::getMessage('INTEGRATIONS_SYSTEM_LIST_TITLE'),
      ],
    ]
  ]
];
