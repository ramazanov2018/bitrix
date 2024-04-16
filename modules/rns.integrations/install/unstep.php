<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== TRUE) die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

CAdminMessage::ShowNote(GetMessage("INTEGRATIONS_MODULE_SUCCESS_UNINSTALL"));
