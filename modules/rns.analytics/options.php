<?php /** @noinspection ALL */
defined('B_PROLOG_INCLUDED') || die;

use Bitrix\Main\Localization\Loc,
    Rns\Analytic\Settings,
    Bitrix\Main\Loader,
    Rns\Analytic\Access,
    Bitrix\Main\Config\Option;

global $APPLICATION, $USER;
CJSCore::Init(["jquery"]);
$module_id = 'rns.analytics';
$MOD_RIGHT = $APPLICATION->GetGroupRight($module_id);
$request = $context->getRequest();
$default_option_field_name = 'RNSANALYTICS_DEFAULT_ACTIVE_TIME';

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/options.php');
Loc::loadMessages(__DIR__ . '/install/index.php');

if (!$USER->IsAdmin()) {
    return;
}

if (!Loader::includeModule($module_id)) {
    return;
}

/**
 *
 * Описание логики табов и настроек в табах
 */
$tabs = [
    [
        'DIV' => 'heatmap',
        'TAB' => Loc::getMessage('RNSANALYTICS_OPT_TAB_HEATMAP_NAME'),
        'TITLE' => Loc::getMessage('RNSANALYTICS_OPT_TAB_HEATMAP_TITLE')
    ],
    [
        'DIV' => 'dynamic_url',
        'TAB' => Loc::getMessage('RNSANALYTICS_OPT_TAB_DYNAMIC_URL_NAME'),
        'TITLE' => Loc::getMessage('RNSANALYTICS_OPT_TAB_DYNAMIC_URL_TITLE')
    ],
    [
        "DIV" => "access",
        "TAB" => Loc::getMessage("MAIN_TAB_RIGHTS"),
        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_RIGHTS")
    ],
];

$options['heatmap'] = [
    [
        'RNSANALYTICS_OPT_ACTIVE',
        Loc::getMessage('RNSANALYTICS_OPT_ACTIVE'),
        '',
        ['checkbox']
    ],
    [
        'RNSANALYTICS_OPT_API_URL',
        Loc::getMessage('RNSANALYTICS_OPT_API_URL'),
        '',
        ['text', 70]
    ],
    [
        $default_option_field_name,
        Loc::getMessage('RNSANALYTICS_DEFAULT_ACTIVE_TIME'),
        '',
        ['text', 5]
    ],
    [
        'test_connection' => true
    ]
];
$options['dynamic_url'] = [
    [
            'dynamic_url' => true,
    ]
];
$options['access'] = [
    [
        'access_form' => true
    ]
];

if (check_bitrix_sessid() && (strlen($_POST['save']) > 0 || strlen($_POST['apply']) > 0 || $_GET['RestoreDefaults'] <> '') && $MOD_RIGHT == "W") {
    if ($_GET['RestoreDefaults'] <> '') {
        COption::RemoveOption($module_id);
        $z = CGroup::GetList($v1 = 'id', $v2 = 'asc', ['ACTIVE' => 'Y', 'ADMIN' => 'N']);
        while ($zr = $z->Fetch()) {
            $APPLICATION->DelGroupRight($module_id, [$zr['ID']]);
        }
        LocalRedirect($APPLICATION->GetCurPage() . "?mid=" . $module_id . "&lang=" . LANG);
    } else {
        foreach ($options as $option) {
            __AdmSettingsSaveOptions($module_id, $option);
        }
        $limit = (int)$request->get($default_option_field_name);
        \Bitrix\Main\Config\Option::set($module_id, $default_option_field_name, $limit);
    }

    if(is_array($_POST["RIGHTS"]))
		$postRights = Access::Post2Array($_POST["RIGHTS"]);
    else
        $postRights = [];
    if (count($postRights) > 0){
        $postRights = serialize($postRights);

        Option::set($module_id,Access::OPTION_FIELD_NAME, $postRights);
    }else{
        $postRights = serialize([]);
        Option::set($module_id,Access::OPTION_FIELD_NAME, $postRights);
    }
    $AnalyticSettings = new Settings();
    /** Права на Highload-блоки ПО **/
    $rights = $request->get('RIGHTS');
    $groups = $request->get('GROUPS');
    $AnalyticSettings->HighloadBlocksRightSave($rights, $groups);

    /** Сохранение, изменение настроек динамических ссылок */
    $dynamicUrl = $request->get('UF_RNS_DYNAMIC_URL');
    $dynamicUrlParam = $request->get('UF_RNS_DYNAMIC_URL_PARAM');
    $dynamicUrlActive = $request->get('UF_RNS_DYNAMIC_URL_ACTIVE');
    $AnalyticSettings->ChangeSettingsDynamicUrl($dynamicUrl, $dynamicUrlParam, $dynamicUrlActive);

}

