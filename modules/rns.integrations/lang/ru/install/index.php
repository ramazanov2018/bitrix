<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();
$MESS['INTEGRATIONS_MODULE_ID'] = 'rns.integrations';
$MESS['INTEGRATIONS_MODULE_NAME'] = 'Интеграции';
$MESS['INTEGRATIONS_MODULE_DESCRIPTION'] = 'Модуль для интеграции с внешними системами';
$MESS['INTEGRATIONS_MODULE_COMPANY'] = 'RuNetSoft';
$MESS['INTEGRATIONS_MODULE_EMAIL'] = '';
$MESS['INTEGRATIONS_MODULE_WEBSITE'] = 'http://www.rns-soft.ru';

$MESS['INTEGRATIONS_MODULE_TYPE_REST_API'] = 'REST API';
$MESS['INTEGRATIONS_MODULE_TYPE_DATABASE'] = 'Запросы к СУБД';
$MESS['INTEGRATIONS_MODULE_TYPE_FILES'] = 'Импорт/экспорт файлов';

$MESS['INTEGRATIONS_MODULE_SYSTEM_JIRA'] = 'Jira';
$MESS['INTEGRATIONS_MODULE_SYSTEM_MS_PROJECT'] = 'MS Project';
$MESS['INTEGRATIONS_MODULE_SYSTEM_SAP'] = 'SAP';

$MESS['INTEGRATIONS_MODULE_OPTIONS_JIRA'] = 'Jira (импорт)';
$MESS['INTEGRATIONS_MODULE_OPTIONS_SAP'] = 'SAP (импорт)';
$MESS['INTEGRATIONS_MODULE_OPTIONS_MS_PROJECT'] = 'MS Project (импорт)';

$MESS['INTEGRATIONS_MODULE_INSTALL_TITLE'] = 'Установка модуля "Интеграции"';
$MESS['INTEGRATIONS_MODULE_INSTALL'] = 'Установить модуль';
$MESS['INTEGRATIONS_MODULE_INSTALL_TASK_NOT_CREATED'] = 'Задача не создана. Обратитесь в службу поддержки.';

$MESS['INTEGRATIONS_MODULE_INSTALL_OPTIONS'] = 'Выберите взаимодействия, которые будут установлены';
$MESS['INTEGRATIONS_MODULE_INSTALL_JIRA'] = 'Импорт задач из внешней системы Jira';
$MESS['INTEGRATIONS_MODULE_INSTALL_SAP'] = 'Импорт задач из внешней системы SAP';
$MESS['INTEGRATIONS_MODULE_INSTALL_MS_PROJECT'] = 'Импорт задач из внешней системы MS Project';

$MESS["INTEGRATIONS_MODULE_INSTALL_ERROR"] = 'Ошибка при установке модуля "Интеграции"';
$MESS["INTEGRATIONS_MODULE_UNINSTALL"] = 'Деинсталляция модуля "Интеграции"';
$MESS["INTEGRATIONS_MODULE_UNINSTALL_TITLE"] = 'Удаление модуля "Интеграции"';
$MESS["INTEGRATIONS_MODULE_UNINST_WARN"] = "Внимание!\nМодуль \"Интеграции\" будет удален из системы.";
$MESS["INTEGRATIONS_MODULE_UNINST_SAVE_DATA"] = "Вы можете сохранить данные модуля:";
$MESS["INTEGRATIONS_MODULE_UNINST_SAVE_DATA_TITLE"] = 'Сохранить "Настройки взаимодействий" и "Информационные системы"';
$MESS["INTEGRATIONS_MODULE_UNINST_SAVE_LOG_TITLE"] = 'Сохранить журналы и справочники';
$MESS["INTEGRATIONS_MODULE_SUCCESS_UNINSTALL"] = 'Модуль "Интеграции" успешно удален из системы.';
$MESS["INTEGRATIONS_MODULE_UNINST"] = 'Удалить модуль';