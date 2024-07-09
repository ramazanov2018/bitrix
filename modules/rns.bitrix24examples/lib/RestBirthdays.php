<?php

namespace Rns\Bitrix24Examples;

use Rns\Bitrix24Examples\Helpers\UserBirthdaysEntity;

class RestBirthdays
{
    /**
     * Расширяем стандартный модуль restApi
     * @return array
     */
    public static function OnRestServiceBuildDescription(): array
    {
        return array(
            'rns.birthdays' => array(
                'rns.birthdays.get' => array(
                    'callback' => array(
                        __CLASS__, 'get'
                    ),
                    'options' => array()
                ),
            )
        );
    }

    /**
     * Получаем список дней рождения
     *
     * @return array
     */
    public static function get($query, $n, \CRestServer $server): array
    {
        return UserBirthdaysEntity::getDataClass()::getList()->fetchAll();
    }
}