$public_right = Option::get($module_id, Access::OPTION_FIELD_NAME);

if (strlen($public_right) > 0)
    $public_right = unserialize($public_right);
else
    $public_right = [];


$rights = Access::GetArrRightsPublic();
$DynamicUrlItems = Settings::GetDynamicUrlValues(true);

ob_start();
Access::AnalyticsShowRights("RIGHTS", $rights, $public_right);
$rights_html = ob_get_contents();
ob_end_clean();

$rights_fields = [
    [
        "id"=>"RIGHTS",
        "name"=>Loc::getMessage("CT_BLLE_ACCESS_RIGHTS"),
        "type"=>"custom",
        "colspan"=>true,
        "value"=>$rights_html,
    ],
];
?>
<style type="text/css">
    input.analytic-settings-input{
        width: 90%;
    }
    td.analytic-settings-td{
        text-align: center;
    }
    td.analytic-settings-td input[type="text"]{
        width: 90%;
    }
    tr.sampleDynamicUrlClass{
        display: none;
    }
    span.span-delete{
        padding-left: 20px;
        color: red;
        font-weight: bold;
        font-size: 16px;
        cursor: pointer;
    }
</style>
<form method="POST" id="analyticsOptionsForm"
      action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialcharsbx($mid) ?>&lang=<?= LANGUAGE_ID ?>"
      ENCTYPE="multipart/form-data">
    <?php
    $tabControl = new CAdminTabControl('tabControl', $tabs);
    $tabControl->Begin();

    if (!empty($options)) {
        foreach ($options as $optionArr) {
            $tabControl->BeginNextTab();
            foreach ($optionArr as $option) {
                if ($option['test_connection'] == 'true') {
                    ?>
                    <tr>
                        <td><input type="button" class="adm-btn-save"
                                   value="<?= Loc::getMessage('RNSANALYTICS_OPT_TEST_CONNECTION') ?>"
                                   onclick="connTest();"/>
                            <span id="RNSANALYTICS_OPT_TEST_CONNECTION"></span>
                        </td>
                    </tr>
                    <?php
                } elseif ($option['dynamic_url'] == 'true') {
                    ?>
                    <tr class="heading">
                        <td valign="middle" align="center" nowrap="">
                            <?=Loc::getMessage('RNSANALYTICS_OPT_TAB_DYNAMIC_URL_URL') ?>
                        </td>
                        <td valign="top" align="center" nowrap="">
                            <?=Loc::getMessage('RNSANALYTICS_OPT_TAB_DYNAMIC_URL_PARAM') ?>
                        </td>
                        <td width="100px" valign="top" align="center" nowrap="">
                            <?=Loc::getMessage('RNSANALYTICS_OPT_TAB_DYNAMIC_URL_ACTIVE') ?>
                        </td>
                    </tr>
                    <?php
                    foreach ($DynamicUrlItems as $item){?>
                        <tr>
                            <td class="analytic-settings-td">
                                <?= InputType('text', 'UF_RNS_DYNAMIC_URL[id_'.$item['id'].']', $item['url'], false) ?>
                            </td >
                            <td class="analytic-settings-td">
                                <?= InputType('text', 'UF_RNS_DYNAMIC_URL_PARAM[id_'.$item['id'].']', $item['parameter'], false) ?>
                            </td>
                            <td class="analytic-settings-td">
                                <?= InputType('checkbox', 'UF_RNS_DYNAMIC_URL_ACTIVE[id_'.$item['id'].']', 'Y', [$item['active']], false, false, '', 'ACTIVE_ELEM_'.$item['id']) ?>

                                <!--<span class="span-delete">X</span>-->
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                    <tr id="sampleDynamicUrlID" class="sampleDynamicUrlClass">
                        <td class="analytic-settings-td">
                            <?= InputType('text', 'UF_RNS_DYNAMIC_URL[]', '', false) ?>
                        </td >
                        <td class="analytic-settings-td">
                            <?= InputType('text', 'UF_RNS_DYNAMIC_URL_PARAM[]', '', false) ?>
                        </td>
                        <td class="analytic-settings-td">
                            <?= InputType('checkbox', 'UF_RNS_DYNAMIC_URL_ACTIVE[]', 'Y', ["Y"], false, false, '', 'ACTIVE_ELEM[]') ?>

                            <!--<span class="span-delete">X</span>-->
                        </td>
                    </tr>
                    <tr id="AddBtn">
                        <td></td>
                        <td></td>
                        <td>
                            <input id="AddPunkt" type="button" value="Добавить новый пункт">
                        </td>
                    </tr>
                    <?php
                }elseif ($option['access_form'] == 'true') {
                    $Update = $_POST['save'] . $_POST['apply'];
                    require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin/group_rights.php");
                    ?>
                    <tr class="heading">
                        <td colspan="4"><?= Loc::getMessage("PUBLIC_GROUP_RIGHT")?></td>
                    </tr>
                    <?php echo $rights_fields[0]['value'];
                } else {
                    __AdmSettingsDrawRow($module_id, $option);
                }
            }
        }
    }
    $tabControl->Buttons(['btnApply' => false]);
    ?>
    <script language="JavaScript">
        $('#AddPunkt').on('click', function (e){
            e.preventDefault();
            let elem = $('#sampleDynamicUrlID').clone().removeClass('sampleDynamicUrlClass');
            $( "#AddBtn" ).before(elem);
        });
        function RestoreDefaults() {
            if (confirm('<?= AddSlashes(Loc::GetMessage('MAIN_HINT_RESTORE_DEFAULTS_WARNING')) ?>'))
                window.location = "<?= $APPLICATION->GetCurPage()?>?RestoreDefaults=Y&ID=<?=$ID?>&lang=<?= LANG?>&mid=<?= rawurlencode($mid) . "&" . bitrix_sessid_get();?>";
        }

        function connTest() {
            let form = document.getElementById("analyticsOptionsForm"),
                apiUrl = form.elements["RNSANALYTICS_OPT_API_URL"].value,
                textPlace = document.getElementById("RNSANALYTICS_OPT_TEST_CONNECTION");
            BX.ajax.post(
                apiUrl + '/',
                {},
                function (response) {
                    let result = BX.parseJSON(response);
                    BX.cleanNode(textPlace);
                    if (result && result.success) {
                        BX.append(BX.create('span', {
                            style: {
                                color: "green"
                            },
                            text: "<?= Loc::getMessage('RNSANALYTICS_ALERT_SUCCESS')?>"
                        }), textPlace);
                    } else {
                        BX.append(BX.create('span', {
                            style: {
                                color: "red"
                            },
                            text: "<?= Loc::getMessage('RNSANALYTICS_ALERT_ERROR')?>"
                        }), textPlace);
                    }
                }
            );
        }
    </script>
    <input type="reset" name="reset" value="<?= Loc::getMessage('MAIN_RESET') ?>">
    <input type="button" <?php if ($MOD_RIGHT < 'W') echo "disabled" ?>
           title="<?= Loc::GetMessage('MAIN_HINT_RESTORE_DEFAULTS') ?>" OnClick="RestoreDefaults();"
           value="<?= Loc::GetMessage('MAIN_RESTORE_DEFAULTS') ?>">
    <?php
    echo bitrix_sessid_post();
    $tabControl->End();
    ?>
</form>