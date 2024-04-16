<?php /** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedVariableInspection */

/** @noinspection PhpUndefinedVariableInspection */

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use RNS\Integrations\ExchangeTypeTable;
use RNS\Integrations\ExternalSystemTable;
use RNS\Integrations\Helpers\EntityFacade;
use RNS\Integrations\Models\EntityMapItem;
use RNS\Integrations\Models\IntegrationOptionsTableWrapper;
use RNS\Integrations\IntegrationOptionsTable;
use RNS\Integrations\Helpers\ImportHelper;
use RNS\Integrations\Processors\Database\Import;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/admin_tools.php');
global $APPLICATION;
if (!CModule::IncludeModule('rns.integrations')) {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

CJSCore::Init(['ui']);

Loader::includeModule('tasks');
Loc::loadMessages(__FILE__);

$industrialOfficeInstalled = EntityFacade::checkIndustrialOffice();

$backUrl = 'rns_integrations_options_list.php?lang=' . LANGUAGE_ID;

$isProjectFilterActive = !empty($_REQUEST['projects']);

$externalProjectIds = $isProjectFilterActive ? explode(',', $_REQUEST['projects']) : [];
$sourceId = $ID;
ImportHelper::automaticUserMapping((int)$sourceId);
$obj = IntegrationOptionsTableWrapper::getById($ID, $externalProjectIds);

$options = $obj->getOptions();
$mapping = $obj->getMapping();

$projectMapping = $mapping->getProjectMap();

if (empty($externalProjectIds)) {
    $externalProjectIds = array_map(function ($item) {
        /** @var EntityMapItem $item */
        return $item->getExternalEntityId();
    }, $projectMapping->getItems());
}

$errorMessage = '';

$rows = ExternalSystemTable::getList([
  'select' => ['*'],
  'order' => ['ID' => 'ASC']
])->fetchAll();
$externalSystems = [['REFERENCE_ID' => [], 'REFERENCE' => []]];
foreach ($rows as $item) {
    $externalSystems['REFERENCE_ID'][] = $item['ID'];
    $externalSystems['REFERENCE'][] = $item['NAME'];
}

$rows = ExchangeTypeTable::getList([
  'select' => ['*'],
  'order' => ['ID' => 'ASC']
])->fetchAll();
$exchangeTypes = ['REFERENCE_ID' => [], 'REFERENCE' => []];
foreach ($rows as $item) {
    $exchangeTypes['REFERENCE_ID'][] = $item['ID'];
    $exchangeTypes['REFERENCE'][] = $item['NAME'];
}

$directions = [
  'REFERENCE_ID' => [0, 1],
  'REFERENCE' => ['Импорт', 'Экспорт']
];

$tabs = [
  ['DIV' => 'tab-1', 'TAB' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_GENERAL'), 'TITLE' => Loc::getMessage('INTEGRATIONS_OPTIONS_GENERAL_TAB_TITLE')]
];

$noneSelected = Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_NONE_SELECTED');
$emptyDict = [
  'REFERENCE_ID' => [null],
  'REFERENCE' => [$noneSelected]
];

$taskLevelLabel = Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_GENERAL_TASK_LEVEL') . '*';

if ($ID > 0) {
    CJSCore::Init(["jquery"]);
    $systemCode = $obj->getSystemCode();
    $converterNeeded = $systemCode == 'ms_project';
    $exchType = $obj->getExchangeTypeCode();
    $exchDirection = $obj->getDirection();

    $localProjects = EntityFacade::getProjects();
    $localUsers = EntityFacade::getUsers();

    $externalStatusNames = [];
    $externalPropNames = [];
    $internalTypeNames = [];

    $entityStatuses = [];
    $entityTypes = EntityFacade::getEntityTypes($systemCode == 'sap' ? ['TASK'] : []);
    $entityProps = EntityFacade::getEntityProperties();
    foreach ($entityTypes['REFERENCE_ID'] as $i => $entityType) {
        $internalTypeNames[$entityType] = $entityTypes['REFERENCE'][$i];
        $entityStatuses[$entityType] = EntityFacade::getEntityStatuses($entityType);
    }
    $entityStatusOptions = json_encode($entityStatuses, JSON_UNESCAPED_UNICODE);

    $entityTypeOptions = [];
    foreach ($entityTypes['REFERENCE_ID'] as $i => $id) {
        $entityTypeOptions[] = '<option value="' . $id . '">' . $entityTypes['REFERENCE'][$i] . '</option>';
    }

    $entityTypeMapping = $mapping->getEntityTypeMap();
    $entityStatusMapping = $mapping->getEntityStatusMap();
    $propertyMapping = $mapping->getEntityPropertyMap();

    $externalEntityTypes = EntityFacade::getExternalEntityTypes($systemCode);
    $externalEntityStatuses = EntityFacade::getExternalEntityStatuses($systemCode);
    $externalEntityProps = EntityFacade::getExternalEntityProperties($systemCode);

    $externalEntityTypeOptions = [];
    foreach ($externalEntityTypes['REFERENCE_ID'] as $i => $id) {
        $externalEntityTypeOptions[] = '<option value="' . $id . '">' . $externalEntityTypes['REFERENCE'][$i] . '</option>';
    }

    $externalEntityStatusOptions = [];
    foreach ($externalEntityStatuses['REFERENCE_ID'] as $i => $id) {
        $externalStatusNames[$id] = $externalEntityStatuses['REFERENCE'][$i];
        $externalEntityStatusOptions[] = '<option value="' . $id . '">' . $externalEntityStatuses['REFERENCE'][$i] . '</option>';
    }

    $entityPropertyOptions = [];
    foreach ($entityProps['REFERENCE_ID'] as $i => $id) {
        $entityPropertyOptions[] = '<option value="' . $id . '">' . $entityProps['REFERENCE'][$i] . '</option>';
    }
    $externalEntityPropertyOptions = [];

    foreach ($externalEntityProps['REFERENCE_ID'] as $i => $id) {
        $externalPropNames[$id] = $externalEntityProps['REFERENCE'][$i];
        $externalEntityPropertyOptions[] = '<option value="' . $id . '">' . $externalEntityProps['REFERENCE'][$i] . '</option>';
    }

    $priorities = [
      'REFERENCE_ID' => [0, 1, 2],
      'REFERENCE' => [
        Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_PRIORITY_LOW'),
        Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_PRIORITY_MEDIUM'),
        Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_PRIORITY_HIGH')
      ]
    ];

    $attrTabs = [];

    if ($exchType != ExchangeTypeTable::TYPE_FILES) {

        $externalProjects = [
          'REFERENCE_ID' => [],
          'REFERENCE' => []
        ];
        $externalProjectList = [];
        $externalProjectFilterList = [];
        try {
            $externalProjectList = EntityFacade::getExternalProjects($exchType, $systemCode, $obj->getOptions(), $obj->getMapping(),
              $externalProjectIds);
            $externalProjectFilterList = EntityFacade::getExternalProjects($exchType, $systemCode, $obj->getOptions(), $obj->getMapping());
            $externalProjectOptions = [];
        } catch (\Throwable $ex) {
            $errorMessage = $ex->getMessage();
        }

        $projectFilterItems = [];
        foreach ($externalProjectFilterList as $id => $name) {
            $projectFilterItems[] = ['NAME' => $name, 'VALUE' => $id];
        }

        foreach ($externalProjectList as $id => $name) {
            $externalProjectOptions[] = '<option value="' . $id . '">' . $name . '</option>';

            $externalProjects['REFERENCE_ID'][] = $id;
            $externalProjects['REFERENCE'][] = $name;
            if (!$isProjectFilterActive && empty($projectMapping->getItemsByExternalId($id))) {
                $projectMapping->addItem($id);
            }
        }

        foreach ($externalEntityTypes['REFERENCE_ID'] as $idx => $typeId) {
            if (!$entityTypeMapping->getItemByExternalTypeId($typeId)) {
                $entityTypeMapping->addNewItem($typeId);
            }

            $item = $entityTypeMapping->getItemByExternalTypeId($typeId);

            $internalTypeId = $item->getInternalTypeId();
            foreach ($externalEntityStatuses['REFERENCE_ID'] as $statusId) {
                if (!$entityStatusMapping->getItemByExternalStatusId($typeId, $statusId, $internalTypeId)) {
                    $entityStatusMapping->addOrUpdateItem($typeId, $statusId, $internalTypeId);
                }
            }
            foreach ($externalEntityProps['REFERENCE_ID'] as $propId) {
                if (!$propertyMapping->getItemByExternalPropertyId($typeId, $propId, $internalTypeId)) {
                    $propertyMapping->addOrUpdateItem($typeId, $propId, $internalTypeId);
                }
            }

            $typeName = $externalEntityTypes['REFERENCE'][$idx];
            $attrTabs[] = [
              'DIV' => 'tab-' . strtolower($typeId), 'TAB' => $typeName, 'TITLE' => $typeName
            ];
        }

        $externalUsers = [
          'REFERENCE_ID' => [],
          'REFERENCE' => []
        ];
        $users = [];
        try {
            $users = EntityFacade::getExternalUsers($exchType, $systemCode, $obj->getOptions(), $obj->getMapping());
        } catch (\Throwable $ex) {
            $errorMessage = $ex->getMessage();
        }
        foreach ($users as $id => $name) {
            $externalUsers['REFERENCE_ID'][] = $id;
            $externalUsers['REFERENCE'][] = $name;
        }
        foreach ($externalUsers['REFERENCE_ID'] as $i => $id) {
            if (!$mapping->getUserMap()->getItemByExternalId($id)) {
                $mapping->getUserMap()->addItem(null, $id);
            }
        }

    } else {

        foreach ($externalEntityTypes['REFERENCE_ID'] as $idx => $typeId) {

            if (!$entityTypeMapping->getItemByExternalTypeId($typeId)) {
                $entityTypeMapping->addNewItem($typeId);
            }
            $internalTypeId = $entityTypeMapping->getItemByExternalTypeId($typeId)->getInternalTypeId();
            if (!$internalTypeId) {
                $internalTypeId = $entityTypeMapping->getDefaultTypeId();
            }
            foreach ($externalEntityProps['REFERENCE_ID'] as $propId) {
                if (!$propertyMapping->getItemByExternalPropertyId($typeId, $propId, $internalTypeId)) {
                    $propertyMapping->addOrUpdateItem($typeId, $propId, $internalTypeId);
                }
            }

            $typeName = $externalEntityTypes['REFERENCE'][$idx];
            $attrTabs[] = [
              'DIV' => 'tab-' . strtolower($typeId), 'TAB' => $typeName, 'TITLE' => $typeName
            ];
        }

        $defaultOptions = include $_SERVER['DOCUMENT_ROOT'] . '/local/modules/rns.integrations/default_options.php';
        if (!$options->getFileMaxSize()) {
            $options->setFileMaxSize($defaultOptions['files']['fileMaxSize']);
        }
        if (!$options->getTaskMaxCount()) {
            $options->setTaskMaxCount($defaultOptions['files']['taskMaxCount']);
        }
    }
    if ($exchType == ExchangeTypeTable::TYPE_EMAIL) {
        $mailboxOptions = \RNS\Integrations\Helpers\ImportHelper::getMailboxList();

        $defaultOptions = include $_SERVER['DOCUMENT_ROOT'] . '/local/modules/rns.integrations/default_options.php';
        if ($exchDirection == IntegrationOptionsTable::DIRECTION_EXPORT) {
            if (!$options->getSubjectAcceptedTemplate()) {
                $options->setSubjectAcceptedTemplate($defaultOptions['email']['subjectAcceptedTemplate']);
            }
            if (!$options->getAcceptComment()) {
                $options->setAcceptComment($defaultOptions['email']['acceptComment']);
            }
            if (!$options->getSubjectDeclinedTemplate()) {
                $options->setSubjectDeclinedTemplate($defaultOptions['email']['subjectDeclinedTemplate']);
            }
            if (!$options->getRefuseComment()) {
                $options->setRefuseComment($defaultOptions['email']['refuseComment']);
            }
        }
        $isCommentMode = strpos($obj->getName(), 'комментарий') !== false;
        if (!$isCommentMode) {
            if (!$options->getRegexpTitle()) {
                $options->setRegexpTitle($defaultOptions['email']['regexpTitle']);
            }
            if (!$options->getRegexpProject()) {
                $options->setRegexpProject($defaultOptions['email']['regexpProject']);
            }
            if (!$options->getRegexpEndDate()) {
                $options->setRegexpEndDate($defaultOptions['email']['regexpEndDate']);
            }
            if (!$options->getRegexpPriority()) {
                $options->setRegexpPriority($defaultOptions['email']['regexpPriority']);
            }
            if (!$options->getRegexpTag()) {
                $options->setRegexpTag($defaultOptions['email']['regexpTag']);
            }
        } else {
            if (!$options->getTaskIdTemplate()) {
                $options->setTaskIdTemplate($defaultOptions['email']['taskIdTemplate']);
            }
            if (!$options->getCommentIdTemplate()) {
                $options->setCommentIdTemplate($defaultOptions['email']['commentIdTemplate']);
            }
        }

        if (!$options->getBeginMarker()) {
            $options->setBeginMarker($defaultOptions['email']['beginMarker']);
        }
        if (!$options->getEndMarker()) {
            $options->setEndMarker($defaultOptions['email']['endMarker']);
        }

        if (!$options->getErrorMessage()) {
            if (!$isCommentMode) {
                $options->setErrorMessage($defaultOptions['email']['errorMessageEntity']);
            } else {
                $options->setErrorMessage($defaultOptions['email']['errorMessageComment']);
            }
        }

        $taskLevelRequired = false;
        $taskLevelLabel = Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_GENERAL_TASK_LEVEL');
    } else {
        $taskLevelRequired = true;
        $taskLevelLabel = Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_GENERAL_TASK_LEVEL') . '*';
    }

    $tabs = array_merge($tabs, [
      ['DIV' => 'tab-2', 'TAB' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_' . strtoupper($exchType)), 'TITLE' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_' . strtoupper($exchType) . '_TAB_TITLE')],
    ]);

    switch ($exchType) {
        case ExchangeTypeTable::TYPE_DATABASE:
            $imp = new Import();
            $dbms = $imp->getCapabilities()['supportedDBMS'];
            if ($systemCode != 'sap') {
                $tabs = array_merge($tabs, [
                  ['DIV' => 'tab-3', 'TAB' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_PROJECTS'), 'TITLE' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_PROJECTS_TAB_TITLE')],
                  ['DIV' => 'tab-4', 'TAB' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_ENTITY_TYPES'), 'TITLE' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_ENTITY_TYPES_TAB_TITLE')],
                  ['DIV' => 'tab-5', 'TAB' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_ENTITY_ATTRIBUTES'), 'TITLE' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_ENTITY_ATTRIBUTES_TAB_TITLE')],
                  ['DIV' => 'tab-7', 'TAB' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_USERS'), 'TITLE' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_USERS_TAB_TITLE')],
                  ['DIV' => 'tab-8', 'TAB' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_RESPONSIBLE'), 'TITLE' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_RESPONSIBLE_TAB_TITLE')]
                ]);
            } else {
                $tabs = array_merge($tabs, [
                  ['DIV' => 'tab-3', 'TAB' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_PROJECTS'), 'TITLE' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_PROJECTS_TAB_TITLE')],
                  ['DIV' => 'tab-4', 'TAB' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_PROPS_AND_VALUES'), 'TITLE' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_PROPS_AND_VALUES_TAB_TITLE')],
                  ['DIV' => 'tab-7', 'TAB' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_USERS'), 'TITLE' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_USERS_TAB_TITLE')],
                  ['DIV' => 'tab-8', 'TAB' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_RESPONSIBLE'), 'TITLE' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_RESPONSIBLE_TAB_TITLE')]
                ]);
            }
            break;
        case ExchangeTypeTable::TYPE_FILES:
            $tabs = array_merge($tabs, [
              ['DIV' => 'tab-5', 'TAB' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_FILE_DEF_VALUES'), 'TITLE' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_FILE_DEF_VALUES_TAB_TITLE')],
              ['DIV' => 'tab-6', 'TAB' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_ENTITY_ATTRIBUTES'), 'TITLE' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_ENTITY_ATTRIBUTES_TAB_TITLE')],
              ['DIV' => 'tab-8', 'TAB' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_PROP_VALUES'), 'TITLE' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_PROP_VALUES_TAB_TITLE')]
            ]);
            break;
        case ExchangeTypeTable::TYPE_API:
            $tabs = array_merge($tabs, [
              ['DIV' => 'tab-3', 'TAB' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_PROJECTS'), 'TITLE' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_PROJECTS_TAB_TITLE')],
              ['DIV' => 'tab-4', 'TAB' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_ENTITY_TYPES'), 'TITLE' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_ENTITY_TYPES_TAB_TITLE')],
              ['DIV' => 'tab-5', 'TAB' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_ENTITY_ATTRIBUTES'), 'TITLE' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_ENTITY_ATTRIBUTES_TAB_TITLE')],
              ['DIV' => 'tab-7', 'TAB' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_USERS'), 'TITLE' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_USERS_TAB_TITLE')],
              ['DIV' => 'tab-8', 'TAB' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_RESPONSIBLE'), 'TITLE' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_RESPONSIBLE_TAB_TITLE')]
            ]);
            break;
        case ExchangeTypeTable::TYPE_EMAIL:
            $tabs = array_merge($tabs, [
              ['DIV' => 'tab-6', 'TAB' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_EMAIL_OPTIONS'), 'TITLE' => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_EMAIL_OPTIONS_TAB_TITLE')]
            ]);
            break;
    }
}

