<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\UI\Toolbar\Facade\Toolbar;

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var $component */
/** @var $templateFolder */
if ($arParams['IFRAME'] == 'Y'){
    CJSCore::Init("sidepanel");

    ?><script type="text/javascript">
        // Prevent loading page without header and footer
        if(window === window.top)
        {
            window.location = "<?=CUtil::JSEscape($APPLICATION->GetCurPageParam('', ['IFRAME'])); ?>";
        }
    </script><?php

    $APPLICATION->ShowHead();
}
echo 'detail';
?>