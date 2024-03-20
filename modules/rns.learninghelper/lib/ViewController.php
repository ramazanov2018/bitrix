<?php

namespace LearningHelper;

class ViewController
{
    /**
     * @param int $lessonId
     * @return bool
     */
    public static function isLessonCompleted(int $lessonId):bool
    {
        $userId = self::CurrentUserId();
        if (!$userId)
            return false;

        $ID = LearnCompletionController::GetList(array(), array('LESSON_ID' => $lessonId, 'USER_ID' => $userId))->Fetch()["ID"];
        if ($ID)
            return true;

        return false;
    }

    /**
     * @param int $moduleId
     * @return bool
     */
    public static function isModuleLessonsCompleted(int $courseId, int $moduleId):bool
    {
        $userId = self::CurrentUserId();
        if (!$userId)
            return false;

        //Included chapters and lessons
        $rsContent = \CCourse::GetCourseContent($courseId, Array());
        $lessonsId = array();
        $foundChapter = false;
        while ($arContent = $rsContent->GetNext())
        {
            $arContent["LESSON_STATUS"] = "D";
            if ($foundChapter)
            {
                if ($arContent["DEPTH_LEVEL"] <= $baseDepthLevel)
                    break;

                $arContent["DEPTH_LEVEL"] -= $baseDepthLevel;

                if ($arContent["TYPE"] == "LE"){
                    $lessonsId[] = $arContent['ID'];
                }

            }

            if ($arContent["ID"]==$moduleId && $arContent["TYPE"]=="CH")
            {
                $foundChapter = true;
                $baseDepthLevel = $arContent["DEPTH_LEVEL"];
            }

        }
        $completedLID = array();
        $resOb = LearnCompletionController::GetList(array(), array('LESSON_ID' => $lessonsId, 'USER_ID' => $userId));
        while ($res = $resOb->Fetch()){
            $completedLID[] = (int)$res["LESSON_ID"];
        }

        foreach ($lessonsId as $value){
            if (!in_array($value, $completedLID))
                return false;
        }

        return true;
    }

    /**
     * @param int $moduleId
     * @return bool
     */
    public static function isModuleTestCompleted(int $courseId, int $moduleId):bool
    {
        $userId = self::CurrentUserId();
        if (!$userId)
            return false;

        $arFilter['COURSE_ID']         = $courseId;
        $arFilter['QUESTIONS_FROM_ID']            = $moduleId;
        $arFilter['ACTIVE']            = 'Y';
        $rsTest = (int)\CTest::GetList(
            array("SORT" => "ASC"),
            $arFilter,
        )->Fetch()["ID"];

        if ($rsTest){
            $attemptFilter = array("TEST_ID" => (int) $rsTest, "STUDENT_ID" => $userId, "COMPLETED" => "Y");
            if($res = \CTestAttempt::GetList(array(), $attemptFilter)->Fetch())
                return true;
            else
                return false;
        }

        return true;
    }

    /**
     * @param int $courseId
     * @param int $moduleId
     * @return bool
     */
    public static function isModuleCompleted(int $courseId, int $moduleId):bool
    {
        $userId = self::CurrentUserId();
        if (!$userId)
            return false;
        if (!self::isModuleLessonsCompleted($courseId, $moduleId))
            return false;
        if (!self::isModuleTestCompleted($courseId, $moduleId))
            return false;
        return true;
    }

    /**
     * @param int $courseId
     * @return bool
     */
    public static function isCourseCompleted(int $courseId):bool
    {
        $userId = self::CurrentUserId();
        if (!$userId)
            return false;
        return \CCertification::IsCourseCompleted($userId, $courseId);
    }

    /**
     * @param int $courseId
     * @return bool
     */
    public static function CourseControllerStart(int $courseId):bool
    {
        $userId = self::CurrentUserId();

        $arFilter['COURSE_ID']         = $courseId;
        $arFilter['ACTIVE']            = 'Y';
        $rsTest = (int)\CTest::GetList(
            array("SORT" => "ASC"),
            $arFilter,
        )->Fetch()["ID"];

        if ($userId && $rsTest)
            return true;

        return false;
    }

    protected static function CurrentUserId()
    {
        global $USER;
        if ($USER->IsAuthorized())
            return $USER->GetID();
        return false;
    }

    /**
     * @param $currentCourseId
     * @return bool
     */
    public static function isCourseIntroductionCompleted($currentCourseId){
        $rsEnum = \CUserFieldEnum::GetList(array(), array("USER_FIELD_NAME"=>"UF_COURSE_TYPE", "XML_ID" => "TYPE_INTRODUCTION"))->fetch()["ID"];

        $currentCourseAudince = (int)LHelperCCourse::GetList(
            array(),
            array('COURSE_ID' => $currentCourseId, "!UF_COURSE_TYPE" =>  $rsEnum),
            array(),
            array("ID", "UF_AUDIENCE")
        )->Fetch()["UF_AUDIENCE"];
        if ($currentCourseAudince)
        {
            $courseIntro = (int)LHelperCCourse::GetList(
                array(),
                array("UF_AUDIENCE" => $currentCourseAudince,"ACTIVE" => "Y", "UF_COURSE_TYPE" => $rsEnum),
                array(),
                array("ID", "UF_AUDIENCE", "UF_COURSE_TYPE")
            )->Fetch()["ID"];

            if ($courseIntro){
                return self::isCourseCompleted($courseIntro);
            }
        }
        return true;
    }
}