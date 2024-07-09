<?php

namespace Rns\Bitrix24Examples;

use Bitrix\Main\LoaderException;
use Bitrix\Main\UI\Extension;

class Events
{
    /**
     * Подключаем js расширение модуля
     *
     * @return void
     * @throws LoaderException
     */
    public static function loadCustomExtension(): void
    {
        Extension::load("rns.bitrix24examples");
    }

    /**
     * Добавляем пункт "Дни рождения" в верхнее меню на главной странице CRM
     *
     * @param $menuItems
     * @return void
     */
    public static function addCrmMenuItemBirthdays(&$menuItems ): void
    {

        // $menuItems - список элементов меню по ссылке. Можно даже удалить
        $menuItems[] = [
            /**
             * ID пункт меню. Нужен для подсветки на странице
             * @var string
             */
            'ID'      => 'BIRTHDAYS',

            /**
             * ID js-пункта меню. Для счетчиков и action-комманд
             * @var string
             */
            'MENU_ID' => 'menu_crm_birthdays',

            /**
             * Название и hover title
             * @var string
             */
            'NAME'    => 'Дни рождения',
            'TITLE'   => 'Дни рождения',

            /**
             * Ссылка, куда вести по нажатию
             * @var string
             */
            'URL'     => '/birthdays/',
        ];
    }
}