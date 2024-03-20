<?php

namespace LearningHelper;

class HelperCLQuestion
{
    function CheckFields(&$arFields, $ID = false)
    {
        global $DB, $USER;
        $arMsg = Array();

        if ( (is_set($arFields, "NAME") || $ID === false) && trim($arFields["NAME"]) == '')
            $arMsg[] = array("id"=>"NAME", "text"=> GetMessage("LEARNING_BAD_NAME"));


        if (is_set($arFields, "FILE_ID"))
        {
            $error = \CFile::CheckImageFile($arFields["FILE_ID"]);
            if ($error <> '')
                $arMsg[] = array("id"=>"FILE_ID", "text"=> $error);
        }

        if($this->LAST_ERROR == '')
        {
            if (
                ($ID === false && !is_set($arFields, "LESSON_ID"))
                ||
                (is_set($arFields, "LESSON_ID") && intval($arFields["LESSON_ID"]) < 1)
            )
            {
                $arMsg[] = array("id"=>"LESSON_ID", "text"=> GetMessage("LEARNING_BAD_LESSON_ID"));
            }
            elseif (is_set($arFields, "LESSON_ID"))
            {
                $res = \CLearnLesson::GetByID($arFields["LESSON_ID"]);
                if($arRes = $res->Fetch())
                {
                    $oAccess = \CLearnAccess::GetInstance($USER->GetID());

                    $bAccessLessonModify =
                        $oAccess->IsBaseAccess(\CLearnAccess::OP_LESSON_WRITE)
                        || $oAccess->IsLessonAccessible ($arFields["LESSON_ID"], \CLearnAccess::OP_LESSON_WRITE);

                    if ( ! $bAccessLessonModify )
                        $arMsg[] = array("id"=>"LESSON_ID", "text"=> GetMessage("LEARNING_BAD_LESSON_ID_EX"));
                }
                else
                {
                    $arMsg[] = array("id"=>"LESSON_ID", "text"=> GetMessage("LEARNING_BAD_LESSON_ID_EX"));
                }
            }
        }

        if(!empty($arMsg))
        {
            $e = new \CAdminException($arMsg);
            $GLOBALS["APPLICATION"]->ThrowException($e);
            return false;
        }

        if (is_set($arFields, "QUESTION_TYPE") && !in_array($arFields["QUESTION_TYPE"], Array("S", "M", "T", "R", ComparisonAnswer::CUESTION_TYPE)))
            $arFields["QUESTION_TYPE"] = "S";

        if (is_set($arFields, "DESCRIPTION_TYPE") && $arFields["DESCRIPTION_TYPE"] != "html")
            $arFields["DESCRIPTION_TYPE"] = "text";

        if (is_set($arFields, "DIRECTION") && $arFields["DIRECTION"] != "H")
            $arFields["DIRECTION"] = "V";

        if (is_set($arFields, "SELF") && $arFields["SELF"] != "Y")
            $arFields["SELF"] = "N";

        if (is_set($arFields, "ACTIVE") && $arFields["ACTIVE"] != "Y")
            $arFields["ACTIVE"] = "N";

        if (is_set($arFields, "EMAIL_ANSWER") && $arFields["EMAIL_ANSWER"] != "Y")
            $arFields["EMAIL_ANSWER"] = "N";

        if (is_set($arFields, "CORRECT_REQUIRED") && $arFields["CORRECT_REQUIRED"] != "Y")
            $arFields["CORRECT_REQUIRED"] = "N";

        return true;
    }

    function Add($arFields)
    {
        global $DB, $USER_FIELD_MANAGER;

        if (
            $this->CheckFields($arFields)
            && $USER_FIELD_MANAGER->CheckFields('LEARNING_QUESTIONS', 0, $arFields)
        )
        {
            unset($arFields["ID"]);

            if (
                array_key_exists("FILE_ID", $arFields)
                && is_array($arFields["FILE_ID"])
                && (
                    !array_key_exists("MODULE_ID", $arFields["FILE_ID"])
                    || $arFields["FILE_ID"]["MODULE_ID"] == ''
                )
            )
                $arFields["FILE_ID"]["MODULE_ID"] = "learning";

            \CFile::SaveForDB($arFields, "FILE_ID", "learning");

            $ID = $DB->Add("b_learn_question", $arFields, array("DESCRIPTION", 'COMMENT_TEXT', 'INCORRECT_MESSAGE'));

            if ($ID)
                $USER_FIELD_MANAGER->Update('LEARNING_QUESTIONS', $ID, $arFields);

            foreach(GetModuleEvents('learning', 'OnAfterQuestionAdd', true) as $arEvent)
                ExecuteModuleEventEx($arEvent, array($ID, $arFields));

            return $ID;
        }

        return false;
    }

