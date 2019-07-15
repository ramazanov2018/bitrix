<?php
namespace Courses;

use Bitrix\Main;
IncludeModuleLangFile(__FILE__);

class CoursesSubscribeTable extends Main\Entity\DataManager
{

    public static function getTableName()
    {
        return 'b_courses_subscribe';
    }

    public static function getMap()
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
                'title' => 'ИД',
            ),
            'USER_ID' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => "ID Пользователя",
            ),
            'COURSE_ID' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => "ID Курса",
            ),
            'DATE_CREATE' => array(
                'data_type' => 'datetime',
                'required' => true,
                'default_value' => new \Bitrix\Main\Type\DateTime(null, 0),
                'title' => "дата с временем",
            ),
        );
    }
}

class CoursesSubscribe
{
    function CheckFields($arFields)
    {
        if (empty($arFields ['COURSE_ID']) && empty($arFields ['USER_ID'])){
            return false;
        }

        $arFields ['COURSE_ID'] = (int) $arFields ['COURSE_ID'];
        $arFields ['USER_ID'] = (int) $arFields ['USER_ID'];

        $parameters = array(
            'filter' => array(
                'COURSE_ID' => $arFields['COURSE_ID'],
                'USER_ID' => $arFields['USER_ID']
            )
        );
        $result = $this->getList($parameters);
        while ($row = $result->fetch())
        {
            return false;
        }
        return true;
    }

    function add($arFields)
    {
        if(!$this->CheckFields($arFields)) {
            return false;
        }
        $DBManager = CoursesSubscribeTable::add($arFields);
        $ID = $DBManager->getId();
        return $ID;
    }

    function getList($parameters){
        return CoursesSubscribeTable::getList($parameters);
    }

    function getCourseID($arFields){
        if (empty($arFields ['COURSE_ID']) && empty($arFields ['USER_ID'])){
            return false;
        }

        $arFields ['COURSE_ID'] = (int) $arFields ['COURSE_ID'];
        $arFields ['USER_ID'] = (int) $arFields ['USER_ID'];

        $parameters = array(
            'filter' => array(
                'COURSE_ID' => $arFields['COURSE_ID'],
                'USER_ID' => $arFields['USER_ID']
            )
        );
        $result = $this->getList($parameters);
        $courseID = "";
        while ($row = $result->fetch())
        {
            $courseID = $row["ID"];
        }
        return $courseID;
    }
    function delete($arFields){
        if(!$this->getCourseID($arFields)) {
            return false;
        }else{
            $primary = $this->getCourseID($arFields);
        }
        return CoursesSubscribeTable::delete($primary);

    }
}
?>