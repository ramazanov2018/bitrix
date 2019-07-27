<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Courses\CoursesSubscribe;
use Courses\CoursesControl;

class CoursesComponent extends CBitrixComponent
{
    public $course;
    public $courseID = false;
    public $courseSub;

    /**
     * @param $arParams
     * @return array|mixed
     * @throws \bitrix\main\LoaderException
     */
    public function onPrepareComponentParams($arParams)
    {
        //Подключим модуль Курсы
        \Bitrix\Main\Loader::includeModule('rns.courses');

        $this->courseSub = new CoursesSubscribe();

        //получаем данные переданные по Ajax
        $post = json_decode(file_get_contents("php://input"), true);

        ($post['course']) ? $this->course = trim($post['course']) : $this->course = "showList";
        ($post['courseID']) ? $this->courseID = $this->clearInt($post['courseID']) : false;

        ($arParams['CHECK_DATES']) ? $arParams['CHECK_DATES'] = trim($arParams['CHECK_DATES']) : $arParams['CHECK_DATES'] = 'Y';
        //($arParams['AJAX_SUB']) ? $arParams['AJAX_SUB'] = trim($arParams['AJAX_SUB']) : $arParams['AJAX_SUB'] = 'Y';//TODO Пересматривать

        return $arParams;
    }

    /**
     * @param $data
     * @return float|int
     */
    protected function clearInt($data)
    {
        return abs((int)$data);
    }

    public function executeComponent()
    {
        switch ($this->course) {
            case "showList";
                $this->showList();
                break;
            case "addMember";
                $this->addMember();
                break;
            case "removeMember";
                $this->removeMember();
                break;

        }
    }

    /**
     * @param $courseID
     * @return bool
     */
    protected function checkMember($courseID)
    {

        $arFilter = $this->returnArFilterSub($courseID);

        $parameters = array(
            'filter' => $arFilter
        );
        if ($result = CoursesSubscribe::getList($parameters)) {
            while ($res = $result->fetch())
                return true;

        }
        return false;
    }

    /**
     * @param $error
     */
    protected function returnError($error)
    {
        return $this->renderJson(['status' => 'error', 'error' => $error]);
    }

    protected function returnSuccess()
    {
        $this->renderJson(['status' => 'success']);
    }

    /**
     * @param $data
     */
    protected function renderJson($data)
    {
        global $APPLICATION;

        $APPLICATION->RestartBuffer();

        echo json_encode($data);

        exit;
    }

    /**
     * @param $courseID
     * @return array
     */
    protected function returnArFieldsSub($courseID){
        global $USER;
        $USER_ID = (int)$USER->GetID();

        $arFields["USER_ID"] = $USER_ID;
        $arFields["COURSE_ID"] = $courseID;

        return $arFields;
    }

    /**
     * @param $courseID
     * @return array
     */
    protected function returnArFilterSub($courseID){
        global $USER;
        $USER_ID = (int)$USER->GetID();

        $arFilter["USER_ID"] = $USER_ID;
        $arFilter["COURSE_ID"] = $courseID;

        return $arFilter;
    }

    public function showList()
    {
        $arFilter = [];
        $arFilter["ACTIVE"] = $this->arParams["CHECK_DATES"];
        $parameters = array(
            'filter' => $arFilter
        );

        $arCourses = [];
        $result = CoursesControl::getList($parameters);
        while ($course = $result->fetch()) {
            $arCourses[] = $course;
        }

        $i = 0;
        foreach ($arCourses as $arCourse) {
            $this->arResult["ITEMS"][$i] = $arCourse;
            if ($this->checkMember($arCourse["ID"]))
            {
                $this->arResult["ITEMS"][$i]["MEMBER"] = "Y";
            }else
            {
                $this->arResult["ITEMS"][$i]["MEMBER"] = "N";
            }
            ++$i;
        }

        $this->includeComponentTemplate();
    }

    public function addMember()
    {
        global $USER;

        if (!$USER->IsAuthorized())
            return $this->returnError(GetMessage("ERROR_YOU_NOT_AUTHORIZED"));

        if (!$this->courseID)
            return $this->returnError(GetMessage("ERROR_IN_MEMBER"));

        if (!$result = CoursesControl::getById($this->courseID))
            return $this->returnError(GetMessage("ERROR_COURSE_NOT_FOUND"));

        if ($this->checkMember($this->courseID))
            return $this->returnError(GetMessage("ERROR_YOU_SIGNED"));

        $arFields = $this->returnArFieldsSub($this->courseID);
        if ($id = $this->courseSub->add($arFields)) {
            return $this->returnSuccess();
        } else {
            return $this->returnError(GetMessage("ERROR_IN_MEMBER"));
        }
    }

    public function removeMember()
    {
        global $USER;

        if (!$USER->IsAuthorized())
            return $this->returnError(GetMessage("ERROR_YOU_NOT_AUTHORIZED"));

        if (!$this->courseID)
            return $this->returnError(GetMessage("ERROR_IN_MEMBER"));

        $USER_ID = (int)$USER->GetID();
        $arFields = Array(
            "USER_ID" => $USER_ID,
            "COURSE_ID" => $this->courseID,
        );

        if ($ID = $this->courseSub->delete($arFields))
        {
            $this->returnSuccess();
        }else{
            return $this->returnError(GetMessage("ERROR_IN_REMOVE_MEMBER"));
        };
    }
}