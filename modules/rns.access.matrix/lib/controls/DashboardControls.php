<?php
namespace Rns\AccessMatrix;

class DashboardControls extends AccessMatrix
{
    public static function Rights($optionField)
    {
        return json_decode(self::GetRightByCode($optionField), true);
    }

    private static function isShowPage($optionField):bool
    {
        return self::IsRight($optionField,
            array(self::RIGHTS_IDS['DASHBOARDS']['READE'], self::RIGHTS_IDS['DASHBOARDS']['EXPORT'], self::RIGHTS_IDS['DASHBOARDS']['WRITE'])
        );
    }
    private static function isAllowExport($optionField):bool
    {
        return self::IsRight($optionField,
            array(self::RIGHTS_IDS['DASHBOARDS']['EXPORT'], self::RIGHTS_IDS['DASHBOARDS']['WRITE'])
        );
    }
    private static function isAllowSort($optionField):bool
    {
        return self::IsRight($optionField,
            array(self::RIGHTS_IDS['DASHBOARDS']['WRITE'])
        );
    }

    public static function isShowPageKP():bool
    {
        return self::isShowPage(self::OPTION_DASHBOARD_KP_FIELD);
    }

    public static function isShowPageGostech():bool
    {
        return self::isShowPage(self::OPTION_DASHBOARD_GOSTEH_FIELD);
    }

    public static function isShowPageProject():bool
    {
        return self::isShowPage(self::OPTION_DASHBOARD_PROJECT);
    }

    public static function isShowPageProjects():bool
    {
        return self::isShowPage(self::OPTION_DASHBOARD_PROJECTS);
    }

    public static function isShowPageDK():bool
    {
        return self::isShowPage(self::OPTION_DASHBOARD_DK);
    }

    public static function isAllowExportKP():bool
    {
        return self::isAllowExport(self::OPTION_DASHBOARD_KP_FIELD);
    }

    public static function isAllowExportGostech():bool
    {
        return self::isAllowExport(self::OPTION_DASHBOARD_GOSTEH_FIELD);
    }
    public static function isAllowSortGostech():bool
    {
        return self::isAllowSort(self::OPTION_DASHBOARD_GOSTEH_FIELD);
    }

}
