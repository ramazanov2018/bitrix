<?php
namespace Rns\AccessMatrix;

class ProjectControls extends AccessMatrix
{
    private $UserId;
    public $project = [];
    private $userGroups = [];
    private $isManager = false;
    private $isMember = false;
    private $isAssitant = false;
    public $R = [];
    public function __construct($project = 0)
    {

        $this->UserId = self::CurrentUserId();
        $this->userGroups = self::GetUserGroups();
        $this->project = $this->GetProject($project);
        $this->GetProjectRole($project);

        $pre = '';
        if($project)
            $pre = '_'.$project;
        $this->R = self::Rights(self::OPTION_PROJECTS_FIELD.$pre);
    }

    public static function Rights($optionField)
    {
        return json_decode(self::GetRightByCode($optionField), true);
    }

    //Создание базового проекта
    public function isAllowBaseProjectCreate($defaultResult = true)
    {
        return $this->isAllowRight('BP_CREATE', $defaultResult);
    }
    //Создание стратегического проекта
    public function isAllowStrategyProjectCreate($defaultResult = true)
    {
        return $this->isAllowRight('SP_CREATE', $defaultResult);
    }
    //Удаление базового проекта
    public function isAllowBaseProjectDelete($defaultResult = true)
    {
        return $this->isAllowRight('BP_DELETE', $defaultResult);
    }
    //Удаление стратегического проекта
    public function isAllowStrategyProjectDelete($defaultResult = true)
    {
        return $this->isAllowRight('SP_DELETE', $defaultResult);
    }
    //Полное редактирование базового проекта
    public function isAllowBaseProjectUpdate($defaultResult = true)
    {
        return $this->isAllowRight('BP_UPDATE', $defaultResult);
    }
    //Полное редактирование стратегического проекта
    public function isAllowStrategyProjectUpdate($defaultResult = true)
    {
        return $this->isAllowRight('SP_UPDATE', $defaultResult);
    }
    //Внесение/редактирование сведений о рисках задач базового проекта
    public function isAllowBaseProjectRisc($defaultResult = true)
    {
        return $this->isAllowRight('BP_RISC', $defaultResult);
    }
    //Внесение/редактирование сведений о рисках задач стратегического проекта
    public function isAllowStrategyProjectRisc($defaultResult = true)
    {
        return $this->isAllowRight('SP_RISC', $defaultResult);
    }
    //Снятие у проекта признака "Стратегический"
    public function isAllowStrategiyUpdate($defaultResult = true)
    {
        return $this->isAllowRight('SP_STRATEGIC', $defaultResult);
    }
    //Установка и изменение сроков базового проекта
    public function isAllowBaseProjectDateUpdate($defaultResult = true)
    {
        return $this->isAllowRight('BP_DATE', $defaultResult);
    }
    //Установка и изменение сроков стратегического проекта
    public function isAllowStrategyProjectDateUpdate($defaultResult = true)
    {
        return $this->isAllowRight('SP_DATE', $defaultResult);
    }
    //Изменение состояния базового проекта
    public function isAllowBaseProjectStateUpdate($defaultResult = true)
    {
        return $this->isAllowRight('BP_STAT', $defaultResult);
    }
    //Изменение состояния стратегического проекта
    public function isAllowStrategyProjectStateUpdate($defaultResult = true)
    {
        return $this->isAllowRight('SP_STAT', $defaultResult);
    }
    //Добавление участников в базовый проект
    public function isAllowBaseProjectMembersUpdate($defaultResult = true)
    {
        return $this->isAllowRight('BP_MEMBER', $defaultResult);
    }
    //Добавление участников в стратегический проект
    public function isAllowStrategyProjectMembersUpdate($defaultResult = true)
    {
        return $this->isAllowRight('SP_MEMBER', $defaultResult);
    }
    //Экспорт списка базовых проектов
    public function isAllowBaseProjectExport($projectId, $defaultResult = true)
    {
        $this->GetProjectRole($projectId);
        return $this->isAllowRight('BP_EXPORT', $defaultResult);
    }
    //Экспорт списка стратегических проектов
    public function isAllowStrategyProjectExport($projectId, $defaultResult = true)
    {
        $this->GetProjectRole($projectId);
        return $this->isAllowRight('SP_EXPORT', $defaultResult);
    }
    //Привязка задачи к базовому проекту
    public function isAllowBaseProjectTask($defaultResult = true)
    {
        return $this->isAllowRight('BP_TASK', $defaultResult);
    }
    //Привязка задачи к стратегическому проекту
    public function isAllowStrategyProjectTask($defaultResult = true)
    {
        return $this->isAllowRight('SP_TASK', $defaultResult);
    }

    private function isAllowRight($RightCode, $defaultResult = true)
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
            //Руководитель?
            if ($value[0] == 'R' && $this->isManager)
                $result = true;
            //Помощник руководителя?
            if ($value[0] == 'M' && $this->isAssitant)
                $result = true;
            //Участник?
            if ($value[0] == 'P' && $this->isMember)
                $result = true;
            //Пользователь
            if ($value[0] == 'U' && $value[1] == $this->UserId)
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

    private function GetProjectRole($projectId)
    {
        $this->isManager = false;
        $this->isAssitant = false;
        $this->isMember = false;
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
                $this->isAssitant = true;

            if ($res['USER_ID'] == $this->UserId && $res['ROLE'] == 'K')
                $this->isMember = true;
        }
    }

    public function ResponseError($msg = '')
    {
        $error = [
            "MESSAGE" => "ERROR",
            "ERROR_MESSAGE" => $msg,
            "ERROR" => $msg,
            "ERROR_DATA" => [
                [
                    "message" => $msg,
                    "field" => "ERROR_FIELDS"
                ]

              ],
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

    public static function DeleteProjectRight($projectId){
        if($projectId)
            self::DeleteRightByCode(self::OPTION_PROJECTS_FIELD.'_'.$projectId);
    }

}
