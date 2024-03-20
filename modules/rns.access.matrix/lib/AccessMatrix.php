<?php

namespace Rns\AccessMatrix;

use Bitrix\Main\Loader,
    Bitrix\Highloadblock as HL;

class AccessMatrix
{
    public const RIGHTS_IDS = [
        //ключи прав на Дашборды
        "DASHBOARDS" => [
            "CLOSE" => "CLOSE", //закрыт
            "READE" => "READE", //просмотр
            "EXPORT" => "EXPORT", //просмотр и экспорт
            "WRITE" => "WRITE", //полный доступ
        ],

        //ключи прав на Настраиваемые дашборды
        "DASHBOARDS_CUSTOM" => [
            "CLOSE" => "CLOSE", //закрыт
            "READE" => "READE", //просмотр
            "WRITE" => "WRITE", //полный доступ
        ],

        //ключи прав на Учет трудозатрать
        "TIMEMAN" => [
            "CLOSE" => "CLOSE", //закрыт
            "READE" => "READE", //просмотр
            "BOOKING" => "BOOKING", //просмотр и бронирование
            "AGREE" => "AGREE", //просмотр и согласование
            "MANAGE" => "MANAGE", //просмотр и управление
        ],

        //ключи прав на базовые проекты
        "BP_PROJECTS" => [
            "BP_CREATE" =>"BP_CREATE", //Создание базового проекта
            "BP_DELETE" => "BP_DELETE", //Удаление базового проекта
            "BP_UPDATE" => "BP_UPDATE", //Полное редактирование базового проекта
            //"BP_REED" => "BP_REED", //Просмотр данных и задач базового проекта
            "BP_TASK" => "BP_TASK", //Привязка задачи к базовому проекту
            "BP_STAT" => "BP_STAT", //Изменение состояния базового проекта
            "BP_DATE" => "BP_DATE", //Установка и изменение сроков базового проекта
            "BP_MEMBER" =>"BP_MEMBER", //Добавление участников в базовый проект
            "BP_STATUS" => "BP_STATUS", //Ввод и изменение статуса базового проекта
            "BP_RISC" => "BP_RISC", //Внесение/редактирование сведений о рисках задач базового проекта
            //"BP_IMPORT" => "BP_IMPORT", //Загрузка файлов на диск базового проекта в ИСУП
            "BP_EXPORT" => "BP_EXPORT", //Экспорт списка базовых проектов
        ],

        //ключи прав на стратегические проекты
        "SP_PROJECTS" => [
            "SP_CREATE" =>"SP_CREATE", //Создание стратегического проекта
            "SP_DELETE" => "SP_DELETE", //Удаление стратегического проекта
            "SP_UPDATE" => "SP_UPDATE", //Полное редактирование стратегического проекта
            //"SP_REED" => "SP_REED", //Просмотр данных и задач стратегического проекта
            "SP_TASK" => "SP_TASK", //Привязка задачи к стратегическому проекту
            "SP_STAT" => "SP_STAT", //Изменение состояния стратегического проекта
            "SP_DATE" => "SP_DATE", //Добавление участников в стратегический проект
            "SP_MEMBER" =>"SP_MEMBER", //Добавление участников в стратегический проект
            "SP_STATUS" => "SP_STATUS", //Ввод и изменение статуса стратегического проекта
            "SP_RISC" => "SP_RISC", //Внесение/редактирование сведений о рисках задач стратегического проекта
            //"SP_IMPORT" => "SP_IMPORT", //Загрузка файлов на диск стратегического проекта в ИСУП
            "SP_EXPORT" => "SP_EXPORT", //Экспорт списка стратегических проектов
            "SP_STRATEGIC" => "SP_STRATEGIC", //Снятие у проекта признака "Стратегический"
        ],

        //ключи прав на базовые задачи
        "BT_TASKS" => [
            "BT_CREATE" =>"BT_CREATE", //Создание задач
            "BT_UPDATE" => "BT_UPDATE", //Экспорт задач
            "BT_READE" => "BT_READE", //Просмотр задач
            "BT_DELETE" => "BT_DELETE", //Удаление задач
            "BT_IMPORT" => "BT_IMPORT", //Импорт  задач
            "BT_EXPORT" => "BT_EXPORT", //Экспорт задач
            /*"BT_NAME" => "BT_NAME", //Изменение названия
            "BT_DESCRIPTION" => "BT_DESCRIPTION", //Изменение описания
            "BT_RESPONSIBLE" => "BT_RESPONSIBLE", //Изменение ответственного
            "BT_AUTHOR" => "BT_AUTHOR", //Изменение постановщика
            "BT_ACCOMPLICES" =>"BT_ACCOMPLICES", //Изменение соисполнителей
            "BT_AUDITORS" => "BT_AUDITORS", //Изменение наблюдателей
            "BT_DEDLINE" => "BT_DEDLINE", //Изменение крайнего срока
            "BT_DATE" => "BT_DATE", //Изменение даты старта и даты финиша
            "BT_UPDATE_PROJECT" =>"BT_UPDATE_PROJECT", //Изменение проекта
            "BT_DELETE_PROJECT" => "BT_DELETE_PROJECT", //Удаление привязки к проекту
            "BT_UF_EXECUTOR" => "BT_UF_EXECUTOR", //Изменение организации-исполнителя
            "BT_TAG" => "BT_TAG", //Изменение и добавление тегов
            "BT_UF_EXTERNAL_ESCALATION" => "BT_UF_EXTERNAL_ESCALATION", //Назначение/снятие признака внешней эскалации
            "BT_UF_INTERNAL_ESCALATION" => "BT_UF_INTERNAL_ESCALATION", //Назначение/снятие признака внутренней эскалации
            "BT_UF_TYPE" => "BT_UF_TYPE", //Изменение типа
            "BT_UF_TRACK" =>"BT_UF_TRACK", //Изменение и добавление трека
            "BT_SUBTASK" => "BT_SUBTASK", //Назначение задачи подзадачей
            "BT_SUBTASK_CREATE" => "BT_SUBTASK_CREATE", //Создание подзадачи к задаче
            "BT_SUBTASK_UPDATE" => "BT_SUBTASK_UPDATE", //Изменение набора связанных задач
            "BT_SET_STRATEGIC" => "BT_SET_STRATEGIC", //Присвоение задаче признака «Стратегическая»
            "BT_RESOLUTION_UPDATE" => "BT_RESOLUTION_UPDATE", //Изменение набора связанных поручений
            "BT_STATUS_1" => "BT_STATUS_1", //Перевод задачи в состояние "Ждет контроля" из состояния "В работе"
            "BT_STATUS_2" => "BT_STATUS_2", //Перевод задачи в состояние "В работе" из состояния "Ждет выполнения"
            "BT_STATUS_3" => "BT_STATUS_3", //Перевод задачи в состояние "В работе" из состояния "Ждет контроля" (данная возможность должна быть доступна только для пользователей ИСУП, состоящих в группах «Администратор» и «Контрольный отдел»)
            "BT_CLOSE" => "BT_CLOSE", //Закрытие задачи
            "BT_COMMENTS" => "BT_COMMENTS", //Добавление комментария к задаче
            "BT_COMMENTS_STATUS" => "BT_COMMENTS_STATUS", //Добавление комментария с признаком «В статус» к задаче
            */
        ],

        //ключи прав на стратегические задачи
        "ST_TASKS" => [
            "ST_CREATE" =>"ST_CREATE",//Создание задач
            "ST_READE" => "ST_READE",//Просмотр задач
            "ST_DELETE" => "ST_DELETE",//Удаление задач
            "ST_NAME" => "ST_NAME",//Изменение названия
            "ST_DESCRIPTION" => "ST_DESCRIPTION",//Изменение описания
            "ST_RESPONSIBLE" => "ST_RESPONSIBLE",//Изменение ответственного
            "ST_AUTHOR" => "ST_AUTHOR",//Изменение постановщика
            "ST_ACCOMPLICES" =>"ST_ACCOMPLICES",//Изменение соисполнителей
            "ST_AUDITORS" => "ST_AUDITORS",//Изменение наблюдателей
            "ST_DEDLINE" => "ST_DEDLINE",//Изменение крайнего срока
            "ST_DATE" => "ST_DATE",//Изменение даты старта и даты финиша
            "ST_UPDATE_PROJECT" =>"ST_UPDATE_PROJECT",//Изменение проекта
            "ST_DELETE_PROJECT" => "ST_DELETE_PROJECT",//Удаление привязки к проекту
            "ST_UF_EXECUTOR" => "ST_UF_EXECUTOR",//Изменение организации-исполнителя
            "ST_TAG" => "ST_TAG",//Изменение и добавление тегов
            "ST_UF_EXTERNAL_ESCALATION" => "ST_UF_EXTERNAL_ESCALATION",//Назначение/снятие признака внешней эскалации
            "ST_UF_INTERNAL_ESCALATION" => "ST_UF_INTERNAL_ESCALATION",//Назначение/снятие признака внутренней эскалации
            "ST_UF_TYPE" => "ST_UF_TYPE",//Изменение типа
            "ST_UF_TRACK" =>"ST_UF_TRACK",//Изменение и добавление трека
            "ST_SUBTASK" => "ST_SUBTASK", //Назначение задачи подзадачей
            "ST_SUBTASK_CREATE" => "ST_SUBTASK_CREATE",//Создание подзадачи к задаче
            "ST_RESOLUTION_UPDATE" => "ST_RESOLUTION_UPDATE",//Изменение набора связанных поручений
            "ST_SUBTASK_UPDATE" => "ST_SUBTASK_UPDATE",//Изменение набора связанных задач
            "ST_SET_STRATEGIC" => "ST_SET_STRATEGIC",//Присвоение задаче признака «Стратегическая»
            "ST_STATUS_1" => "ST_STATUS_1",//Перевод задачи в состояние "Ждет контроля" из состояния "В работе"
            "ST_STATUS_2" => "ST_STATUS_2",//Перевод задачи в состояние "В работе" из состояния "Ждет выполнения"
            "ST_STATUS_3" => "ST_STATUS_3",//Перевод задачи в состояние "В работе" из состояния "Ждет контроля" (данная возможность должна быть доступна только для пользователей ИСУП, состоящих в группах «Администратор» и «Контрольный отдел»)
            "ST_CLOSE" => "ST_CLOSE",//Закрытие задачи
            "ST_COMMENTS" => "ST_COMMENTS",//Добавление комментария к задаче
            "ST_COMMENTS_STATUS" => "ST_COMMENTS_STATUS",//Добавление комментария с признаком «В статус» к задаче
            "ST_EXPORT" => "ST_EXPORT",//Экспорт задач
            "ST_IMPORT" => "ST_IMPORT",//Импорт  задач
        ],

        //ключи прав на поручения
        "RESOLUTIONS" => [
            "RT_CREATE" =>"RT_CREATE", //Создание поручений
            "RT_UPDATE" =>"RT_UPDATE", //Редактирование поручений
            "RT_READE" => "RT_READE", //Просмотр поручений
            "RT_DELETE" => "RT_DELETE", //Удаление поручений
            "RT_EXPORT" => "RT_EXPORT", //Экспорт поручений
            /*"RT_NAME" => "RT_NAME", //Изменение названия
            "RT_DESCRIPTION" => "RT_DESCRIPTION", //Изменение описания
            "RT_RESPONSIBLE" => "RT_RESPONSIBLE", //Изменение ответственного
            "RT_ACCOMPLICES" => "RT_ACCOMPLICES", //Изменение соисполнителей
            "RT_DEDLINE" =>"RT_DEDLINE", //Изменение контрольного срока
            "RT_DEDLINE_PROM" => "RT_DEDLINE_PROM", //Изменение промежуточного контрольного срока
            "RT_UF_TYPE" => "RT_UF_TYPE", //Изменение типа
            "RT_UF_LEVEL" => "RT_UF_LEVEL", //Изменение уровня
            "RT_UF_RECVIZIT" =>"RT_UF_RECVIZIT", //Изменение реквизитов
            "ST_UPDATE_PROJECT" => "ST_UPDATE_PROJECT", //Изменение проекта
            "RT_UF_ESCALATION" => "RT_UF_ESCALATION", //Назначение/снятие признака эскалации
            "RT_SUBTASK_UPDATE" => "RT_SUBTASK_UPDATE", //Изменение набора связанных задач
            "RT_RESOLUTION_UPDATE" =>"RT_RESOLUTION_UPDATE", //Изменение набора связанных поручений
            "RT_UF_CONTROL" => "RT_UF_CONTROL", //Установка признака «На контроле»
            "RT_UF_NO_CONTROL" => "RT_UF_NO_CONTROL", //Установка признака «Без контроля»
            "RT_STATUS_1" => "RT_STATUS_1", //Перевод поручения в состояние "Ждет контроля" из состояния "В работе"
            "RT_STATUS_2" => "RT_STATUS_2", //Перевод поручения в состояние "В работе" из состояния "Ждет выполнения"
            "RT_STATUS_3" => "RT_STATUS_3", //Перевод поручения в состояние "В работе" из состояния "Ждет контроля"
            "RT_STATUS_4" => "RT_STATUS_4", //Перевод поручения в состояние "На докладе" из состояния "Ждет контроля"
            "RT_CLOSE" => "RT_CLOSE", //Закрытие поручения
            "RT_COMMENTS" => "RT_COMMENTS", //Добавление комментария к поручению
            "RT_COMMENTS_STATUS" => "RT_COMMENTS_STATUS", //Добавление комментария с признаком «В статус» к поручению*/
        ],
    ];