    function Update($ID, $arFields)
    {
        global $DB, $USER_FIELD_MANAGER;

        $ID = intval($ID);
        if ($ID < 1) return false;

        if (is_set($arFields, "FILE_ID"))
        {
            if($arFields["FILE_ID"]["name"] == '' && $arFields["FILE_ID"]["del"] == '' && $arFields["FILE_ID"]["description"] == '')
                unset($arFields["FILE_ID"]);
            else
            {
                $pic_res = $DB->Query("SELECT FILE_ID FROM b_learn_question WHERE ID=".$ID);
                if($pic_res = $pic_res->Fetch())
                    $arFields["FILE_ID"]["old_file"]=$pic_res["FILE_ID"];
            }
        }

        if (
            $this->CheckFields($arFields, $ID)
            && $USER_FIELD_MANAGER->CheckFields('LEARNING_QUESTIONS', $ID, $arFields)
        )
        {
            unset($arFields["ID"]);

            $arBinds=Array(
                "DESCRIPTION"       => $arFields["DESCRIPTION"],
                'COMMENT_TEXT'      => $arFields['COMMENT_TEXT'],
                'INCORRECT_MESSAGE' => $arFields['INCORRECT_MESSAGE']
            );

            if (
                array_key_exists("FILE_ID", $arFields)
                && is_array($arFields["FILE_ID"])
                && (
                    !array_key_exists("MODULE_ID", $arFields["FILE_ID"])
                    || $arFields["FILE_ID"]["MODULE_ID"] == ''
                )
            )
                $arFields["FILE_ID"]["MODULE_ID"] = "learning";

            \CFile::SaveForDB($arFields, "FILE_ID", "learning");

            $USER_FIELD_MANAGER->Update('LEARNING_QUESTIONS', $ID, $arFields);
            $strUpdate = $DB->PrepareUpdate("b_learn_question", $arFields);
            if ($strUpdate !== '')
            {
                $strSql = "UPDATE b_learn_question SET ".$strUpdate." WHERE ID=".$ID;
                $DB->QueryBind($strSql, $arBinds, false, "File: ".__FILE__."<br>Line: ".__LINE__);
            }

            foreach(GetModuleEvents('learning', 'OnAfterQuestionUpdate', true) as $arEvent)
                ExecuteModuleEventEx($arEvent, array($ID, $arFields));

            return true;
        }
        return false;
    }

    public static function GetFilter($arFilter)
    {
        return \CLQuestion::GetFilter($arFilter);
    }

    public static function Delete($ID)
    {
        return \CLQuestion::Delete($ID);
    }

    public static function GetByID($ID)
    {
        return \CLQuestion::GetByID($ID);
    }

    public static function GetList($arOrder = array(), $arFilter = array(), $bHz = false, $arNavParams = array(), $arSelect = array())
    {
        return \CLQuestion::GetList($arOrder, $arFilter, $bHz, $arNavParams, $arSelect);
    }

    public static function GetCount($arFilter=Array())
    {
        return \CLQuestion::GetCount($arFilter);
    }

