<?php
namespace Rns\AccessMatrix;

class TimemanControls extends AccessMatrix
{

    public static function timemanArUsers():array
    {
        $data = self::Rights(self::OPTION_TIMEMAN_FIELD.'_USERS');
        $data = self::Explode($data['USERS']);
        return $data;
    }

    public static function isAllowUsersShow()
    {
        return self::IsRight(self::OPTION_TIMEMAN_FIELD,
            array(self::RIGHTS_IDS['TIMEMAN']['BOOKING'], self::RIGHTS_IDS['TIMEMAN']['AGREE'], self::RIGHTS_IDS['TIMEMAN']['MANAGE'])
        );
    }

    public static function isAllowReserv()
    {
        return self::IsRight(self::OPTION_TIMEMAN_FIELD,
            array(self::RIGHTS_IDS['TIMEMAN']['BOOKING'], self::RIGHTS_IDS['TIMEMAN']['MANAGE'])
        );
    }

    public static function isShowPageTimeman():bool
    {
        return self::IsRight(self::OPTION_TIMEMAN_FIELD,
            array(self::RIGHTS_IDS['TIMEMAN']['BOOKING'], self::RIGHTS_IDS['TIMEMAN']['READE'], self::RIGHTS_IDS['TIMEMAN']['MANAGE'], self::RIGHTS_IDS['TIMEMAN']['AGREE'])
        );
    }
}
