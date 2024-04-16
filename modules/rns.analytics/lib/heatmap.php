<?php

namespace Rns\Analytics;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use CJSCore;
use CUtil;
use Rns\Analytic\Access as RnsAccess;
use Rns\Analytic\Settings;

class Heatmap
{
    const MODULE_ID = "rns.analytics";

    /**
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public static function onEpilogAction()
    {
        global $USER, $APPLICATION;

        if (defined('ADMIN_SECTION') && ADMIN_SECTION) {
            return;
        }
        $active = Option::get(self::MODULE_ID, 'RNSANALYTICS_OPT_ACTIVE');
        if ($active !== 'Y') {
            return;
        }

        $userId = $USER->GetID();
        $vid = sha1(microtime() . 'user' . $userId);

        $arJsConfig = [
            'analytics_heatmap_ext' => [
                'js' => '/bitrix/js/rns/analytics/heatmapext.js'
            ],
            'analytics_heatmap' => [
                'js' => '/bitrix/js/rns/analytics/heatmap.min.js'
            ]
        ];
        foreach ($arJsConfig as $ext => $arExt) {
            CJSCore::RegisterExt($ext, $arExt);
        }
        CUtil::InitJSCore([
            'jquery',
            'analytics_heatmap_ext',
            'analytics_heatmap'
        ]);

        $apiUrl = Option::get(self::MODULE_ID, 'RNSANALYTICS_OPT_API_URL');
        $time = Option::get(self::MODULE_ID, 'RNSANALYTICS_DEFAULT_ACTIVE_TIME');
        $userId = $USER->GetID();
        $dynamicUrlParameter = Controller\Statictics::DYNAMIC_URL_SEPARATOR;
        $items = Settings::GetDynamicUrlValues();
        $dynamicUrlValues = '';
        foreach ($items as $item){
            $dynamicUrlValues .= $item["url"].':'.$item["parameter"].';';
        }
        $menuItemShow = "N";
        if (RnsAccess::MenuShow()){
            $menuItemShow = "Y";
        }
        $erMsg = Loc::getMessage("RNS_ANVIEW_FORM_ERROR_MSG");
        if ($apiUrl && $userId) {
            Asset::getInstance()->addString("<script>
            BX.message({
                RNS_ANVIEW_FORM_ERROR_MSG: '{$erMsg}',
            })
            BX.ready(function () {
            	BX.bind(document, \"readystatechange\", function () {
            		setTimeout(() => {
            			HeatMapExt.apiUrl = '{$apiUrl}';
            			HeatMapExt.userId = '{$userId}';
            			HeatMapExt.vid = '{$vid}';
            			HeatMapExt.time = '{$time}';
            			HeatMapExt.ShowMenu ='$menuItemShow';
            			HeatMapExt.dynamicUrlParameter ='$dynamicUrlParameter';
            			HeatMapExt.dynamicUrlValues ='$dynamicUrlValues';
            			HeatMapExt.init();
            		});
            	});
            });
            </script>");
        }
    }

    /**
     * @return string[]
     */
    public static function onEventLogGetAuditTypesAction()
    {
        return [
            'RNS_ANALYTICS_HEATMAP_CREATE_LOG' => '[RNS_ANALYTICS_HEATMAP_CREATE_LOG] Построение тепловой карты',
            'RNS_ANALYTICS_STATISTICS_CREATE_LOG' => '[RNS_ANALYTICS_STATISTICS_CREATE_LOG] Формирование статистики'
        ];
    }

    public static function CheckMenuItemShow()
    {
        $_SESSION['menuItemShow'] = "N";
        if (RnsAccess::MenuShow()){
            $_SESSION['menuItemShow'] = "Y";
        }
    }
}
