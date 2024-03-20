<?php
namespace Rns\AccessMatrix;

class CustomDashboardControls extends AccessMatrix
{

    private static function isShowPage($optionField):bool
    {
        return self::IsRight($optionField,
            array(self::RIGHTS_IDS['DASHBOARDS_CUSTOM']['READE'], self::RIGHTS_IDS['DASHBOARDS']['WRITE'])
        );
    }

    public static function isShowModule():bool
    {
        return self::isShowPage(self::OPTION_DASHBOARD_CUSTOM_FIELD);
    }

    public static function GetCurrentUserRight()
    {
        $rightCode = "CLOSE";
        $rightCode = self::IsRight(self::OPTION_DASHBOARD_CUSTOM_FIELD, array(self::RIGHTS_IDS['DASHBOARDS_CUSTOM']['READE'])) ? 'READE' : $rightCode;
        $rightCode = self::IsRight(self::OPTION_DASHBOARD_CUSTOM_FIELD, array(self::RIGHTS_IDS['DASHBOARDS_CUSTOM']['WRITE'])) ? 'WRITE' : $rightCode;
        return $rightCode;
    }

    public static function GetCurrentUserProjects()
    {
        $resOb = \Bitrix\Socialnetwork\UserToGroupTable::getList([
            'filter' => ['USER_ID' => self::CurrentUserId()],
            'select' => ['USER_ID', 'GROUP_ID', 'ROLE'],
            'order' => ['ID' => 'DESC'],
        ]);
        $ProjectsId = [];
        while ($res = $resOb->fetch())
        {
            if ($res['ROLE'] == 'A' || $res['ROLE'] == 'E' || $res['ROLE'] == 'K')
                $ProjectsId[$res['GROUP_ID']] = $res['GROUP_ID'];
        }

        $resGroup = \Bitrix\Socialnetwork\WorkgroupTable::getList([
            'select' => ['ID', 'CLOSED', 'VISIBLE', 'OPENED'],
            'order' => ["ID" => "DESC"],
        ]);
        while ($group = $resGroup->fetch())
        {
            if ($group['VISIBLE'] === 'Y' || $group['OPENED'] === 'Y' || self::UserIsAdmin())
                $ProjectsId[$group['ID']] = $group['ID'];
        }

    }

}