    public const OPTION_DASHBOARD_KP_FIELD = 'OPTION_DASHBOARD_KP_FIELD'; // код прав на дашборд КП
    public const OPTION_DASHBOARD_GOSTEH_FIELD = 'OPTION_DASHBOARD_GOSTEH_FIELD'; // код прав на дашборд Гостех
    public const OPTION_DASHBOARD_DK = 'OPTION_DASHBOARD_DK'; //код прав на дашборд ДК
    public const OPTION_DASHBOARD_PROJECT = 'OPTION_DASHBOARD_PROJECT'; //код прав на проекты
    public const OPTION_DASHBOARD_PROJECTS = 'OPTION_DASHBOARD_PROJECTS'; //код прав на дашборд проекты
    public const OPTION_DASHBOARD_CUSTOM_FIELD = 'OPTION_DASHBOARD_CUSTOM_FIELD'; //код прав на настраиваемые дашборд
    public const OPTION_TIMEMAN_FIELD = 'OPTION_TIMEMAN_FIELD'; //код прав на учет трудозатрать
    public const OPTION_PROJECTS_FIELD = 'OPTION_PROJECTS_FIELD'; //код прав на проекты
    public const OPTION_TASKS_FIELD = 'OPTION_TASKS_FIELD'; //код прав на задачи
    public const OPTION_RESOLUTION_FIELD = 'OPTION_RESOLUTION_FIELD'; //код прав на поручения

