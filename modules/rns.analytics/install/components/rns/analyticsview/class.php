<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;

class AnalyticsView extends \CBitrixComponent
{
    const MODULE_ID = "rns.analytics";

    /**
     * @return bool|mixed|null
     * @throws \Bitrix\Main\LoaderException
     */
    public function executeComponent()
    {
        if (!Loader::includeModule('rns.analytics')) {
            echo GetMessage("ACCESS_DENIED");
            return false;
        }

        $this->includeComponentTemplate();
    }
}