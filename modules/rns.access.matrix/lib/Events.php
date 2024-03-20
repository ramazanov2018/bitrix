<?php

namespace Rns\AccessMatrix;

use Bitrix\Main\Localization\Loc;
use Bitrix\Tasks\Control\Exception\TaskAddException;
use Bitrix\Tasks\Control\Exception\TaskUpdateException;

class Events
{
    static function OnProjectAdd(&$arFields)
    {
        $controller = new ProjectControls();
        if ($arFields['UF_STRATEGY'] == '1'){
            if (!$controller->isAllowStrategyProjectCreate(false))
                $controller->ResponseError('Ошибка создания: У Вас нет прав на создание стратегического проекта');
        }
        else {
            if (!$controller->isAllowBaseProjectCreate(false))
                $controller->ResponseError('Ошибка создания: У Вас нет прав на создание базового проекта');
        }
        return true;
    }

    static function OnAfterProjectAdd($ID, &$arFields)
    {
        $perms = [
            "view" => "L",
            "view_all" => "L",
            "sort" => "L",
            "create_tasks" => "L",
            "edit_tasks" => "L",
            "delete_tasks" => "L",

        ];
        \Bitrix\Main\Loader::includeModule("socialnetwork");
        $resGroup = \Bitrix\Socialnetwork\WorkgroupTable::getList([
            'select' => ['ID'],
        ]);
        while ($group = $resGroup->fetch())
        {
            $idTmp = \CSocNetFeatures::setFeature("G", $ID, "tasks", true);
            if ($idTmp){
                foreach($perms as $key => $perm){
                    $id1Tmp = \CSocNetFeaturesPerms::SetPerm($idTmp, $key, $perm);
                }
            }
        }
        return true;
    }
    static function OnProjectUpdate($ID, array &$arFields)
    {
        /*$controller = new ProjectControls($ID);

        if (isset($arFields['NUMBER_OF_MEMBERS']) && isset($arFields['NUMBER_OF_MODERATORS']))
            return true;

        //Снятие у проекта признака "Стратегический"
        if ($controller->project['UF_STRATEGY'] == '1' && $arFields['UF_STRATEGY'] == '0'){
            if (!$controller->isAllowStrategiyUpdate())
                $controller->ResponseError('Ошибка обновления: У Вас нет прав на снятие у проекта признака "Стратегический"');
        }

        //Установка и изменение сроков стратегического проекта
        //Установка и изменение сроков базового проекта
        if ($arFields['UF_STRATEGY'] == '1'
            && ($controller->project['PROJECT_DATE_START'] != $arFields['PROJECT_DATE_START']
            || $controller->project['PROJECT_DATE_FINISH'] != $arFields['PROJECT_DATE_FINISH'])){

            if (!$controller->isAllowStrategyProjectDateUpdate())
                $controller->ResponseError('Ошибка обновления: У Вас нет прав на установку и изменение сроков стратегического проекта	');
        }
        elseif ($controller->project['PROJECT_DATE_START'] != $arFields['PROJECT_DATE_START']
            || $controller->project['PROJECT_DATE_FINISH'] != $arFields['PROJECT_DATE_FINISH']) {
            if (!$controller->isAllowBaseProjectDateUpdate())
                $controller->ResponseError('Ошибка обновления: У Вас нет прав на установку и изменение сроков базового проекта');
        }

        //Установка и изменение сроков стратегического проекта
        //Установка и изменение сроков базового проекта
        if ($arFields['UF_STRATEGY'] == '1' && $controller->project['UF_GROUP_CONDITION'] != $arFields['UF_GROUP_CONDITION']){
            if (!$controller->isAllowStrategyProjectStateUpdate())
                $controller->ResponseError('Ошибка обновления: У Вас нет прав на изменение состояния базового проекта');
        }
        elseif ($controller->project['UF_GROUP_CONDITION'] != $arFields['UF_GROUP_CONDITION']) {
            if (!$controller->isAllowBaseProjectStateUpdate())
                $controller->ResponseError('Ошибка обновления: У Вас нет прав на изменение состояния базового проекта');
        }*/

        return true;
    }
    static function OnAfterProjectDelete($ID)
    {
        /**
         * удаление прав при удалении проекта
         * */
        ProjectControls::DeleteProjectRight($ID);
        TasksControls::DeleteProjectRight($ID);
        ResolutionsControls::DeleteProjectRight($ID);
        return true;
    }
    static function OnProjectDelete($ID)
    {

        $controller = new ProjectControls($ID);
        if ($controller->project['UF_STRATEGY'] == '1'){
            if (!$controller->isAllowStrategyProjectDelete(false))
                $controller->ResponseError('Ошибка удаления: У Вас нет прав на удаленя данного проекта');
        }
        else {
            if (!$controller->isAllowBaseProjectDelete(false))
                $controller->ResponseError('Ошибка удаления: У Вас нет прав на удаленя данного проекта');
        }

        return true;
    }
    static function OnTaskAdd(&$arFields)
    {
        if ($arFields['GROUP_ID'] && $arFields['UF_RESOLUTION'] != 'Y'){
            $isProjectTaskCreate = false;
            $ProjectController = new ProjectControls($arFields['GROUP_ID']);
            $TaskController = new TasksControls($arFields['GROUP_ID'], 0);
            if ($ProjectController->project['UF_STRATEGY'] == '1'){
                if ($ProjectController->isAllowStrategyProjectTask(false) || $TaskController->isAllowBaseTaskAdd())
                    $isProjectTaskCreate = true;
            }else{
                if ($ProjectController->isAllowBaseProjectTask(false) || $TaskController->isAllowBaseTaskAdd())
                    $isProjectTaskCreate = true;
            }

            if (!$isProjectTaskCreate)
                throw new TaskAddException('У Вас нет прав на привязки задачи к данному проекту');
        }

        if ($arFields['UF_RESOLUTION'] == 'Y') {
            $controller = new ResolutionsControls($arFields['GROUP_ID'], 0);
            if (!$controller->isAllowResolutionAdd())
                throw new TaskAddException('У Вас нет прав на создание поручения');
        }
    }
    static function OnTaskUpdate($ID, array &$arFields, &$arTask)
    {
        if ($arFields['GROUP_ID'] > 0 && $arFields['GROUP_ID'] != $arTask['GROUP_ID'] && $arFields['UF_RESOLUTION'] != 'Y'){
            $isProjectTaskCreate = false;
            $controller = new ProjectControls($arFields['GROUP_ID']);
            $TaskController = new TasksControls($arFields['GROUP_ID'], $ID);
            if ($controller->project['UF_STRATEGY'] == '1'){
                if ($controller->isAllowStrategyProjectTask(false) || $TaskController->isAllowBaseTaskAdd())
                    $isProjectTaskCreate = true;
            }else{
                if ($controller->isAllowBaseProjectTask(false) || $TaskController->isAllowBaseTaskAdd())
                    $isProjectTaskCreate = true;
            }

            if (!$isProjectTaskCreate)
                throw new TaskUpdateException('У Вас нет прав на привязки задачи к данному проекту');
        }

        if ($arFields['UF_RESOLUTION'] == 'Y') {
            $controller = new ResolutionsControls($arFields['GROUP_ID'], 0);
            if (!$controller->isAllowResolutionAdd())
                throw new TaskAddException('У Вас нет прав на привязки поручения к данному проекту');
        }

        /* echo "<pre>";
        print_r($arFields);
        echo "</pre>";
        die();*/
    }
    static function OnTaskDelete($ID, array &$arFields)
    {
        if ($arFields['UF_RESOLUTION'] == 'Y'){
            $controller = new ResolutionsControls($arFields['GROUP_ID'], $ID);
            if (!$controller->isAllowResolutionDelete())
                $controller->ResponseError('Ошибка удаления: У Вас нет прав на удаления данного поручения');
        }else{
            $controller = new TasksControls($arFields['GROUP_ID'], $ID);
                if (!$controller->isAllowBaseTaskDelete())
                    $controller->ResponseError('Ошибка удаления: У Вас нет прав на удаления данной задачи');
        }
    }
    static function OnResolutionAdd(&$arFields)
    {
        /* echo "<pre>";
        print_r($arFields);
        echo "</pre>";
        die();*/
    }
    static function OnResolutionUpdate($ID, array &$arFields)
    {
        /* echo "<pre>";
        print_r($arFields);
        echo "</pre>";
        die();*/
    }
    static function OnResolutionDelete($ID, array &$arFields)
    {
        /* echo "<pre>";
        print_r($arFields);
        echo "</pre>";
        die();*/
    }
}