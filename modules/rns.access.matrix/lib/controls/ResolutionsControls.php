<?php
namespace Rns\AccessMatrix;

use CIntranetUtils;
use CModule;
use Bitrix\Tasks\Util\User;

class ResolutionsControls extends AccessMatrix
{
    private $UserId;
    public $project = [];
    public $task = [];
    private $userGroups = [];
    private $isManager = false;
    private $isMember = false;
    private $isAssistant = false;
    private $isAuthor = false;
    private $isResponse = false;
    private $isAccomplice = false;
    private $isAudithor = false;
    private $isReed = false;
    public $R = [];

    public function __construct($project = 0, $task = 0)
    {

        $this->UserId = self::CurrentUserId();
        $this->userGroups = self::GetUserGroups();
        if ($task)
            $this->task = $this->GetTask($task);

        if (!$project && $this->task['GROUP_ID'])
            $project = $this->task['GROUP_ID'];

        if ($project)
            $this->project = $this->GetProject($project);

        $this->GetProjectRole($project);
        if ($task){
            $this->GetTaskRole($task);
            $this->isReed($task);
        }
        $this->R = $this->GetRights($project, $task);
    }

    public function GetRights($project = 0, $task = 0)
    {
        $PRights = json_decode(self::GetRightByCode(self::OPTION_RESOLUTION_FIELD.'_P_'.$project),true);
        $TRights = json_decode(self::GetRightByCode(self::OPTION_RESOLUTION_FIELD.'_T_'.$task),true);

        foreach ($TRights as $id => $right)
        {
            $PRights[$id] = $right;
        }

        return $PRights;
    }

    private function isAllowRight($RightCode, $defaultResult = false)
    {
        $result = false;
        if (self::UserIsAdmin())
            $result = true;

        if(empty($this->R[$RightCode]) && $defaultResult)
            $result = true;

        foreach ($this->R[$RightCode] as $value){
            $value =  explode('_', $value);
            //Группа пользователя
            if ($value[0] == 'G' && in_array($value[1], $this->userGroups))
                $result = true;
            //Пользователь
            if ($value[0] == 'U' && $value[1] == $this->UserId)
                $result = true;
            //Руководитель?
            if ($value[0] == 'R' && $this->isManager)
                $result = true;
            //Помощник руководителя?
            if ($value[0] == 'M' && $this->isAssistant)
                $result = true;
            //Участник?
            if ($value[0] == 'P' && $this->isMember)
                $result = true;
            //Ответственный (роль в задаче)
            if ($value[0] == 'TR' && $this->isResponse)
                $result = true;
            //Постановщик (роль в задаче)
            if ($value[0] == 'TA' && $this->isAuthor)
                $result = true;
            //Соисполнители (роль в задаче)
            if ($value[0] == 'TS' && $this->isAccomplice)
                $result = true;
            //Наблюдатели (роль в задаче)
            if ($value[0] == 'TN' && $this->isAudithor)
                $result = true;
        }

        if (!$result && $RightCode == "RT_READE"){
            if ($this->isManager || $this->isAssistant || $this->isReed)
                $result = true;
        }
        return $result;
    }

    private static function GetProject($projectId)
    {
        $result = [];
        $resOb = \Bitrix\Socialnetwork\WorkgroupTable::getList([
            'filter' => ['ID'=>$projectId],
            'select' => ['*',  'UF_*'],
            'order' => ['ID' => 'DESC'],
            'limit' => 1
        ]);

        if($res = $resOb->fetch()){
            if ($res['PROJECT_DATE_START'])
                $res['PROJECT_DATE_START'] = $res['PROJECT_DATE_START']->format('d.m.Y');
            if ($res['PROJECT_DATE_FINISH'])
                $res['PROJECT_DATE_FINISH'] = $res['PROJECT_DATE_FINISH']->format('d.m.Y');
            $result = $res;
        }
        return $result;
    }

    private static function GetTask($taskId)
    {
        $result = [];
        $resOb =  \Bitrix\Tasks\TaskTable::getList([
            'filter' => ['ID'=>$taskId],
            'select' => ['*',  'UF_*'],
            'order' => ['ID' => 'DESC'],
            'limit' => 1
        ]);

        if($res = $resOb->fetch()){
            $result = $res;
        }
        return $result;
    }

    private function GetProjectRole($projectId)
    {
        $resOb = \Bitrix\Socialnetwork\UserToGroupTable::getList([
            'filter' => ['GROUP_ID'=>$projectId],
            'select' => ['*'],
            'order' => ['ID' => 'DESC'],
        ]);
        while ($res = $resOb->fetch())
        {
            if ($res['USER_ID'] == $this->UserId && $res['ROLE'] == 'A')
                $this->isManager = true;

            if ($res['USER_ID'] == $this->UserId && $res['ROLE'] == 'E')
                $this->isAssistant = true;

            if ($res['USER_ID'] == $this->UserId && $res['ROLE'] == 'K')
                $this->isMember = true;
        }
    }

    public function ResponseError($msg = '')
    {
        $error = [
            "status" => "error",
            "data" => [],
            "errors" => [[
                "message" => $msg,
            ]],
        ];

        global $APPLICATION; $APPLICATION->RestartBuffer();
        echo json_encode($error);
        die();
    }

    public function ResponseText($msg = '')
    {
        global $APPLICATION; $APPLICATION->RestartBuffer();
        echo $msg;
        die();
    }

    /**
     * Удаление поручения
     **/
    public function isAllowResolutionDelete()
    {
        return $this->isAllowRight('RT_DELETE');
    }