    public static function GetRightByCode($access_group_code)
    {
        $param = [
            'limit' => 1,
            'filter' => [
                'UF_ACCESS_GROUP_CODE' => $access_group_code,
            ],
            'select' => ['UF_ACCESS_GROUP_VALUE']
        ];
        Loader::includeModule("highloadblock");
        $hlblock = HL\HighloadBlockTable::getList(['filter' => ['NAME' => HLBlockAccessMatrix::$HLBlockName]])->fetch();
        if($hlblock){
            $Release = HL\HighloadBlockTable::compileEntity($hlblock);
            $Release_data_class = $Release->getDataClass();
            if ($arRes = $Release_data_class::getList($param)->fetch()) {
                return $arRes['UF_ACCESS_GROUP_VALUE'];
            }
        }

        return '';
    }
    public static function DeleteRightByCode($access_group_code)
    {
        $param = [
            'limit' => 1,
            'filter' => [
                'UF_ACCESS_GROUP_CODE' => $access_group_code,
            ],
            'select' => ['ID']
        ];
        Loader::includeModule("highloadblock");
        $hlblock = HL\HighloadBlockTable::getList(['filter' => ['NAME' => HLBlockAccessMatrix::$HLBlockName]])->fetch();
        if($hlblock){
            $Release = HL\HighloadBlockTable::compileEntity($hlblock);
            $Release_data_class = $Release->getDataClass();
            if ($arRes = $Release_data_class::getList($param)->fetch()) {
                $Release_data_class::delete($arRes['ID']);
            }
        }
    }

