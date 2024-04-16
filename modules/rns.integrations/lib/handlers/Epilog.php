<?php

namespace RNS\Integrations\Handlers;

use CJSCore;
use COption;
use CSite;
use RNS\Integrations\IntegrationOptionsTable;
use RNS\Integrations\Helpers\EntityFacade;
use RNS\Integrations\Models\IntegrationOptionsTableWrapper;
use Bitrix\Main\Loader;

class Epilog
{
    /**
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function initializeJs()
    {
        global $USER;
        global $APPLICATION;
        if (COption::GetOptionString('rns.integrations', 'active') != 'Y') {
            return;
        }
        $res = IntegrationOptionsTable::getList(['select' => ['ID', 'ACTIVE'],
          'filter' => ['=SYSTEM.CODE' => 'ms_project']]);
        if ($row = $res->fetch()) {
            if ($row['ACTIVE'] != 'Y') {
                return;
            }
        } else {
            return;
        }
        if (CSite::InDir("/company/personal/user/{$USER->GetID()}/tasks")) {
            CJSCore::RegisterExt('custom_import_formats',
              ['js' => '/local/modules/rns.integrations/assets/js/custom_import_formats.js']);
            CJSCore::Init('custom_import_formats');

            $APPLICATION->SetAdditionalCss('/local/modules/rns.integrations/assets/css/custom_import_formats.css');
        }
    }
}