    /**
     * Создание поручения
     **/
    public function isAllowResolutionAdd()
    {
        return $this->isAllowRight('RT_CREATE');
    }

    /**
     * просмотр поручения
     **/
    public function isAllowResolutionView()
    {
        return $this->isAllowRight('RT_READE');
    }
    /**
     * Обновление поручения
     **/
    public function isAllowResolutionUpdate()
    {
        return $this->isAllowRight('RT_UPDATE');
    }

    public static function DeleteProjectRight($projectId){
        if($projectId)
            self::DeleteRightByCode(self::OPTION_RESOLUTION_FIELD.'_P_'.$projectId);
    }



    private function GetTaskRole($TaskId)
    {
        $resOb = \Bitrix\Tasks\Internals\Task\MemberTable::getList([
            'filter' => ['TASK_ID' => $TaskId],
            'select' => ['*'],
        ]);
        while ($res = $resOb->fetch())
        {
            if ($res['USER_ID'] == $this->UserId && $res['TYPE'] == 'A')
                $this->isAccomplice = true;

            if ($res['USER_ID'] == $this->UserId && $res['TYPE'] == 'U')
                $this->isAudithor = true;

            if ($res['USER_ID'] == $this->UserId && $res['TYPE'] == 'R')
                $this->isResponse = true;

            if ($res['USER_ID'] == $this->UserId && $res['TYPE'] == 'O')
                $this->isAuthor = true;
        }
    }
    private function isReed($TaskId)
    {
        if(CModule::IncludeModule("intranet")){
            $arDeps = CIntranetUtils::GetSubordinateDepartmentsOld($this->UserId);
            $arUsers = CIntranetUtils::getDepartmentEmployees($arDeps, true);
            $Users = [];
            while($User = $arUsers->GetNext()){
                $Users[] = $User['ID'];
            }
            $resOb = \Bitrix\Tasks\Internals\Task\MemberTable::getList([
                'filter' => ['TASK_ID' => $TaskId, 'USER_ID' => $Users],
                'select' => ['*'],
            ]);
            if ($res = $resOb->fetch() && $Users)
            {
                $this->isReed = true;
            }
        }
    }

    public static function AllTasksViews($arTasks)
    {

        $ReturnTasksId = [];
        $TasksRights = [];
        $AllRight = [];

        $R = self::GetRightsByCode(self::OPTION_RESOLUTION_FIELD);
        foreach ($R as $item){
            $AllRight[$item['UF_ACCESS_GROUP_CODE']] = json_decode($item['UF_ACCESS_GROUP_VALUE'],true);
        }
        $Tasks = [];
        $Groups = [];
        foreach ($arTasks as $arTask){
            $Tasks[] = $arTask['ID'];
            $Groups[$arTask['ID']] = $arTask['GROUP_ID'];
            $TR = $AllRight[self::OPTION_RESOLUTION_FIELD."_T_".$arTask['ID']];
            $PR = $AllRight[self::OPTION_RESOLUTION_FIELD."_P_".$arTask['GROUP_ID']];
            foreach ($TR as $id => $right)
            {
                $PR[$id] = $right;
            }
            $TasksRights[$arTask['ID']] = $PR;
        }

        $TasksRole = self::GetTasksRole($Tasks);
        $GroupsRole = self::GetProjectsRole($Groups);
        $userGroups = self::GetUserGroups();

        foreach ($TasksRights as $id => $tasksRight){
            foreach ($tasksRight['RT_READE'] as $value){
                $value =  explode('_', $value);
                //Группа пользователя
                if ($value[0] == 'G' && in_array($value[1], $userGroups))
                    $ReturnTasksId[] = $id;
                //Пользователь
                if ($value[0] == 'U' && $value[1] == User::getId())
                    $ReturnTasksId[] = $id;
            }
            if ($TasksRole[$id]['isReed'] == 'Y')
                $ReturnTasksId[] = $id;
            if ($GroupsRole[$Groups[$id]]['isReed'] == 'Y')
                $ReturnTasksId[] = $id;
        }

        $ReturnTasksId = array_unique($ReturnTasksId);


        return $ReturnTasksId;
    }

    private static function GetTasksRole($TasksId)
    {
        if(CModule::IncludeModule("intranet")){
            $TasksRole = [];
            $arDeps = CIntranetUtils::GetSubordinateDepartmentsOld(User::getId(), true);
            $Users[] = User::getId();
            if ($arDeps){
                $arUsers = CIntranetUtils::getDepartmentEmployees($arDeps, true);
                while($User = $arUsers->GetNext()){
                    $Users[] = $User['ID'];
                }
            }

            $resOb = \Bitrix\Tasks\Internals\Task\MemberTable::getList([
                'filter' => ['TASK_ID' => $TasksId, 'USER_ID' => $Users],
                'select' => ['*'],
            ]);
            while ($res = $resOb->fetch())
            {
                $TasksRole[$res['TASK_ID']]['isReed'] = "Y";
            }
        }


        return $TasksRole;
    }

    protected static function GetProjectsRole($GroupsId){
        $GroupsRole = [];
        $resOb = \Bitrix\Socialnetwork\UserToGroupTable::getList([
            'filter' => ['GROUP_ID'=>$GroupsId, 'USER_ID' => User::getId()],
            'select' => ['*'],
            'order' => ['ID' => 'DESC'],
        ]);
        while ($res = $resOb->fetch())
        {
            if ($res['ROLE'] == 'A' || $res['ROLE'] == 'E')
                $GroupsRole[$res['GROUP_ID']]['isReed'] = "Y";
        }

        return $GroupsRole;
    }

}