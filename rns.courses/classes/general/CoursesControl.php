<?php
namespace Courses;

use Bitrix\Main;
IncludeModuleLangFile(__FILE__);

class CoursesControlTable extends Main\Entity\DataManager
{

    public static function getTableName()
    {
        return 'b_courses_list';
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
            'CODE' => array(
                'data_type' => 'string',
                'required' => false,
                'title' =>"Символьный код",
            ),
            'NAME' => array(
                'data_type' => 'string',
                'required' => false,
                'title' => "Название курса",
            ),
            'DESCRIPTION' => array(
                'data_type' => 'text',
                'required' => false,
                'title' => "Описание курса",
            ),
            'PRICE' => array(
                'data_type' => 'integer',
                'required' =>false,
                'title' => "Цена курса",
            ),
            'SORT' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => "Сортировка",
                'values' => 100
            ),
            'ACTIVE' => array(
                'data_type' => 'string',
                'required' => false,
                'title' => "Активность",
            ),

            'DATE_START' => array(
                'data_type' => 'string',
                'required' => false,
                'title' => "Дата началы курса",
            ),
            'DATE_END' => array(
                'data_type' => 'string',
                'required' => false,
                'title' => "Дата заверщения курса",
            ),
        );
    }
}


class CoursesControl
{

    private $LAST_ERROR = "";
    function CheckFields($arFields)
    {
        $this->LAST_ERROR = "";
        $aMsg = array();
        if (strlen($arFields["NAME"]) == 0)
        {
            $aMsg[] = array(
                "id" => "NAME",
                "text" => GetMessage("CLASS_COURSE_ERR_NAME")
            );

        }

        if(!empty($aMsg))
        {
            $e = new CAdminException($aMsg);
            $GLOBALS["APPLICATION"]->ThrowException($e);
            $this->LAST_ERROR = $e->GetString();
            return false;
        }
        return true;
    }

    function add($arFields)
    {

        if(!$this->CheckFields($arFields)) {
            return false;
        }
        $DBManager = CoursesControlTable::add($arFields);
        $ID = $DBManager->getId();
        return $ID;
    }

    function getList($parameters){
        return CoursesControlTable::getList($parameters);
    }

    function getById($Id){
        return CoursesControlTable::getById($Id);
    }
    function update($Id, $arFields){
        if(!$this->CheckFields($arFields)) {
            return false;
        }
        $DBManager = CoursesControlTable::update($Id, $arFields);
        $ID = $DBManager->getId();
        return $ID;
    }
    function delete($Id){
        return CoursesControlTable::delete($Id);
    }
}