<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\Response\Component;
use Bitrix\Main\Loader;

class mapanalitics extends \CBitrixComponent implements Controllerable
{
    /**
     * @return array
     */
    public function configureActions()
    {
        return [];
    }

    /**
     * @return Component
     */
    public function getFormAction()
    {
        return new Component('rns:mapanalitics');
    }

    /**
     * @return bool|mixed|null
     * @throws \Bitrix\Main\LoaderException
     */
    public function executeComponent()
    {
        if (!Loader::includeModule('rns.analytics') && !Loader::includeModule('tasks')) {
            echo GetMessage("ACCESS_DENIED");
            return false;
        }

        $this->includeComponentTemplate();
    }
}