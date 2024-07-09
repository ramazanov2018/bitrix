<?php
namespace Rns\Bitrix24Examples\Helpers;

use Bitrix\Highloadblock as HB;
use Bitrix\Main\Entity\Query;

class UserBirthdaysEntity
{
    const HLB_NAME = 'UserBirthdays';

    public static function getEntity()
    {
        $hlbBirthdays = HB\HighloadBlockTable::getList(['filter' => ['NAME' => self::HLB_NAME]])->fetch();
        return HB\HighloadBlockTable::compileEntity($hlbBirthdays);
    }

    public static function getQueryClass()
    {
        return new Query(self::getEntity());
    }

    public static function getDataClass()
    {
        return self::getEntity()->getDataClass();
    }
}