<?php /** @noinspection PhpUndefinedVariableInspection */

/** @noinspection PhpUndefinedVariableInspection */

use Bitrix\Main\Localization\Loc;
use RNS\Integrations\Models\IntegrationOptionsTableWrapper;
use RNS\Integrations\Processors\IntegrationAgent;
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

$entityId = 'INTEGRATION_OPTIONS';
$tableId = 'integration_options';
$sort = new CAdminSorting($tableId, 'id', 'asc');
/** @var CAdminList $list */
$list = new CAdminList($tableId, $sort);

if ($objId = $list->GroupAction()) {
    switch ($_REQUEST['action']) {
        case 'run':
            try {
                IntegrationAgent::run($ID, ['isManual' => true]);
                if (IntegrationAgent::$result->success) {
                    if (IntegrationAgent::$result->objectsAdded > 0 || IntegrationAgent::$result->objectsUpdated > 0) {
                        $message = sprintf(Loc::getMessage('INTEGRATIONS_OPTIONS_LIST_IMPORT_SUCCESS'),
                                           IntegrationAgent::$result->objectsTotal,
                                           IntegrationAgent::$result->objectsAdded,
                                           IntegrationAgent::$result->objectsUpdated);
                        CAdminMessage::ShowMessage(["MESSAGE" => $message, "TYPE" => "OK"]);
                    } else {
                        CAdminMessage::ShowMessage(["MESSAGE" => Loc::getMessage('INTEGRATIONS_OPTIONS_LIST_IMPORT_EMPTY'), "TYPE" => "OK"]);
                    }
                } else {
                    $errors = implode("\n", IntegrationAgent::$result->errors);
                    if (IntegrationAgent::$result->objectsAdded > 0 || IntegrationAgent::$result->objectsUpdated > 0) {
                        $message = sprintf(Loc::getMessage('INTEGRATIONS_OPTIONS_LIST_IMPORT_SUCCESS_PARTIAL'),
                            IntegrationAgent::$result->objectsTotal,
                            IntegrationAgent::$result->objectsAdded,
                            IntegrationAgent::$result->objectsUpdated) . $errors;
                        CAdminMessage::ShowMessage(["MESSAGE" => $message, "TYPE" => "ERROR"]);
                    } else {
                        CAdminMessage::ShowMessage(["MESSAGE" => Loc::getMessage('INTEGRATIONS_OPTIONS_LIST_IMPORT_FAILURE') . $errors, "TYPE" => "ERROR"]);
                    }
                }
            } catch (\Exception $ex) {
                CAdminMessage::ShowMessage(["MESSAGE" => Loc::getMessage('INTEGRATIONS_OPTIONS_LIST_IMPORT_FAILURE') . $ex->getMessage(), "TYPE" => "ERROR"]);
            }
            break;
        case 'delete':
            IntegrationOptionsTableWrapper::delete($ID);
            break;
    }
}

$headers = [
  [
    'id' => 'NAME',
    'content' => Loc::getMessage("INTEGRATIONS_OPTIONS_LIST_FIELD_NAME"),
    'default' => true,
    'sort' => 'NAME'
  ],
  [
    'id' => 'SYSTEM_NAME',
    'content' => Loc::getMessage("INTEGRATIONS_OPTIONS_LIST_FIELD_SYS_NAME"),
    'default' => true,
    'sort' => 'SYSTEM_NAME'
  ],
  [
    'id' => 'EXCHANGE_TYPE_NAME',
    'content' => Loc::getMessage("INTEGRATIONS_OPTIONS_LIST_FIELD_EXCH_TYPE"),
    'default' => true,
    'sort' => 'EXCHANGE_TYPE_NAME'
  ],
  [
    'id' => 'CREATED',
    'content' => Loc::getMessage("INTEGRATIONS_OPTIONS_LIST_FIELD_CREATED"),
    'default' => true,
    'sort' => 'created',
  ],
  [
    'id' => 'CREATED_BY',
    'content' => Loc::getMessage("INTEGRATIONS_OPTIONS_LIST_FIELD_AUTHOR"),
    'default' => true,
    'sort' => 'CREATED_BY',
  ],
  [
    'id' => 'MODIFIED',
    'content' => Loc::getMessage("INTEGRATIONS_OPTIONS_LIST_FIELD_MODIFIED"),
    'default' => true,
    'sort' => 'modified',
  ],
  [
    'id' => 'MODIFIED_BY',
    'content' => Loc::getMessage("INTEGRATIONS_OPTIONS_LIST_FIELD_EDITOR"),
    'default' => true,
    'sort' => 'MODIFIED_BY',
  ],
  [
    'id' => 'ACTIVE',
    'content' => Loc::getMessage("INTEGRATIONS_OPTIONS_LIST_FIELD_ACTIVE"),
    'default' => false,
    'sort' => 'ACTIVE',
  ],
  [
    'id' => 'DIRECTION',
    'content' => Loc::getMessage("INTEGRATIONS_OPTIONS_LIST_FIELD_DIRECTION"),
    'default' => false,
    'sort' => 'DIRECTION',
  ],
  [
    'id' => 'DESCRIPTION',
    'content' => Loc::getMessage("INTEGRATIONS_OPTIONS_LIST_FIELD_DESCRIPTION"),
    'default' => false,
    'sort' => 'DESCRIPTION',
  ]
];