$lastOperationTime = $obj->getLastOperationDate() ?? Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_LAST_OPERATION_NONE');

if (!empty($attrTabs)) {
    $attrTabControl = new CAdminTabControl('attrTabControl', $attrTabs);
}

$tabControl = new CAdminTabControl('tabControl', $tabs);

$context = Application::getInstance()->getContext();
$request = $context->getRequest();
$save = trim($request->get('save'));
$apply = trim($request->get('apply'));
$fields = $request->getValues();

if ((!empty($save) || !empty($apply)) && is_array($fields) && !empty($fields)) {
    try {
        $obj->save($fields);
        ImportHelper::automaticUserMapping((int)$sourceId);
        if (!empty($save)) {
            LocalRedirect($backUrl);
        } else {
            LocalRedirect($_SERVER['PHP_SELF'] . '?ID=' . $obj->getId() . '&lang=' . LANGUAGE_ID . '&mess=ok&' . $tabControl->ActiveTabParam());
        }
    } catch (\InvalidArgumentException $ex) {
        $errorMessage = Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_VALIDATION_ERROR');
    } catch (\Exception $ex) {
        $errorMessage = $ex->getMessage();
    }
}

$APPLICATION->SetTitle(Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_TITLE').' '.$obj->getName());

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
if ($_REQUEST["mess"] == "ok") {
    CAdminMessage::ShowMessage(["MESSAGE" => GetMessage("INTEGRATIONS_OPTIONS_EDIT_DATA_SAVED"), "TYPE" => "OK"]);
}
if ($errorMessage) {
    CAdminMessage::ShowMessage(["MESSAGE" => $errorMessage, "TYPE" => "ERROR"]);
}

?>
    <style type="text/css">
        .main-ui-square-search-item {
            border: 0 !important;
            box-shadow: none !important;
        }
        .main-ui-square-search {
            color:#bfbfbd;
        }
        table.filter-block {
            width: 100%;
        }
        table.filter-block td.first-cell,
        table.filter-block td.third-cell {
            width: 5%;
        }
        table.filter-block td.second-cell {
            width: 90%;
        }
        .conn-status-ok {
            color: #1a820e;
            display: none;
        }
        .conn-status-error {
            color: #9b1f20;
            display: none;
        }
        .adm-detail-content-cell-r textarea {
            width: 100%;
        }
        #validation_summary {
            display: none;
        }
        #validation_summary .adm-info-message-title,
        .multiline-text {
            white-space: pre-line;
        }
        .w-25 {
            width: 25%;
        }
        .w-100 {
            width: 100%;
        }
        .bold-text {
            font-weight: bold;
        }
        .align-left {
            text-align: left;
        }
        .pt-16 {
            padding-top: 16px;
        }
        .pt-8 {
            padding-top: 8px;
        }
        .valign-top {
            vertical-align: top;
        }
        .adm-detail-content-cell-l {
            width: 45%;
        }
        tr.email-options-row td {
            width: 32%;
            padding: 0 8px 0 8px;
        }
        tr.email-options-row .email-options-head {
            text-align: center;
            margin-bottom: 16px;
        }
    </style>
