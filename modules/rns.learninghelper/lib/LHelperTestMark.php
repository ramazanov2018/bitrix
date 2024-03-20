<?php

namespace LearningHelper;

class LHelperTestMark
{
    // 2012-04-13 Checked/modified for compatibility with new data model
    function CheckFields(&$arFields, $ID = false)
    {
        global $DB;
        $arMsg = [];

        if ((is_set($arFields, "MARK") || $ID === false) && (string)$arFields["MARK"] == '')
            $arMsg[] = ["id" => "MARK", "text" => GetMessage("LEARNING_BAD_MARK")];


        if (
            ($ID === false && !is_set($arFields, "TEST_ID"))
            ||
            (is_set($arFields, "TEST_ID") && intval($arFields["TEST_ID"]) < 1)
        ) {
            $arMsg[] = ["id" => "TEST_ID", "text" => GetMessage("LEARNING_BAD_TEST_ID")];
        } elseif (is_set($arFields, "TEST_ID")) {
            $res = LHelperTest::GetByID($arFields["TEST_ID"]);
            if (!$arRes = $res->Fetch())
                $arMsg[] = ["id" => "TEST_ID", "text" => GetMessage("LEARNING_BAD_TEST_ID")];
        }

        if (!is_set($arFields, "SCORE") || intval($arFields["SCORE"]) > 100 || intval($arFields["SCORE"]) < 1) {
            $arMsg[] = ["id" => "SCORE", "text" => GetMessage("LEARNING_BAD_MARK_SCORE")];
        }

        if (!empty($arMsg)) {
            $e = new \CAdminException($arMsg);
            $GLOBALS["APPLICATION"]->ThrowException($e);
            return false;
        }

        return true;
    }


    // 2012-04-13 Checked/modified for compatibility with new data model
    function Add($arFields)
    {
        global $DB;

        if ($this->CheckFields($arFields)) {
            unset($arFields["ID"]);

            $ID = $DB->Add("b_learn_test_mark", $arFields, ["DESCRIPTION"], "learning");

            return $ID;
        }

        return false;
    }


    // 2012-04-13 Checked/modified for compatibility with new data model
    function Update($ID, $arFields)
    {
        global $DB;

        $ID = intval($ID);
        if ($ID < 1) return false;


        if ($this->CheckFields($arFields, $ID)) {
            unset($arFields["ID"]);

            $arBinds = [
                "DESCRIPTION" => $arFields["DESCRIPTION"]
            ];

            $strUpdate = $DB->PrepareUpdate("b_learn_test_mark", $arFields, "learning");
            $strSql = "UPDATE b_learn_test_mark SET " . $strUpdate . " WHERE ID=" . $ID;
            $DB->QueryBind($strSql, $arBinds, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);

            return true;
        }
        return false;
    }


    // 2012-04-13 Checked/modified for compatibility with new data model
    public static function Delete($ID)
    {
        return \CLTestMark::Delete($ID);
    }


    // 2012-04-13 Checked/modified for compatibility with new data model
    public static function GetByID($ID)
    {
        return \CLTestMark::GetList($arOrder = [], $arFilter = ["ID" => $ID]);
    }


    // 2012-04-13 Checked/modified for compatibility with new data model
    public static function GetByPercent($TEST_ID, $PERCENT)
    {
        return \CLTestMark::GetByPercent($TEST_ID, $PERCENT);
    }


    // 2012-04-13 Checked/modified for compatibility with new data model
    public static function GetFilter($arFilter)
    {
        return \CLTestMark::GetFilter($arFilter);
    }


    // 2012-04-13 Checked/modified for compatibility with new data model

    /**
     * @param $arOrder
     * @param $arFilter
     * @return \CDBResult|false|null
     */
    public static function GetList($arOrder = [], $arFilter = [])
    {
        return \CLTestMark::GetList($arOrder, $arFilter);
    }
}