$USER_FIELD_MANAGER->AdminListAddHeaders($entityId, $headers);
$list->AddHeaders($headers);

$arUsersCache = [];
$res = IntegrationOptionsTable::getList([
  'select' => ['*', 'SYSTEM_NAME' => 'SYSTEM.NAME', 'EXCHANGE_TYPE_NAME' => 'EXCHANGE_TYPE.NAME', 'EXCHANGE_TYPE_CODE' => 'EXCHANGE_TYPE.CODE'],
  'order' => [strtoupper($sort->getField()) => $sort->getOrder()]
]);
while ($dr = $res->fetch()) {

    $row = &$list->addRow('ID', $dr, 'rns_integrations_options_edit.php?lang='.LANGUAGE_ID.'&ID='.$dr['ID']);

    $USER_FIELD_MANAGER->AddUserFields($entityId, $dr, $row);

    $htmlLink = 'rns_integrations_options_edit.php?ID='.urlencode($dr['ID']).'&lang='.LANGUAGE_ID;
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

    $row->AddViewField('ACTIVE', $dr['ACTIVE'] == 'Y' ? 'Да' : 'Нет');
    $row->AddViewField('DIRECTION', $dr['DIRECTION'] == 0 ? 'Импорт' : 'Экспорт');
    $row->AddViewField('DESCRIPTION', htmlspecialcharsbx($dr['DESCRIPTION']));

    $arActions = [
      [
        "ICON" => "edit",
        "DEFAULT" => "Y",
        "TEXT" => GetMessage("INTEGRATIONS_OPTIONS_LIST_ACT_EDIT"),
        "ACTION" => $list->ActionRedirect("rns_integrations_options_edit.php?ID=".urlencode($dr['ID'])."&lang=".LANGUAGE_ID)
      ]
    ];
    if ($dr['EXCHANGE_TYPE_CODE'] != 'files' && ($dr['EXCHANGE_TYPE_CODE'] != 'email' || $dr['DIRECTION'] == 0)) {
        $arActions[] = [
          "ICON" => "btn_download",
          "TEXT" => GetMessage("INTEGRATIONS_OPTIONS_LIST_ACT_RUN"),
          "ACTION" => $list->ActionDoGroup($dr['ID'], "run")
        ];
    }
    $arActions[] = ["SEPARATOR" => true];
    $arActions[] = [
        "ICON" => "delete",
        "TEXT" => GetMessage("INTEGRATIONS_OPTIONS_LIST_ACT_DELETE"),
        "ACTION" => "if(confirm('".CUtil::JSEscape(GetMessage("INTEGRATIONS_OPTIONS_LIST_ACT_DEL_CONFIRM"))."')) ".$list->ActionDoGroup($dr['ID'], "delete"),
    ];
    $row->AddActions($arActions);
}

$context = [
  [
    'ICON' => 'btn_new',
    'TEXT' => GetMessage('MAIN_ADD'),
    'LINK' => 'rns_integrations_options_edit.php?lang='.LANGUAGE_ID,
    'TITLE' => GetMessage('MAIN_ADD')
  ]
];

$list->AddAdminContextMenu($context);
$list->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage('INTEGRATIONS_OPTIONS_LIST_TITLE'));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$list->DisplayList();
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
