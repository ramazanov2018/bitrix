<?php

namespace LearningHelper;

class LearnCompletionController
{
    /**
     * @param $arFields
     * @param $ID
     * @return bool
     */
    protected static function CheckFields(&$arFields, $ID = false)
    {
        global $DB;
        $arMsg = Array();

        global $DB;
        $arMsg = Array();

        if (
            ($ID === false && !is_set($arFields, "LESSON_ID"))
            ||
            (is_set($arFields, "LESSON_ID") && intval($arFields["LESSON_ID"]) < 1)
        )
        {
            $arMsg[] = array("id"=>"LESSON_ID", "text"=> GetMessage("LEARNING_BAD_QUESTION_ID"));
        }
        if (
            ($ID === false && !is_set($arFields, "USER_ID"))
            ||
            (is_set($arFields, "USER_ID") && intval($arFields["USER_ID"]) < 1)
        )
        {
            $arMsg[] = array("id"=>"USER_ID", "text"=> GetMessage("LEARNING_BAD_QUESTION_ID"));
        }

        elseif (is_set($arFields, "LESSON_ID"))
        {
            $res = \CLearnLesson::GetList(
            array(),
            array(
                'LESSON_ID'         => $arFields['LESSON_ID'],
                'ACTIVE'            => 'Y',
                'CHECK_PERMISSIONS' => 'N'
            ),
            array(),
            );

            if(!$arRes = $res->Fetch())
                $arMsg[] = array("id"=>"LESSON_ID", "text"=> GetMessage("LEARNING_BAD_QUESTION_ID"));
        }

        if(!empty($arMsg))
        {
            $e = new \CAdminException($arMsg);
            $GLOBALS["APPLICATION"]->ThrowException($e);
            return false;
        }

        return true;
    }

    public static function Add($arFields)
    {
        global $DB;

        if(self::CheckFields($arFields))
        {
            unset($arFields["ID"]);
            $resOb = self::GetList(array(), array("LESSON_ID" => $arFields["LESSON_ID"], "USER_ID" => $arFields["USER_ID"]));
            if($res = $resOb->Fetch())
                return $res["ID"];

            $ID = $DB->Add("b_learn_status", $arFields, Array(), "learning");

            return $ID;
        }

        return false;
    }

    /*public static function Delete($ID)
    {
        global $DB;

        $ID = intval($ID);
        if ($ID < 1) return false;

        $strSql = "DELETE FROM b_learn_h_comparison_answer WHERE ID = ".$ID;

        if (!$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
            return false;

        return true;
    }*/

    public static function GetFilter($arFilter)
    {
        if (!is_array($arFilter))
            $arFilter = Array();

        $arSqlSearch = Array();

        foreach ($arFilter as $key => $val)
        {
            $key = mb_strtoupper($key);

            switch ($key)
            {
                case "ID":
                case "LESSON_ID":
                case "USER_ID":
                    $arSqlSearch[] = \CLearnHelper::FilterCreate("LS.".$key, $val, "number", $bFullJoin, $cOperationType);
                    break;
            }

        }

        return $arSqlSearch;
    }

    public static function GetList($arOrder=Array(), $arFilter=Array())
    {
        global $DB, $USER;

        $arSqlSearch = self::GetFilter($arFilter);

        $strSqlSearch = "";
        for($i=0; $i<count($arSqlSearch); $i++)
            if($arSqlSearch[$i] <> '')
                $strSqlSearch .= " AND ".$arSqlSearch[$i]." ";

        $strSql =
            "SELECT LS.*".
            "FROM b_learn_status LS ".
            "WHERE 1=1 ".
            $strSqlSearch;

        if (!is_array($arOrder))
            $arOrder = Array();

        $arSqlOrder = [];

        $strSqlOrder = "";
        DelDuplicateSort($arSqlOrder);
        for ($i=0; $i<count($arSqlOrder); $i++)
        {
            if($i==0)
                $strSqlOrder = " ORDER BY ";
            else
                $strSqlOrder .= ",";

            $strSqlOrder .= $arSqlOrder[$i];
        }

        $strSql .= $strSqlOrder;

        return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
    }

    public static function IsPassedLessonCurrUser($lessonId)
    {
        global $USER;
        if(!$USER->IsAuthorized())
            return false;

        $resOb = self::GetList(array(), array("LESSON_ID" => $lessonId, "USER_ID" => $USER->GetID()));
        if(!$res = $resOb->Fetch())
            return false;

        return true;
    }
}