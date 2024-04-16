<?php /** @noinspection PhpUndefinedVariableInspection */

use Bitrix\Main\Entity\Query\Join;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Localization\Loc;
use RNS\Integrations\ExternalSystemTable;
use RNS\Integrations\IntegrationOptionsTable;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/admin_tools.php');
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CUserTypeManager $USER_FIELD_MANAGER */

if (!CModule::IncludeModule('rns.integrations')) {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

Loc::loadMessages(__FILE__);

$entityId = 'INTEGRATION_EXTERNAL_SYSTEM';
$tableId = 'integration_external_system';
$sort = new CAdminSorting($tableId, 'id', 'asc');
/** @var CAdminList $list */
$list = new CAdminList($tableId, $sort);

$filterRows = [
  'ID' => 'ID',
  'NAME' => Loc::getMessage('INTEGRATIONS_SYSTEM_LIST_FIELD_NAME'),
  'CREATED' => Loc::getMessage('INTEGRATIONS_SYSTEM_LIST_FIELD_CREATED'),
];

$USER_FIELD_MANAGER->AddFindFields($entityId, $filterRows);

$filter = new CAdminFilter($tableId . '_filter_id', $filterRows);

$filterFields = [
  'find_name',
  'find_id',
  'find_created_from',
  'find_created_to',
];
$USER_FIELD_MANAGER->AdminListAddFilterFields($entityId, $filterFields);

$adminFilter = $list->InitFilter($filterFields);

$filter = [
  'ID' => $adminFilter['find_id'],
  '%NAME' => $adminFilter['find_name'],
  '>=CREATED' => $adminFilter['find_created_from'],
  '<=CREATED' => $adminFilter['find_created_to'],
];
$USER_FIELD_MANAGER->AdminListAddFilter($entityId, $filter);

if ($objId = $list->GroupAction()) {
    switch ($_REQUEST['action']) {
        case 'delete':
            try {
                $res = IntegrationOptionsTable::getList([
                    'select' => ['ID'],
                    'filter' => ['SYSTEM_ID' => $ID]
                ]);
                if (empty($res->fetch())) {
                    ExternalSystemTable::delete($ID);
                    ShowMessage(['TYPE' => 'OK', 'MESSAGE' => Loc::getMessage("INTEGRATIONS_SYSTEM_LIST_ACT_DEL_SUCCESS")]);
                } else {
                    ShowMessage(Loc::getMessage("INTEGRATIONS_SYSTEM_LIST_ACT_DEL_DENIED"));
                }
            } catch (\Exception $ex) {
                ShowMessage(Loc::getMessage("INTEGRATIONS_SYSTEM_LIST_ACT_DEL_ERROR") . $ex->getMessage());
            }
            break;
    }
}

$headers = [
    [
        'id' => 'NAME',
        'content' => Loc::getMessage("INTEGRATIONS_SYSTEM_LIST_FIELD_NAME"),
        'default' => true,
        'sort' => 'name'
    ],
    [
        'id' => 'CREATED',
        'content' => Loc::getMessage("INTEGRATIONS_SYSTEM_LIST_FIELD_CREATED"),
        'default' => true,
        'sort' => 'created',
    ],
      [
        'id' => 'CREATED_BY',
        'content' => Loc::getMessage("INTEGRATIONS_SYSTEM_LIST_FIELD_AUTHOR"),
        'default' => true,
        'sort' => 'CREATED_BY',
      ],
      [
        'id' => 'MODIFIED',
        'content' => Loc::getMessage("INTEGRATIONS_SYSTEM_LIST_FIELD_MODIFIED"),
        'default' => true,
        'sort' => 'modified',
      ],
      [
        'id' => 'MODIFIED_BY',
        'content' => Loc::getMessage("INTEGRATIONS_SYSTEM_LIST_FIELD_EDITOR"),
        'default' => true,
        'sort' => 'MODIFIED_BY',
      ],
    [
        'id' => 'IMPORT_ACTIVE',
        'content' => Loc::getMessage("INTEGRATIONS_SYSTEM_LIST_FIELD_IMPORT"),
        'default' => true,
    ],
    [
        'id' => 'EXPORT_ACTIVE',
        'content' => Loc::getMessage("INTEGRATIONS_SYSTEM_LIST_FIELD_EXPORT"),
        'default' => true,
    ],
    [
        'id' => 'DESCRIPTION',
        'content' => Loc::getMessage("INTEGRATIONS_SYSTEM_LIST_FIELD_DESCRIPTION")
    ]
];

$USER_FIELD_MANAGER->AdminListAddHeaders($entityId, $headers);
$list->AddHeaders($headers);

$res = ExternalSystemTable::query()
  ->registerRuntimeField('IMPORT',
        new ReferenceField(
          'IMPORT',
    IntegrationOptionsTable::class,
          Join::on('this.ID', 'ref.SYSTEM_ID')->where('ref.DIRECTION',  IntegrationOptionsTable::DIRECTION_IMPORT),
          [
            'join_type' => 'LEFT'
          ]
    )
  )
  ->registerRuntimeField('EXPORT',
    new ReferenceField(
      'EXPORT',
      IntegrationOptionsTable::class,
      Join::on('this.ID', 'ref.SYSTEM_ID')->where('ref.DIRECTION',  IntegrationOptionsTable::DIRECTION_EXPORT),
      [
        'join_type' => 'LEFT'
      ]
    )
  )
  ->setSelect(['*', 'IMPORT_ACTIVE' => 'IMPORT.ACTIVE', 'EXPORT_ACTIVE' => 'EXPORT.ACTIVE'])
  ->addOrder(strtoupper($sort->getField()), $sort->getOrder())
  ->fetchAll();

$valueYes = Loc::getMessage("INTEGRATIONS_SYSTEM_LIST_FIELD_VALUE_ACTIVE");
$valueNo = Loc::getMessage("INTEGRATIONS_SYSTEM_LIST_FIELD_VALUE_INACTIVE");
$valueNull = Loc::getMessage("INTEGRATIONS_SYSTEM_LIST_FIELD_VALUE_NOTSET");

$arUsersCache = [];

foreach ($res as $dr) {

    $dr['IMPORT_ACTIVE'] = !is_null($dr['IMPORT_ACTIVE']) ? ($dr['IMPORT_ACTIVE'] == 'Y' ? $valueYes : $valueNo) : $valueNull;
    $dr['EXPORT_ACTIVE'] = !is_null($dr['EXPORT_ACTIVE']) ? ($dr['EXPORT_ACTIVE'] == 'Y' ? $valueYes : $valueNo) : $valueNull;

    $row = &$list->addRow('ID', $dr, 'rns_integrations_system_edit.php?lang='.LANGUAGE_ID.'&ID='.$dr['ID']);

    $USER_FIELD_MANAGER->AddUserFields($entityId, $dr, $row);

    $htmlLink = 'rns_integrations_system_edit.php?ID='.urlencode($dr['ID']).'&lang='.LANGUAGE_ID;
    $row->AddViewField("NAME", '<a href="'.htmlspecialcharsbx($htmlLink).'">'.htmlspecialcharsEx($dr['NAME']).'</a>');

    if(!array_key_exists($dr['CREATED_BY'], $arUsersCache)) {
        $rsUser = CUser::GetByID($dr['CREATED_BY']);
        $arUsersCache[$dr['CREATED_BY']] = $rsUser->Fetch();
    }

    if($arUser = $arUsersCache[$dr['CREATED_BY']]) {
        $htmlLink = 'user_edit.php?lang='.LANGUAGE_ID.'&ID='.$dr['CREATED_BY'];
        $row->AddViewField("CREATED_BY", '[<a href="'.htmlspecialcharsbx($htmlLink).'">'.$dr['CREATED_BY']."</a>]&nbsp;".htmlspecialcharsEx("(".$arUser["LOGIN"].") ".$arUser["NAME"]." ".$arUser["LAST_NAME"]));
        unset($htmlLink);
    }

    if(!array_key_exists($dr['MODIFIED_BY'], $arUsersCache)) {
        $rsUser = CUser::GetByID($dr['MODIFIED_BY']);
        $arUsersCache[$dr['MODIFIED_BY']] = $rsUser->Fetch();
    }

    if($arUser = $arUsersCache[$dr['MODIFIED_BY']]) {
        $htmlLink = 'user_edit.php?lang='.LANGUAGE_ID.'&ID='.$dr['MODIFIED_BY'];
        $row->AddViewField("MODIFIED_BY", '[<a href="'.htmlspecialcharsbx($htmlLink).'">'.$dr['MODIFIED_BY']."</a>]&nbsp;".htmlspecialcharsEx("(".$arUser["LOGIN"].") ".$arUser["NAME"]." ".$arUser["LAST_NAME"]));
        unset($htmlLink);
    }

    $arActions = [
      [
        "ICON" => "edit",
        "DEFAULT" => "Y",
        "TEXT" => GetMessage("INTEGRATIONS_SYSTEM_LIST_ACT_EDIT"),
        "ACTION" => $list->ActionRedirect("rns_integrations_system_edit.php?ID=".urlencode($dr['ID'])."&lang=".LANGUAGE_ID)
      ],
      ["SEPARATOR" => true],
      [
        "ICON" => "delete",
        "TEXT" => GetMessage("INTEGRATIONS_SYSTEM_LIST_ACT_DELETE"),
        "ACTION" => "if(confirm('".CUtil::JSEscape(GetMessage("INTEGRATIONS_SYSTEM_LIST_ACT_DEL_CONFIRM"))."')) ".$list->ActionDoGroup($dr['ID'], "delete"),
      ]
    ];
    $row->AddActions($arActions);
}

$context = [
  [
    'ICON' => 'btn_new',
    'TEXT' => GetMessage('MAIN_ADD'),
    'LINK' => 'rns_integrations_system_edit.php?lang='.LANGUAGE_ID,
    'TITLE' => GetMessage('MAIN_ADD')
  ]
];

$list->AddAdminContextMenu($context);
$list->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage('INTEGRATIONS_SYSTEM_LIST_TITLE'));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$list->DisplayList();
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