    public static function CreateAttemptQuestions($TEST_ID)
    {
        global $APPLICATION, $DB;

        $TEST_ID = intval($TEST_ID);

        $test = \CTest::GetByID($TEST_ID);
        if (!$arTest = $test->Fetch())
        {
            $APPLICATION->ThrowException(GetMessage("LEARNING_BAD_TEST_ID_EX"), "ERROR_NO_TEST_ID");
            return false;
        }

        /**
         * QUESTIONS_FROM values:
         * 'L' - X questions from every lesson in course
         * 'C' - X questions from every lesson from every chapter in the course
         *       In this case questions taken from immediate lessons of all chapters (X per chapter) in the course.
         *       In new data model it means, get X questions from every lesson in the course, except
         *       1) immediate lessons-childs of the course and
         *       2) lessons, contains other lessons (because, in old data model chapters doesn't contains questions)
         *
         * 'H' - all questions from the selected chapter (recursive) in the course
         *       This case must be ignored, because converter to new data model updates 'H' to 'R', but in case
         *       when chapter is not exists updates didn't become. So QUESTIONS_FROM stayed in 'H' value. And it means,
         *       that there is no chapter exists with QUESTIONS_FROM_ID, so we can't do work. And we should just
         *       ignore, for backward compatibility (so, don't throw an error).
         * 'S' - all questions from the selected lesson (unilesson_id in QUESTIONS_FROM_ID)
         * 'A' - all questions of the course (nothing interesting in QUESTIONS_FROM_ID)
         *
         * new values:
         * 'R' - all questions from the tree with root at selected lesson (include questions of selected lesson)
         *       in the course (unilesson_id in QUESTIONS_FROM_ID)
         */

        if ($arTest["QUESTIONS_FROM"] == "C" || $arTest["QUESTIONS_FROM"] == "L")
        {
            $courseId = $arTest['COURSE_ID'] + 0;
            $courseLessonId = \CCourse::CourseGetLinkedLesson ($courseId);
            if ($courseLessonId === false)
            {
                $APPLICATION->ThrowException(GetMessage("LEARNING_BAD_TEST_IS_EMPTY"), "ERROR_TEST_IS_EMPTY");
                return false;
            }

            $clauseAllChildsLessons = \CLearnHelper::SQLClauseForAllSubLessons ($courseLessonId);

            if ($arTest["QUESTIONS_FROM"] == "C")	// X questions from every lessons from every chapter in the course
            {
                $strSql =
                    "SELECT Q.ID as QUESTION_ID, TLEUP.SOURCE_NODE as FROM_ID
				FROM b_learn_lesson L
				INNER JOIN b_learn_question Q ON L.ID = Q.LESSON_ID
				INNER JOIN b_learn_lesson_edges TLEUP ON L.ID = TLEUP.TARGET_NODE
				LEFT OUTER JOIN b_learn_lesson_edges TLEDOWN ON L.ID = TLEDOWN.SOURCE_NODE "
                    . "WHERE L.ID IN (" . $clauseAllChildsLessons . ") \n"		// only lessons from COURSE_ID = $arTest['COURSE_ID']
                    . " AND TLEDOWN.SOURCE_NODE IS NULL \n"						// exclude lessons, contains other lessons ("chapters")

                    // include lessons in current course tree context only (and exclude immediate childs of course)
                    . " AND TLEUP.SOURCE_NODE IN (" . $clauseAllChildsLessons . ") \n"

                    . " AND Q.ACTIVE = 'Y' "		// active questions only
                    . ($arTest["INCLUDE_SELF_TEST"] != "Y" ? "AND Q.SELF = 'N' " : "")
                    . "ORDER BY ".($arTest["RANDOM_QUESTIONS"] == "Y" ? \CTest::GetRandFunction() : "L.SORT, Q.SORT, L.ID");
            }
            else	// 'L' X questions from every lesson in course
            {
                $strSql =
                    "SELECT Q.ID as QUESTION_ID, L.ID as FROM_ID ".
                    "FROM b_learn_lesson L ".
                    "INNER JOIN b_learn_question Q ON L.ID = Q.LESSON_ID ".
                    "WHERE L.ID IN (" . $clauseAllChildsLessons . ") AND Q.ACTIVE = 'Y' ".
                    ($arTest["INCLUDE_SELF_TEST"] != "Y" ? "AND Q.SELF = 'N' " : "").
                    "ORDER BY ".($arTest["RANDOM_QUESTIONS"] == "Y" ? \CTest::GetRandFunction() : "L.SORT, Q.SORT, L.ID");
            }

            if (!$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
                return false;

            $Values = Array();
            $tmp = Array();
            while ($arRecord = $res->Fetch())
            {
                if (is_set($tmp, $arRecord["FROM_ID"]))
                {
                    if ($tmp[$arRecord["FROM_ID"]] < $arTest["QUESTIONS_AMOUNT"])
                        $tmp[$arRecord["FROM_ID"]]++;
                    else
                        continue;
                }
                else
                {
                    $tmp[$arRecord["FROM_ID"]] = 1;
                }
                $Values[]= $arRecord["QUESTION_ID"];
            }

            if (empty($Values))
            {
                $APPLICATION->ThrowException(GetMessage("LEARNING_BAD_TEST_IS_EMPTY"), "ERROR_TEST_IS_EMPTY");
                return false;
            }

            return $Values;
        }
        elseif (($arTest["QUESTIONS_FROM"] == "H" || $arTest["QUESTIONS_FROM"] == "S" || $arTest["QUESTIONS_FROM"] == "R") && $arTest["QUESTIONS_FROM_ID"])
        {
            $WHERE = '';
            if ($arTest["QUESTIONS_FROM"] == "H")
            {
                /**
                 * 'H' - all questions from the selected chapter (recursive) in the course
                 *       This case must be ignored, because converter to new data model updates 'H' to 'R', but in case
                 *       when chapter is not exists updates didn't become. So QUESTIONS_FROM stayed in 'H' value. And it means,
                 *       that there is no chapter exists with QUESTIONS_FROM_ID, so we can't do work. And we should just
                 *       ignore, for backward compatibility (so, don't throw an error).
                 */
                $APPLICATION->ThrowException(GetMessage("LEARNING_BAD_TEST_IS_EMPTY"), "ERROR_TEST_IS_EMPTY");
                return false;
            }
            elseif ($arTest["QUESTIONS_FROM"] == 'R')	// all questions from the tree with root at selected lesson (include questions of selected lesson) in the course (unilesson_id in QUESTIONS_FROM_ID)
            {
                $clauseAllChildsLessons = \CLearnHelper::SQLClauseForAllSubLessons ($arTest['QUESTIONS_FROM_ID']);
                $WHERE = " (L.ID IN(" . $clauseAllChildsLessons . ") OR (L.ID = " . ($arTest['QUESTIONS_FROM_ID'] + 0) . ")) ";
            }
            elseif ($arTest["QUESTIONS_FROM"] == 'S')	// 'S' - all questions from the selected lesson (unilesson_id in QUESTIONS_FROM_ID)
            {
                $clauseAllChildsLessons = $arTest["QUESTIONS_FROM_ID"] + 0;
                $WHERE = " (L.ID IN(" . $clauseAllChildsLessons . ") OR (L.ID = " . ($arTest['QUESTIONS_FROM_ID'] + 0) . ")) ";
            }
            else
            {
                return (false);
            }

            $strSql =
                "SELECT Q.ID AS QUESTION_ID ".
                "FROM b_learn_lesson L ".
                "INNER JOIN b_learn_question Q ON L.ID = Q.LESSON_ID ".
                "WHERE " . $WHERE . " AND Q.ACTIVE = 'Y' ".
                ($arTest["INCLUDE_SELF_TEST"] != "Y" ? "AND Q.SELF = 'N' " : "").
                "ORDER BY ".($arTest["RANDOM_QUESTIONS"] == "Y" ? \CTest::GetRandFunction() : "L.SORT, Q.SORT, L.ID ").
                ($arTest["QUESTIONS_AMOUNT"] > 0 ? "LIMIT ".$arTest["QUESTIONS_AMOUNT"] :"");

            $success = false;
            $rsQuestions = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

            $strSql = '';
            if ($rsQuestions)
            {
                $arSqlSubstrings = array();
                while ($arQuestion = $rsQuestions->fetch())
                    $arSqlSubstrings[] = $arQuestion['QUESTION_ID'];

                if (empty($arSqlSubstrings))
                {
                    $APPLICATION->ThrowException(GetMessage("LEARNING_BAD_TEST_IS_EMPTY"), "ERROR_TEST_IS_EMPTY");
                    return false;
                }
                return $arSqlSubstrings;
            }
        }elseif ($arTest["QUESTIONS_FROM"] == 'A')
        {
            $courseId = $arTest['COURSE_ID'] + 0;
            $courseLessonId = \CCourse::CourseGetLinkedLesson ($courseId);
            if ($courseLessonId === false)
            {
                $APPLICATION->ThrowException(GetMessage("LEARNING_BAD_TEST_IS_EMPTY"), "ERROR_TEST_IS_EMPTY");
                return false;
            }

            $clauseAllChildsLessons = \CLearnHelper::SQLClauseForAllSubLessons ($courseLessonId);

            $strSql =
                "SELECT Q.ID AS QUESTION_ID
			FROM b_learn_lesson L
			INNER JOIN b_learn_question Q ON L.ID = Q.LESSON_ID
			WHERE (L.ID IN (" . $clauseAllChildsLessons . ") OR (L.ID = " . ($courseLessonId + 0) . ") )
			AND Q.ACTIVE = 'Y' "
                . ($arTest["INCLUDE_SELF_TEST"] != "Y" ? "AND Q.SELF = 'N' " : "").
                "ORDER BY " . ($arTest["RANDOM_QUESTIONS"] == "Y" ? \CTest::GetRandFunction() : "L.SORT, Q.SORT, L.ID ").
                ($arTest["QUESTIONS_AMOUNT"] > 0 ? "LIMIT " . ($arTest["QUESTIONS_AMOUNT"] + 0) : "");


            $success = false;
            $rsQuestions = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

            $strSql = '';
            if ($rsQuestions)
            {
                $arSqlSubstrings = array();
                while ($arQuestion = $rsQuestions->fetch())
                    $arSqlSubstrings[] = $arQuestion['QUESTION_ID'];

                if (empty($arSqlSubstrings))
                {
                    $APPLICATION->ThrowException(GetMessage("LEARNING_BAD_TEST_IS_EMPTY"), "ERROR_TEST_IS_EMPTY");
                    return false;
                }
                return $arSqlSubstrings;

            }
        }

        return false;
    }

}