<div id="validation_summary">
    <?php CAdminMessage::ShowMessage(["MESSAGE" => Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_VALIDATION_ERROR'), "TYPE" => "ERROR"]);?>
</div>
<div class="adm-detail-toolbar"><span style="position:absolute;"></span>
	<a href="/bitrix/admin/rns_integrations_options_list.php?lang=ru" class="adm-detail-toolbar-btn" title="Вернуться в список настроек" id="btn_list"><span class="adm-detail-toolbar-btn-l"></span><span class="adm-detail-toolbar-btn-text"><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_LIST_BTN_TITLE')?></span><span class="adm-detail-toolbar-btn-r"></span></a>
<script type="text/javascript"> if(window.BXHotKeys!==undefined) {  BXHotKeys.Add("", "var d=BX(\'btn_list\'); if (d) location.href = d.href;", 8, 'Кнопка для перехода в список', 0);  } </script>
</div>
<div id="settingsform">
  <form method="POST" name="<?= basename(__FILE__, '.php') ?>" action="<?= $APPLICATION->GetCurUri() ?>">
    <?= bitrix_sessid_post() ?>
    <?php
    $tabControl->Begin();
//  tab-1
    $tabControl->BeginNextTab()
    ?>
    <tr>
        <td><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_GENERAL_NAME') ?></td>
        <td>
            <?= InputType('text', 'name', $obj->getName(), false, false, '', 'data-required') ?>
        </td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_GENERAL_SYSTEM') ?></td>
        <td>
            <?= SelectBoxFromArray('externalSystem', $externalSystems, $obj->getSystemId(), '', $ID > 0 ? 'disabled="disabled"' : '') ?>
        </td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_GENERAL_EXCH_TYPE') ?></td>
        <td>
            <?= SelectBoxFromArray('exchangeType', $exchangeTypes, $obj->getExchangeTypeId(), '', $ID > 0 ? 'disabled="disabled"' : '') ?>
        </td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_GENERAL_EXCH_DIR') ?></td>
        <td>
            <?= SelectBoxFromArray('exchangeDirection', $directions, $obj->getDirection()) ?>
        </td>
    </tr>
    <?php if ($exchType != ExchangeTypeTable::TYPE_FILES): ?>
    <tr>
        <td><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_GENERAL_SCHEDULE') ?></td>
        <td>
            <?= InputType('number', 'schedule', $obj->getSchedule(), false) ?>
        </td>
    </tr>
    <?php endif; ?>
    <?php if ($exchType != ExchangeTypeTable::TYPE_EMAIL): ?>
    <tr>
        <td>
            <?= $taskLevelLabel ?>
        </td>
        <td>
            <?= InputType('number', 'options[taskLevel]', $options->getTaskLevel(), false, false, false, 'step="1"' . ($taskLevelRequired ? ' data-required' : '')) ?>
        </td>
    </tr>
    <?php endif; ?>
    <tr>
        <td>
            <label for="description"><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_GENERAL_DESCRIPTION') ?></label>
        </td>
        <td>
            <textarea name="description" rows="3"><?= $obj->getDescription()?></textarea>
        </td>
    </tr>
    <tr>
        <td>
            <label for="active"><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_GENERAL_ACTIVE') ?></label>
        </td>
        <td>
            <?= InputType('checkbox', 'active', true, htmlspecialcharsbx($obj->isActive())) ?>
        </td>
    </tr>
  <tr>
      <td>
          <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_LAST_OPERATION_TIME') ?>
      </td>
      <td>
          <?= $lastOperationTime ?>
      </td>
  </tr>
    <?php if ($ID > 0): ?>
<!-- tab-2-->
    <?php $tabControl->BeginNextTab() ?>
    <?php if($exchType == ExchangeTypeTable::TYPE_DATABASE): ?>
    <!-- Настройки DB -->
    <tr>
        <td><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_DATABASE_TYPE') ?></td>
        <td>
            <?= SelectBoxFromArray('options[database][type]', $dbms, $options->getType(), '', 'id="options_database_type"') ?>
        </td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_DATABASE_HOSTNAME') ?></td>
        <td>
            <?= InputType('text', 'options[database][hostName]', $options->getHostName(), false, false, '', 'id="options_database_hostName" data-required') ?>
        </td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_DATABASE_PORT') ?></td>
        <td>
            <?= InputType('text', 'options[database][port]', $options->getPort(), false, false, '', 'id="options_database_port" data-required') ?>
        </td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_DATABASE_DATABSENAME') ?></td>
        <td>
            <?= InputType('text', 'options[database][databaseName]', $options->getDatabaseName(), false, false, '', 'id="options_database_databaseName" data-required') ?>
        </td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_DATABASE_USERNAME') ?></td>
        <td>
            <?= InputType('text', 'options[database][userName]', $options->getUserName(), false, false, '', 'id="options_database_userName" data-required') ?>
        </td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_DATABASE_PASSWORD') ?></td>
        <td>
            <?= InputType('password', 'options[database][password]', $options->getPassword(), false, false, '', 'id="options_database_password" data-required') ?>
        </td>
    </tr>
    <tr>
        <td>
            <input type="button" id="test_db_connection" class="adm-btn-green" value="<?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_TEST_CONNECTION') ?>">
            <span class="conn-status-ok" id="conn_status_ok"><?= Loc::getMessage('INTEGRATIONS_ALERT_SUCCESS')?></span>
            <span class="conn-status-error" id="conn_status_error"><?= Loc::getMessage('INTEGRATIONS_ALERT_ERROR')?></span>
        </td>
    </tr>
    <?php endif; ?>
    <?php if ($exchType == ExchangeTypeTable::TYPE_API): ?>
    <!-- Настройки API -->
    <tr>
        <td><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_API_ENDPOINT') ?></td>
        <td>
            <?= InputType('text', 'options[api][endpoint]', $options->getEndpoint(), false) ?>
        </td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_API_USERNAME') ?></td>
        <td>
            <?= InputType('text', 'options[api][userName]', $options->getUserName(), false) ?>
        </td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_API_PASSWORD') ?></td>
        <td>
            <?= InputType('password', 'options[api][password]', $options->getPassword(), false) ?>
        </td>
    </tr>
    <?php endif; ?>
    <?php if ($exchType == ExchangeTypeTable::TYPE_EMAIL) : ?>
    <!-- Настройки E-mail -->
    <tr>
        <td><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_EMAIL_MAILBOX') ?></td>
        <td>
            <?= SelectBoxFromArray('options[email][mailboxId]', $mailboxOptions, $options->getMailboxId()) ?>
        </td>
    </tr>
    <?php endif; ?>
    <?php if ($exchType == ExchangeTypeTable::TYPE_FILES) : ?>
    <!-- Настройки импорта/экспорта файлов -->
    <?php if ($converterNeeded): ?>
    <tr>
        <td><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_FILE_CONV_URL') ?></td>
        <td>
            <?= InputType('text', 'options[files][converterUrl]', $options->getConverterUrl(), false, false, '', 'id="options_files_converterUrl" data-required') ?>
        </td>
    </tr>
    <?php endif; ?>
    <tr>
        <td><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_FILE_MAX_SIZE') ?></td>
        <td>
            <?= InputType('number', 'options[files][fileMaxSize]', $options->getFileMaxSize(), false, false, '', 'data-required') ?>
        </td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_FILE_MAX_TASKS') ?></td>
        <td>
            <?= InputType('number', 'options[files][taskMaxCount]', $options->getTaskMaxCount(), false, false, '', 'data-required') ?>
        </td>
    </tr>
    <?php if ($converterNeeded): ?>
    <tr>
        <td>
            <input type="button" id="test_svc_connection" class="adm-btn-green" value="<?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_TEST_CONNECTION') ?>">
            <span class="conn-status-ok" id="conn_status_ok"><?= Loc::getMessage('INTEGRATIONS_ALERT_SUCCESS')?></span>
            <span class="conn-status-error" id="conn_status_error"><?= Loc::getMessage('INTEGRATIONS_ALERT_ERROR')?></span>
        </td>
    </tr>
    <?php endif; ?>
    <?php endif; ?>

    <?php if($exchType != ExchangeTypeTable::TYPE_FILES && $exchType != ExchangeTypeTable::TYPE_EMAIL): ?>
<!-- tab-3-->
    <?php $tabControl->BeginNextTab() ?>
    <!-- Экземпляры проектов -->
    <tr>
        <td colspan="2">
            <table class="filter-block">
                <tr>
                    <td class="first-cell">Проекты</td>
                    <td class="second-cell">
                        <div data-name="SELECT_MULTIPLE" id="filter_projects"
                             class="main-ui-filter-wield-with-label main-ui-filter-date-group main-ui-control-field-group">
                            <div data-name="SELECT_MULTIPLE"
                                 data-items='<?= Json::encode($projectFilterItems); ?>'
                                 data-params='<?= Json::encode(['isMulti' => true]); ?>'
                                 id="select2" class="main-ui-control main-ui-multi-select">

                                <span class="main-ui-square-container"></span>
                                <span class="main-ui-square-search"><input type="text" tabindex="2" class="main-ui-square-search-item"><?= GetMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_PROJECTS_SEARCH_HELPER')?></span>
                                <span class="main-ui-hide main-ui-control-value-delete"><span class="main-ui-control-value-delete-item"></span></span>
                            </div>
                        </div>
                    </td>
                    <td class="third-cell">
                        <input type="button" id="filter_projects_apply" value="<?= GetMessage("admin_lib_edit_apply")?>">
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="adm-detail-content-cell-l">
            <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_DEF_PRJ') ?>
        </td>
        <td class="adm-detail-content-cell-r">
            <?= SelectBoxFromArray('mapping[projectMap][defaultEntityId]', $localProjects, $projectMapping->getDefaultEntityId()) ?>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <table class="adm-list-table">
                <thead>
                <tr class="adm-list-table-header">
                    <td class="adm-list-table-cell">
                        <div class="adm-list-table-cell-inner">
                            <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_SRC_PRJ') ?>
                        </div>
                    </td>
                    <td class="adm-list-table-cell">
                        <div class="adm-list-table-cell-inner">
                            <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_DEST_PRJ') ?>
                        </div>
                    </td>
                </tr>
                </thead>
                <tbody id="projectMapRows">
                <?php foreach ($projectMapping->getItems() as $i => $item):?>
                <tr>
                    <td class="adm-list-table-cell">
                        <?= SelectBoxFromArray("mapping[projectMap][items][{$i}][externalEntityId]", $externalProjects, $item->getExternalEntityId()) ?>
                    </td>
                    <td class="adm-list-table-cell">
                        <?= SelectBoxFromArray("mapping[projectMap][items][{$i}][internalEntityId]", $localProjects, $item->getInternalEntityId(), $noneSelected,  'onchange="refillSelects(\'projectMap\', \'internalEntityId\', ' . $i . ')"') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </td>
    </tr>
<!-- tab-4-->
    <?php if ($systemCode != 'sap'): ?>
    <?php $tabControl->BeginNextTab() ?>
    <!-- Типы сущности -->
    <tr>
        <td class="adm-detail-content-cell-l">
            <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_ENT_DEF_TYPE') ?>
        </td>
        <td class="adm-detail-content-cell-r">
            <?= SelectBoxFromArray("mapping[entityTypeMap][defaultTypeId]", $entityTypes, $entityTypeMapping->getDefaultTypeId(), '', 'id="mapping_entityTypeMap_defaultTypeId" onchange="entityTypeMapDefEntityTypeChange()"') ?>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <table class="adm-list-table">
                <thead>
                <tr class="adm-list-table-header">
                    <td class="adm-list-table-cell">
                        <div class="adm-list-table-cell-inner">
                            <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_EXT_ENT_TYPE') ?>
                        </div>
                    </td>
                    <td class="adm-list-table-cell">
                        <div class="adm-list-table-cell-inner">
                            <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_INT_ENT_TYPE') ?>
                        </div>
                    </td>
                </tr>
                </thead>
                <tbody id="entityTypeMapRows">
                <?php foreach ($entityTypeMapping->getItems() as $i => $mapItem): ?>
                    <tr>
                        <td class="adm-list-table-cell">
                            <?= SelectBoxFromArray("mapping[entityTypeMap][items][{$i}][externalTypeId]", $externalEntityTypes, $mapItem->getExternalTypeId(), $noneSelected, 'onchange="refillSelects(\'entityTypeMap\', \'externalTypeId\', ' . $i . ')"') ?>
                        </td>
                        <td class="adm-list-table-cell">
                            <?= SelectBoxFromArray("mapping[entityTypeMap][items][{$i}][internalTypeId]", $entityTypes, $mapItem->getInternalTypeId(), $noneSelected, 'onchange="refillSelects(\'entityTypeMap\', \'internalTypeId\', ' . $i . ')"') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </td>
    </tr>
    <?php endif; ?>
    <?php endif; ?>
<!-- tab-5-->
    <?php $tabControl->BeginNextTab() ?>
    <?php if ($exchType == ExchangeTypeTable::TYPE_FILES): ?>
        <!-- Значения по умолчанию -->
        <tr>
            <td class="admdetailcontentcelll">
                <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_ENT_DEF_TYPE') ?>
            </td>
            <td class="admdetailcontentcellr">
                <?= SelectBoxFromArray("mapping[entityTypeMap][defaultTypeId]", $entityTypes, $entityTypeMapping->getDefaultTypeId(), '', 'id="mapping_entityTypeMap_defaultTypeId" onchange="entityTypeMapDefEntityTypeChange()"') ?>
            </td>
        </tr>
        <tr>
            <td class="admdetailcontentcelll">
                <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_ENT_DEF_STATUS') ?>
            </td>
            <td class="admdetailcontentcellr">
                <?= SelectBoxFromArray("mapping[entityStatusMap][defaultStatusId]", [], '', '', 'id="mapping_entityStatusMap_defaultStatusId" data-value="' . $entityStatusMapping->getDefaultStatusId() . '"') ?>
            </td>
        </tr>
    <?php endif; ?>
    <!-- Атрибуты сущности -->
    <?php if ($exchType != ExchangeTypeTable::TYPE_FILES): ?>
    <?php if ($exchType != ExchangeTypeTable::TYPE_EMAIL): ?>
    <?php if ($systemCode != 'sap'): ?>
    <?php if ($attrTabControl): ?>
    <tr>
        <td>
            <?php $statusRowIndex = 0; ?>
            <?php $propertyRowIndex = 0; ?>
            <?php $attrTabControl->Begin(); ?>
            <?php foreach ($externalEntityTypes['REFERENCE_ID'] as $idx => $typeId) : ?>
            <?= $attrTabControl->BeginNextTab() ?>
            <tr class="heading">
                <td><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_ENTITY_STATUSES_TAB_TITLE')?></td>
            </tr>
            <tr>
                <td>
                    <table class="adm-list-table">
                        <thead>
                        <tr class="adm-list-table-header">
                            <td class="adm-list-table-cell">
                                <div class="adm-list-table-cell-inner">
                                    <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_EXT_ENT_STATUS') ?>
                                </div>
                            </td>
                            <td class="adm-list-table-cell">
                                <div class="adm-list-table-cell-inner">
                                    <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_INT_ENT_TYPE') ?>
                                </div>
                            </td>
                            <td class="adm-list-table-cell">
                                <div class="adm-list-table-cell-inner">
                                    <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_INT_ENT_STATUS') ?>
                                </div>
                            </td>
                        </tr>
                        </thead>
                        <tbody id="entityStatusMapRows">
                        <?php foreach ($entityStatusMapping->getItemsByExternalTypeId($typeId) as $mapItem): ?>
                            <?php if (!empty($externalStatusNames[$mapItem->getExternalStatusId()])):?>
                            <tr>
                                <td class="adm-list-table-cell">
                                    <?= $externalStatusNames[$mapItem->getExternalStatusId()] ?>
                                    <input type="hidden" name="mapping[entityStatusMap][items][<?= $statusRowIndex?>][externalTypeId]"
                                           value="<?= $typeId?>">
                                    <input type="hidden" name="mapping[entityStatusMap][items][<?= $statusRowIndex?>][externalStatusId]"
                                           value="<?= $mapItem->getExternalStatusId()?>">
                                </td>
                                <td class="adm-list-table-cell">
                                    <?= $internalTypeNames[$mapItem->getInternalTypeId()] ?>
                                    <input type="hidden" name="mapping[entityStatusMap][items][<?= $statusRowIndex?>][internalTypeId]"
                                           value="<?= $mapItem->getInternalTypeId()?>">
                                </td>
                                <td class="adm-list-table-cell">
                                    <?= SelectBoxFromArray("mapping[entityStatusMap][items][{$statusRowIndex}][internalStatusId]",
                                      $industrialOfficeInstalled ? $entityStatuses[$mapItem->getInternalTypeId()] : $entityStatuses['TASK'],
                                      $mapItem->getInternalStatusId(), $noneSelected) ?>
                                </td>
                            </tr>
                            <?php endif;?>
                            <?php $statusRowIndex++;?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr class="heading">
                <td><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_ENTITY_PROPS_TAB_TITLE')?></td>
            </tr>
            <tr>
                <td>
                    <table class="adm-list-table">
                        <thead>
                        <tr class="adm-list-table-header">
                            <td class="adm-list-table-cell">
                                <div class="adm-list-table-cell-inner">
                                    <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_EXT_PROP') ?>
                                </div>
                            </td>
                            <td class="adm-list-table-cell">
                                <div class="adm-list-table-cell-inner">
                                    <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_INT_ENT_TYPE') ?>
                                </div>
                            </td>
                            <td class="adm-list-table-cell">
                                <div class="adm-list-table-cell-inner">
                                    <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_INT_PROP') ?>
                                </div>
                            </td>
                        </tr>
                        </thead>
                        <tbody id="entityPropertyMapRows">
                        <?php foreach ($mapping->getEntityPropertyMap()->getItemsByExternalTypeId($typeId) as $mapItem): ?>
                            <?php if (!empty($externalPropNames[$mapItem->getExternalPropertyId()])):?>
                            <tr>
                                <td class="adm-list-table-cell">
                                    <?= $externalPropNames[$mapItem->getExternalPropertyId()] ?>
                                    <input type="hidden" name="mapping[entityPropertyMap][items][<?= $propertyRowIndex?>][externalTypeId]"
                                           value="<?= $typeId?>">
                                    <input type="hidden" name="mapping[entityPropertyMap][items][<?= $propertyRowIndex?>][externalPropertyId]"
                                           value="<?= $mapItem->getExternalPropertyId()?>">
                                </td>
                                <td class="adm-list-table-cell">
                                    <?= $internalTypeNames[$mapItem->getInternalTypeId()] ?>
                                    <input type="hidden" name="mapping[entityPropertyMap][items][<?= $propertyRowIndex?>][internalTypeId]"
                                           value="<?= $mapItem->getInternalTypeId()?>">
                                </td>
                                <td class="adm-list-table-cell">
                                    <?= SelectBoxFromArray("mapping[entityPropertyMap][items][{$propertyRowIndex}][internalPropertyId]",
                                      $entityProps, $mapItem->getInternalPropertyId(), $noneSelected, 'onchange="refillSelects(\'entityPropertyMap\', \'internalPropertyId\', ' . $i . ')"') ?>
                                </td>
                            </tr>
                            <?php endif;?>
                            <?php $propertyRowIndex++;?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php $attrTabControl->End();?>
        </td>
    </tr>
    <?php endif;?>
    <?php endif;?>
    <?php endif;?>
    <?php if ($systemCode == 'sap'): ?>
    <tr>
        <td class="adm-detail-content-cell-l">
            <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_ENT_DEF_TYPE') ?>
        </td>
        <td class="adm-detail-content-cell-r">
            <?= SelectBoxFromArray("mapping[entityTypeMap][defaultTypeId]", $entityTypes, $entityTypeMapping->getDefaultTypeId(), '', 'id="mapping_entityTypeMap_defaultTypeId" onchange="entityTypeMapDefEntityTypeChange()"') ?>
        </td>
    </tr>
    <tr>
        <td class="adm-detail-content-cell-l">
            <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_ENT_DEF_STATUS') ?>
        </td>
        <td class="adm-detail-content-cell-r">
            <?= SelectBoxFromArray("mapping[entityStatusMap][defaultStatusId]", [], '', '', 'id="mapping_entityStatusMap_defaultStatusId" data-value="' . $entityStatusMapping->getDefaultStatusId() . '"') ?>
        </td>
    </tr>
    <tr>
        <td class="adm-detail-content-cell-l">
            <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_DEF_PRIORITY') ?>
        </td>
        <td class="adm-detail-content-cell-r">
            <?= SelectBoxFromArray('mapping[entityPropertyMap][defaultPriority]', $priorities, $propertyMapping->getDefaultPriority()) ?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_ENTITY_PROPS_TAB_TITLE')?></td>
    </tr>
    <tr>
        <td colspan="2">
            <table class="adm-list-table">
                <thead>
                <tr class="adm-list-table-header">
                    <td class="adm-list-table-cell">
                        <div class="adm-list-table-cell-inner">
                            <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_EXT_PROP') ?>
                        </div>
                    </td>
                    <td class="adm-list-table-cell">
                        <div class="adm-list-table-cell-inner">
                            <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_INT_PROP') ?>
                        </div>
                    </td>
                </tr>
                </thead>
                <tbody id="entityPropertyMapRows">
                <?php
                    $propertyRowIndex = 0;
                    foreach ($mapping->getEntityPropertyMap()->getItemsByExternalTypeId($typeId) as $mapItem):
                    if (!empty($externalPropNames[$mapItem->getExternalPropertyId()])):?>
                    <tr>
                        <td class="adm-list-table-cell">
                            <?= $externalPropNames[$mapItem->getExternalPropertyId()] ?>
                            <input type="hidden" name="mapping[entityPropertyMap][items][<?= $propertyRowIndex?>][externalTypeId]"
                                   value="<?= $typeId?>">
                            <input type="hidden" name="mapping[entityPropertyMap][items][<?= $propertyRowIndex?>][externalPropertyId]"
                                   value="<?= $mapItem->getExternalPropertyId()?>">
                            <input type="hidden" name="mapping[entityPropertyMap][items][<?= $propertyRowIndex?>][internalTypeId]"
                                   value="<?= $mapItem->getInternalTypeId()?>">
                        </td>
                        <td class="adm-list-table-cell">
                            <?= SelectBoxFromArray("mapping[entityPropertyMap][items][{$propertyRowIndex}][internalPropertyId]",
                              $entityProps, $mapItem->getInternalPropertyId(), $noneSelected, 'onchange="refillSelects(\'entityPropertyMap\', \'internalPropertyId\', ' . $i . ')"') ?>
                        </td>
                    </tr>
                    <?php endif;?>
                    <?php $propertyRowIndex++;?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </td>
    </tr>
    <?php endif;?>
    <?php if ($exchType == ExchangeTypeTable::TYPE_EMAIL) : ?>
        <?php if ($exchDirection == IntegrationOptionsTable::DIRECTION_IMPORT): ?>
        <!--  Параметры обработки почты-->
        <?php if (!$isCommentMode): ?>
        <tr class="email-options-row">
            <td class="align-left valign-top">
                <div class="bold-text email-options-head">
                    <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_EMAIL_COMMON_PARAMS')?>
                </div>
                <table>
                    <tr>
                        <td class="adm-detail-content-cell-l">
                            <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_EMAIL_BOUNDS_BEGIN') ?>
                        </td>
                        <td class="adm-detail-content-cell-r">
                            <?= InputType('text', 'options[email][beginMarker]', $options->getBeginMarker(), false, false, '', 'data-required') ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="adm-detail-content-cell-l">
                            <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_EMAIL_BOUNDS_END') ?>
                        </td>
                        <td class="adm-detail-content-cell-r">
                            <?= InputType('text', 'options[email][endMarker]', $options->getEndMarker(), false, false, '', 'data-required') ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="adm-detail-content-cell-l">
                            <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_EMAIL_ERR_MSG_ENTITY') ?>
                        </td>
                        <td class="adm-detail-content-cell-r">
                            <textarea name="options[email][errorMessage]" rows="3" data-required><?= $options->getErrorMessage()?></textarea>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="align-left valign-top">
                <div class="bold-text email-options-head">
                    <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_EMAIL_REG_EXP_PARTS')?>
                </div>
                <table>
                    <tr>
                        <td class="adm-detail-content-cell-l">title*</td>
                        <td class="adm-detail-content-cell-r">
                            <?= InputType('text', 'options[email][regexpTitle]', $options->getRegexpTitle(), false, false, '', 'data-required') ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="adm-detail-content-cell-l">project*</td>
                        <td class="adm-detail-content-cell-r">
                            <?= InputType('text', 'options[email][regexpProject]', $options->getRegexpProject(), false, false, '', 'data-required') ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="adm-detail-content-cell-l">endDate*</td>
                        <td class="adm-detail-content-cell-r">
                            <?= InputType('text', 'options[email][regexpEndDate]', $options->getRegexpEndDate(), false, false, '', 'data-required') ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="adm-detail-content-cell-l">priority*</td>
                        <td class="adm-detail-content-cell-r">
                            <?= InputType('text', 'options[email][regexpPriority]', $options->getRegexpPriority(), false, false, '', 'data-required') ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="adm-detail-content-cell-l">tags*</td>
                        <td class="adm-detail-content-cell-r">
                            <?= InputType('text', 'options[email][regexpTag]', $options->getRegexpTag(), false, false, '', 'data-required') ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="align-left pt-16">
                <div>
                    <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_EMAIL_SUBJECT_TEMPLATE_LABEL')?>
                </div>
                <div class="pt-8">
                    <?= htmlspecialchars(Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_EMAIL_SUBJECT_TEMPLATE')) ?>
                </div>
                <div class="pt-8">
                    <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_EMAIL_SPECIAL_TAG_LIST')?>
                </div>
            </td>
        </tr>
        <?php endif; ?>
        <?php if ($isCommentMode): ?>
        <tr>
            <td class="adm-detail-content-cell-l w-25">
                <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_EMAIL_TASK_TEMPLATE') ?>
            </td>
            <td class="adm-detail-content-cell-r">
                <?= InputType('text', 'options[email][taskIdTemplate]', $options->getTaskIdTemplate(), false, false, '', 'data-required class="w-100"') ?>
            </td>
        </tr>
        <tr>
            <td class="adm-detail-content-cell-l w-25">
                <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_EMAIL_COMMENT_TEMPLATE') ?>
            </td>
            <td class="adm-detail-content-cell-r">
                <?= InputType('text', 'options[email][commentIdTemplate]', $options->getCommentIdTemplate(), false, false, '', 'data-required class="w-100"') ?>
            </td>
        </tr>
        <tr>
            <td><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_EMAIL_BOUNDS_BEGIN') ?></td>
            <td>
                <?= InputType('text', 'options[email][beginMarker]', $options->getBeginMarker(), false, false, '', 'data-required') ?>
            </td>
        </tr>
        <tr>
            <td><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_EMAIL_BOUNDS_END') ?></td>
            <td>
                <?= InputType('text', 'options[email][endMarker]', $options->getEndMarker(), false, false, '', 'data-required') ?>
            </td>
        </tr>
        <?php endif; ?>
        <?php endif; ?>
        <?php if ($exchDirection == IntegrationOptionsTable::DIRECTION_EXPORT): ?>
        <tr>
            <td class="adm-detail-content-cell-l">
                <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_EMAIL_SUBJECT_ACCEPTED') ?>
            </td>
            <td class="adm-detail-content-cell-r">
                <?= InputType('text', 'options[email][subjectAcceptedTemplate]', $options->getSubjectAcceptedTemplate(), false, false, '', 'data-required class="w-100"') ?>
            </td>
        </tr>
        <tr>
            <td class="adm-detail-content-cell-l">
                <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_EMAIL_ACCEPT_COMMENT') ?>
            </td>
            <td class="adm-detail-content-cell-r">
                <textarea name="options[email][acceptComment]" rows="5" data-required><?= $options->getAcceptComment()?></textarea>
            </td>
        </tr>
        <tr>
            <td class="adm-detail-content-cell-l">
                <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_EMAIL_SUBJECT_DECLINED') ?>
            </td>
            <td class="adm-detail-content-cell-r">
                <?= InputType('text', 'options[email][subjectDeclinedTemplate]', $options->getSubjectDeclinedTemplate(), false, false, '', 'data-required class="w-100"') ?>
            </td>
        </tr>
        <tr>
            <td class="adm-detail-content-cell-l">
                <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_EMAIL_REFUSE_COMMENT') ?>
            </td>
            <td class="adm-detail-content-cell-r">
                <textarea name="options[email][refuseComment]" rows="5" data-required><?= $options->getRefuseComment()?></textarea>
            </td>
        </tr>
        <?php endif;?>
        <?php if ($isCommentMode): ?>
        <tr>
            <td class="adm-detail-content-cell-l">
                <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_EMAIL_ERR_MSG_COMMENT') ?>
            </td>
            <td class="adm-detail-content-cell-r">
                <textarea name="options[email][errorMessage]" rows="3" data-required><?= $options->getErrorMessage()?></textarea>
            </td>
        </tr>
        <?php endif;?>
    <?php endif; ?>
    <?php endif; ?>
<!-- tab-6-->
    <?php if($exchType == ExchangeTypeTable::TYPE_FILES): ?>
    <?php $tabControl->BeginNextTab() ?>
<!-- Атрибуты сущности-->
    <tr class="heading">
        <td><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_ENTITY_PROPS_TAB_TITLE')?></td>
    </tr>
    <tr>
        <td colspan="4">
            <table class="adm-list-table">
                <thead>
                <tr class="adm-list-table-header">
                    <td class="adm-list-table-cell">
                        <div class="adm-list-table-cell-inner">
                            <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_EXT_ENT_TYPE') ?>
                        </div>
                    </td>
                    <td class="adm-list-table-cell">
                        <div class="adm-list-table-cell-inner">
                            <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_EXT_PROP') ?>
                        </div>
                    </td>
                    <td class="adm-list-table-cell">
                        <div class="adm-list-table-cell-inner">
                            <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_INT_ENT_TYPE') ?>
                        </div>
                    </td>
                    <td class="adm-list-table-cell">
                        <div class="adm-list-table-cell-inner">
                            <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_INT_PROP') ?>
                        </div>
                    </td>
                </tr>
                </thead>
                <tbody id="entityPropertyMapRows">
                <?php foreach ($mapping->getEntityPropertyMap()->getItems() as $i => $mapItem): ?>
                    <tr>
                        <td class="adm-list-table-cell">
                            <?= SelectBoxFromArray("mapping[entityPropertyMap][items][{$i}][externalTypeId]", $externalEntityTypes, $mapItem->getExternalTypeId(), $noneSelected) ?>
                        </td>
                        <td class="adm-list-table-cell">
                            <?= SelectBoxFromArray("mapping[entityPropertyMap][items][{$i}][externalPropertyId]", $externalEntityProps, $mapItem->getExternalPropertyId(), $noneSelected) ?>
                        </td>
                        <td class="adm-list-table-cell">
                            <?= SelectBoxFromArray("mapping[entityPropertyMap][items][{$i}][internalTypeId]", $entityTypes, $mapItem->getInternalTypeId(), $noneSelected) ?>
                        </td>
                        <td class="adm-list-table-cell">
                            <?= SelectBoxFromArray("mapping[entityPropertyMap][items][{$i}][internalPropertyId]", $entityProps, $mapItem->getInternalPropertyId(), $noneSelected, 'onchange="refillSelects(\'entityPropertyMap\', \'internalPropertyId\', ' . $i . ')"') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </td>
    </tr>
    <?php endif; ?>
<!-- tab-7-->
    <?php if($exchType != ExchangeTypeTable::TYPE_FILES && $exchType != ExchangeTypeTable::TYPE_EMAIL): ?>
    <?php $tabControl->BeginNextTab() ?>
    <!-- Пользователи -->
    <tr>
        <td class="adm-detail-content-cell-l">
            <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_USER_DEF_EXT_EMAIL') ?>
        </td>
        <td class="adm-detail-content-cell-r">
            <?= InputType('text', 'mapping[userMap][defaultExternalEmail]', $mapping->getUserMap()->getDefaultExternalEmail(), false) ?>
        </td>
        <td colspan="2" class="adm-detail-content-cell-l">
            <label for="mapping[userMap][ignoreAliens]"><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_USER_IGNORE_ALIENS') ?></label>
            <?= InputType('checkbox', 'mapping[userMap][ignoreAliens]', true, htmlspecialcharsbx($mapping->getUserMap()->isIgnoreAliens())) ?>
        </td>
    </tr>
    <tr>
        <td colspan="4">
            <table class="adm-list-table">
                <thead>
                <tr class="adm-list-table-header">
                    <td class="adm-list-table-cell">
                        <div class="adm-list-table-cell-inner">
                            <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_EXT_USER') ?>
                        </div>
                    </td>
                    <td class="adm-list-table-cell">
                        <div class="adm-list-table-cell-inner">
                            <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_INT_USER') ?>
                        </div>
                    </td>
                </tr>
                </thead>
                <tbody id="userMapRows">
                <?php foreach ($mapping->getUserMap()->getItems() as $i => $item):?>
                    <tr>
                        <td class="adm-list-table-cell">
                            <?= SelectBoxFromArray("mapping[userMap][items][{$i}][externalId]", $externalUsers, $item->getExternalId()) ?>
                        </td>
                        <td class="adm-list-table-cell">
                            <?= SelectBoxFromArray("mapping[userMap][items][{$i}][internalId]", $localUsers, $item->getInternalId(), $noneSelected) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </td>
    </tr>
    <?php endif; ?>
<!-- tab-8-->
    <?php if($exchType != ExchangeTypeTable::TYPE_EMAIL): ?>
    <?php $tabControl->BeginNextTab() ?>
    <!-- Ответственные -->
    <?php if($exchType != ExchangeTypeTable::TYPE_FILES): ?>
    <tr>
        <td><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_RESP_DEF_RESP') ?></td>
        <td>
            <?= SelectBoxFromArray('mapping[responsibleSettings][defaultResponsibleId]', $localUsers, $mapping->getResponsibleSettings()->getDefaultResponsibleId()) ?>
        </td>
    </tr>
    <tr>
        <td>
            <label for="active"><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_RESP_EXEC_LOAD') ?></label>
        </td>
        <td>
            <?= InputType('checkbox', 'mapping[responsibleSettings][executorLoading]', true, htmlspecialcharsbx($mapping->getResponsibleSettings()->isExecutorLoading())) ?>
        </td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_RESP_DEF_AUTHOR') ?></td>
        <td>
            <?= SelectBoxFromArray('mapping[responsibleSettings][defaultAuthorId]', $localUsers, $mapping->getResponsibleSettings()->getDefaultAuthorId()) ?>
        </td>
    </tr>
    <tr>
        <td>
            <label for="active"><?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_RESP_AUTHOR_LOAD') ?></label>
        </td>
        <td>
            <?= InputType('checkbox', 'mapping[responsibleSettings][authorLoading]', true, htmlspecialcharsbx($mapping->getResponsibleSettings()->isAuthorLoading())) ?>
        </td>
    </tr>
    <?php endif; ?>
    <tr>
        <td>
            <?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_MAP_RESP_DEADLINE') ?>
        </td>
        <td>
            <?= InputType('number', 'mapping[responsibleSettings][defaultDeadlineDays]', $mapping->getResponsibleSettings()->getDefaultDeadlineDays(), false, false, false, 'step="1"') ?>
        </td>
    </tr>
    <?php endif; ?>
    <?php endif; ?>
    <?php
    $tabControl->Buttons();
    $hkInst = CHotKeys::getInstance();
    echo '<input'.($aParams["disabled"] === true? " disabled":"").' type="submit" name="save" value="'.GetMessage("admin_lib_edit_save").'" title="'.GetMessage("admin_lib_edit_save_title").$hkInst->GetTitle("Edit_Save_Button").'" class="adm-btn-save" id="btn_save" />';
    echo $hkInst->PrintJSExecs($hkInst->GetCodeByClassName("Edit_Save_Button"));
    echo '<input'.($aParams["disabled"] === true? " disabled":"").' type="submit" name="apply" value="'.GetMessage("admin_lib_edit_apply").'" title="'.GetMessage("admin_lib_edit_apply_title").$hkInst->GetTitle("Edit_Apply_Button").'" id="btn_apply" />';
    echo $hkInst->PrintJSExecs($hkInst->GetCodeByClassName("Edit_Apply_Button"));
    echo '<input type="reset" name="reset" value="' . Loc::getMessage('MAIN_RESET'). '">';
    $tabControl->End();
    ?>
  </form>
</div>
<?php if ($ID > 0): ?>
<script type="text/javascript">

    var statusCache = <?= $entityStatusOptions ?>;

    function updateStatusListByIds(typesSelId, statusesSelId, selectVal) {
      var typesSelect = BX(typesSelId);
      var statusesSelect = BX(statusesSelId);
      if (!typesSelect || !statusesSelect) return;
      var typeCode = typesSelect.value;
      var list = statusCache[typeCode].REFERENCE_ID;
      if (list) {
        BX.selectUtils.deleteAllOptions(statusesSelect);
        BX.selectUtils.addNewOption(statusesSelect, null, '<?=$noneSelected?>');
        list.forEach(function (item, index) {
          BX.selectUtils.addNewOption(statusesSelect, item, statusCache[typeCode].REFERENCE[index]);
        });
        if (selectVal) {
          BX.selectUtils.selectOption(statusesSelect, statusesSelect.getAttribute('data-value'));
        }
      }
    }

    function updateStatusList(idx, selectVal) {
      var typesSelId = `mapping[entityStatusMap][items][${idx}][internalTypeId]`;
      var statusesSelId = 'mapping_entityStatusMap_items_' + idx + '_internalStatusId';
      updateStatusListByIds(typesSelId, statusesSelId, selectVal);
    }

    function entityStatusMapEntityTypeChange(idx) {
      updateStatusList(idx);
    }

    function updateAllStatusLists() {
      var idx = 0;
      for (idx = 0;;idx++) {
        var typesSelect = BX(`mapping[entityStatusMap][items][${idx}][internalTypeId]`);
        if (!typesSelect) break;
        updateStatusList(idx, true);
      }
      updateStatusListByIds('mapping_entityTypeMap_defaultTypeId', 'mapping_entityStatusMap_defaultStatusId', true);
    }

    function entityTypeMapDefEntityTypeChange() {
      updateStatusListByIds('mapping_entityTypeMap_defaultTypeId', 'mapping_entityStatusMap_defaultStatusId');
    }

    function optionUsed(value, mapping, property, excludeIndex, scopeProperty) {
      var currVal = null;
      if (scopeProperty) {
        if (Array.isArray(scopeProperty)) {
          currVal = [];
          scopeProperty.forEach(function(item) {
            var elScopeCurrent = BX(`mapping[${mapping}][items][${excludeIndex}][${item}]`);
            if (elScopeCurrent) currVal.push({prop: item, val: elScopeCurrent.value});
          });
        } else {
          var elScopeCurrent = BX(`mapping[${mapping}][items][${excludeIndex}][${scopeProperty}]`);
          if (elScopeCurrent) currVal = elScopeCurrent.value;
        }
      }
      for (var i = 0;; i++) {
        var el = BX(`mapping[${mapping}][items][${i}][${property}]`);
        if (!el) break;
        if (scopeProperty) {
          if (currVal && Array.isArray(currVal)) {
            var scoped = true;
            currVal.forEach(function(item) {
              var elScope = BX(`mapping[${mapping}][items][${i}][${item.prop}]`);
              if (elScope.value !== item.val) scoped = false;
            });
            if (!scoped) continue;
          } else {
            var elScope = BX(`mapping[${mapping}][items][${i}][${scopeProperty}]`);
            if (elScope.value !== currVal) continue;
          }
        }
        if (i !== excludeIndex && el.value === value) return true;
      }
      return false;
    }

    function filterOptions(options, mapping, property, index, scopeProperty) {
      var result = [];
      result.push({id: '', name: '<?=$noneSelected?>'});
      options.REFERENCE_ID.forEach(function(item, idx) {
        if (!optionUsed(item, mapping, property, index, scopeProperty)) {
          result.push({id: item, name: options.REFERENCE[idx]});
        }
      });
      return result;
    }

    function getOptions(mapping, property, index) {
      var options;
      var scopeProperty = null;
      switch (mapping) {
        case 'projectMap':
          options = <?= json_encode($localProjects, JSON_UNESCAPED_UNICODE)?>;
          break;
        case 'entityTypeMap':
          switch (property) {
            case 'externalProjectId':
              options = <?= json_encode($externalProjects, JSON_UNESCAPED_UNICODE)?>;
              break;
            case 'externalTypeId':
              options = <?= json_encode($externalEntityTypes, JSON_UNESCAPED_UNICODE)?>;
              scopeProperty = 'externalProjectId';
              break;
            default:
              options = <?= json_encode($entityTypes, JSON_UNESCAPED_UNICODE)?>;
              scopeProperty = 'externalProjectId';
              break;
          }
          break;
        case 'entityStatusMap':
          switch (property) {
            case 'externalProjectId':
              options = <?= json_encode($externalProjects, JSON_UNESCAPED_UNICODE)?>;
              break;
          }
          break;
        case 'entityPropertyMap':
          options = <?= json_encode($entityProps, JSON_UNESCAPED_UNICODE)?>;
          <?php if ($exchType != ExchangeTypeTable::TYPE_FILES): ?>
          scopeProperty = ['externalProjectId', 'externalTypeId'];
        <?php endif; ?>
          break;

      }
      return filterOptions(options, mapping, property, index, scopeProperty);
    }

    function getOptionsHtml(mapping, property, index) {
      var options = getOptions(mapping, property, index);
      var html = [];
      options.forEach(function(item) {
        html.push(`<option value="${item.id}">${item.name}</option>`);
      });
      return html.join('');
    }

    function refillSelects(mapping, property, index) {
      return;
      for (var i = 0;; i++) {
        var el = $(`#mapping[${mapping}][items][${i}][${property}]`);
        if (!el) break;
        if (i === index) continue;
        var options = getOptions(mapping, property, i);
        var saveVal = el.value;
        BX.selectUtils.deleteAllOptions(el);
        options.forEach(function(item) {
          BX.selectUtils.addNewOption(el, item.id, item.name);
        });
        if (saveVal) BX.selectUtils.selectOption(el, saveVal);
      }
    }

    function initializeFilter() {
      var filterId = 'filter_projects';
      var filter = BX(filterId),
        submit = BX(filterId + '_apply');

      BX.bind(submit, 'click', function () {
        var fields = BX.findChildren(filter, {
          attribute: 'data-name',
          className: 'main-ui-control'
        }, true);

        var element = fields[0];
        //var projectMaxCount = 3;
        var projects = JSON.parse(element.getAttribute('data-value'));
        //if (projects.length > projectMaxCount) {
        //  if (!confirm('<?php //= Loc::getMessage("INTEGRATIONS_OPTIONS_EDIT_MAX_PRJ_COUNT_WARN")?>//')) {
        //    return;
        //  }
        //}
        window.location.href = '<?= $_SERVER['PHP_SELF'] . '?ID=' . $ID . '&lang=' . LANGUAGE_ID . '&projects='?>' +
          projects.map((item) => {
            return item.VALUE;
          }).join(',') + '&tabControl_active_tab=tab-3';
      });

      var projects = <?= json_encode($externalProjects, JSON_UNESCAPED_UNICODE)?>;
      var html = [];
      var len = projects.REFERENCE_ID.length;
      var value = [];
      for (var i = 0; i < len; i++) {
        value.push({NAME: projects.REFERENCE[i], VALUE: projects.REFERENCE_ID[i]});
        html.push(`<span class="main-ui-square" data-item="{&quot;NAME&quot;:&quot;${projects.REFERENCE[i]}&quot;,&quot;VALUE&quot;:&quot;${projects.REFERENCE_ID[i]}&quot;}"><span class="main-ui-square-item">${projects.REFERENCE[i]}</span><span class="main-ui-item-icon main-ui-square-delete"></span></span>`);
      }
      var cont = $('#filter_projects').find('.main-ui-square-container');
      cont.html(html.join(''));
      cont = $('#select2');
      cont.attr('data-value', JSON.stringify(value));
    }

   function testDbConnection() {
     var data = {
       type: BX('options_database_type').value,
       host: BX('options_database_hostName').value,
       port: BX('options_database_port').value,
       dbname: BX('options_database_databaseName').value,
       username: BX('options_database_userName').value,
       password: BX('options_database_password').value,
     };
     $('#conn_status_ok').hide();
     $('#conn_status_error').hide();

     BX.ajax.runAction('rns:integrations.api.entity.testDbConnection', {data: data})
       .then(function (response) {
         if (response.status === 'success') {
           if (response.data) {
             $('#conn_status_ok').show();
           } else {
             $('#conn_status_error').show();
           }
         }
       }, function (result) {
         $('#conn_status_error').show();
       });
   }

   function testConverterConnection() {
     var data = {
       url: BX('options_files_converterUrl').value
     };
     $('#conn_status_ok').hide();
     $('#conn_status_error').hide();
     BX.ajax.runAction('rns:integrations.api.entity.testConverterConnection', {data: data})
       .then(function (response) {
         if (response.status === 'success') {
           if (response.data) {
             $('#conn_status_ok').show();
           } else {
             $('#conn_status_error').show();
           }
         }
       }, function (result) {
         $('#conn_status_error').show();
       });
   }

   function validate() {
     var isValid = true;
     var summary = $('#validation_summary');
     summary.hide();
     var fields = [];
     var elems = $('[data-required]');
     for (var key in elems) {
       if (isNaN(Number(key))) break;
       var elem = $(elems[key]);
       if (!elem.attr('disabled') && !elem.val()) {
         isValid = false;
         var label = elem.parent().prev('td').html();
         label = label.trim();
         fields.push(label.substr(0, label.length - 1));
       }
     }
     if (!isValid) {
       var msg = '<?= Loc::getMessage('INTEGRATIONS_OPTIONS_EDIT_VALIDATION_ERROR');?>';
       summary.find('.adm-info-message-title').text(msg + '\n' + fields.join('\n'));
       summary.show();
     }
     return isValid;
   }

    function mapping(elmId, leftId, rightId){
        let parent, selects, external, internal;

        parent = $("#"+elmId);
        selects =$(parent).find("select");
        external = leftId; internal = rightId;

        $(parent).on('change', 'select', function () {
            let CurrentSelectId, CurrentSelectValue,
                findId, matchSelectValue, matchSelect,
                itemSelectId, itemSelectValue,
                itemMatchId, itemMatchSelect;

            CurrentSelectId = this.id;
            CurrentSelectValue = this.value;

            if(CurrentSelectId.indexOf(external) !== -1){
                findId = CurrentSelectId.replace(external, internal);
                matchSelect = document.getElementById(findId);
                matchSelectValue = matchSelect ? matchSelect.value : ""

                if(matchSelectValue === ""){
                    $.each(selects, function(index, select) {
                        itemSelectId = select.id;
                        itemSelectValue = select.value;
                        if(itemSelectId.indexOf(external) !== -1 && itemSelectValue === CurrentSelectValue){
                            itemMatchId = itemSelectId.replace(external, internal);
                            itemMatchSelect = document.getElementById(itemMatchId);
                            if (itemMatchSelect.value.length > 0){
                                matchSelectValue = itemMatchSelect.value;
                                return false;
                            }
                        }
                    });
                }

                $.each(selects, function(index, select) {
                    itemSelectId = select.id;
                    itemSelectValue = select.value;
                    if(itemSelectId.indexOf(external) !== -1 && itemSelectValue === CurrentSelectValue){
                        itemMatchId = itemSelectId.replace(external, internal);
                        itemMatchSelect = document.getElementById(itemMatchId);
                        itemMatchSelect.value = matchSelectValue;
                    }
                });
            }else if(CurrentSelectId.indexOf(internal) !== -1){
                findId = CurrentSelectId.replace(internal, external);
                matchSelect = document.getElementById(findId);
                matchSelectValue = matchSelect ? matchSelect.value : ""
                $.each(selects, function(index, select) {
                    itemSelectId = select.id;
                    itemSelectValue = select.value;
                    if(itemSelectId.indexOf(external) !== -1 && itemSelectValue === matchSelectValue){
                        itemMatchId = itemSelectId.replace(external, internal);
                        itemMatchSelect = document.getElementById(itemMatchId);
                        itemMatchSelect.value = CurrentSelectValue;
                    }
                });
            }
        });
    }

    BX.ready(function() {
        //updateAllStatusLists();
        updateStatusListByIds('mapping_entityTypeMap_defaultTypeId', 'mapping_entityStatusMap_defaultStatusId', true);

        $('#test_db_connection').click(testDbConnection);
        $('#test_svc_connection').click(testConverterConnection);

        $('#btn_save').click(validate);
        $('#btn_apply').click(validate);
        <?php if ($exchType == ExchangeTypeTable::TYPE_DATABASE):?>
        initializeFilter();
        <?php endif;?>
        mapping("projectMapRows", "externalEntityId", "internalEntityId")
        mapping("entityTypeMapRows", "externalTypeId", "internalTypeId")
        mapping("userMapRows", "externalId", "internalId")
    });
</script>
<?php endif;?>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