    public static function GetRightsByCode($access_group_code)
    {
        $res = [];
        $param = [
            'filter' => [
                '%UF_ACCESS_GROUP_CODE' => $access_group_code,
            ],
            'select' => ['*'],
            'order' => ['UF_ACCESS_GROUP_CODE' => 'asc']
        ];
        Loader::includeModule("highloadblock");
        $hlblock = HL\HighloadBlockTable::getList(['filter' => ['NAME' => HLBlockAccessMatrix::$HLBlockName]])->fetch();
        if($hlblock){
            $Release = HL\HighloadBlockTable::compileEntity($hlblock);
            $Release_data_class = $Release->getDataClass();
            $r = $Release_data_class::getList($param);
            while ($arRes = $r->fetch()) {
                $res[] = $arRes;
            }
        }

        return $res;
    }

    public static function RightsSave($access_group_code, $value)
    {
        $param = [
            'limit' => 1,
            'filter' => [
                'UF_ACCESS_GROUP_CODE' => $access_group_code,
            ],
            'select' => ['ID']
        ];
        Loader::includeModule("highloadblock");
        $hlblock = HL\HighloadBlockTable::getList(['filter' => ['NAME' => HLBlockAccessMatrix::$HLBlockName]])->fetch();
        if($hlblock){
            $Release = HL\HighloadBlockTable::compileEntity($hlblock);
            $Release_data_class = $Release->getDataClass();
            if ($arRes = $Release_data_class::getList($param)->fetch()) {
                $Release_data_class::update($arRes['ID'], ['UF_ACCESS_GROUP_VALUE' => $value]);
            } else {
                $resId = $Release_data_class::add(['UF_ACCESS_GROUP_CODE' => $access_group_code, 'UF_ACCESS_GROUP_VALUE' => $value])->getId();
            }
        }
    }

    public static function Explode($string, $separator = ";")
    {
        $res = explode($separator, str_replace(' ', '', $string));
        $res = array_unique(array_filter($res));
        return $res;
    }

    public static function GetUserGroups():array
    {
        global $USER;
        return \CUser::GetUserGroup($USER->GetID());
    }

    public static function UserIsAdmin():bool
    {
        global $USER;
        return $USER->IsAdmin();
    }

    public static function CurrentUserId():int
    {
        global $USER;
        return  (int)$USER->GetID();
    }

    public static function Rights($optionField)
    {
        return json_decode(self::GetRightByCode($optionField), true);
    }

    public static function IsRight($option_field, $arRights = [])
    {
        $result = false;
        if (self::UserIsAdmin())
            $result = true;

        $r = self::Rights($option_field);
        $UGroups = self::GetUserGroups();
        $userId = self::CurrentUserId();
        foreach ($r as $value){
            $type =  substr($value['GROUP_CODE'], 0, 1 );
            $id =  substr($value['GROUP_CODE'], 1 );
            if ($type == 'G' && in_array($id, $UGroups) && in_array($value['TASK_ID'], $arRights))
                $result = true;

            if ($type == 'U' && $id == $userId && in_array($value['TASK_ID'], $arRights))
                $result = true;
        }

        return $result;
    }

}