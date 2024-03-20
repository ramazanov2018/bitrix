<?php

namespace LearningHelper;

class LHelperCCourse
{
    // 2012-04-17 Checked/modified for compatibility with new data model
    final public static function GetList($arOrder = [], $arFields = [], $arNavParams = [], $arSelect = [])
    {
        // Lists only lesson-courses
        $arFields = array_merge(['>LINKED_LESSON_ID' => 0], $arFields);

        foreach ($arOrder as $key => $value) {
            if (mb_strtoupper($key) === 'ID') {
                $arOrder['COURSE_ID'] = $arOrder[$key];
                unset ($arOrder[$key]);
            }
        }

        // We must replace '...ID' => '...COURSE_ID', where '...' is some operation (such as '!', '<=', etc.)
        foreach ($arFields as $key => $value) {
            // If key ends with 'ID'
            if ((mb_strlen($key) >= 2) && (mb_strtoupper(mb_substr($key, -2)) === 'ID')) {
                // And prefix before 'ID' doesn't contains letters
                if (!preg_match("/[a-zA-Z_]+/", mb_substr($key, 0, -2))) {
                    $prefix = '';
                    if (mb_strlen($key) > 2)
                        $prefix = mb_substr($key, 0, -2);

                    $arFields[$prefix . 'COURSE_ID'] = $arFields[$key];
                    unset ($arFields[$key]);
                }
            }
        }

        $arFields['#REPLACE_COURSE_ID_TO_ID'] = true;

        $res = \CLearnLesson::GetList($arOrder, $arFields, $arSelect, $arNavParams);
        return ($res);
    }

    public static function GetCourseTree($courseId){
        $tree =  \CLearnCacheOfLessonTreeComponent::GetData($courseId);
        $lessonsId = [];
        foreach ($tree as $item){
            $lessonsId[] = $item['LESSON_ID'];
        }
        $lessonData = [];
        $resOb = \CLearnLesson::GetList(array(), array('LESSON_ID' => $lessonsId), array('LESSON_ID', 'UF_LEARN_GALLERY'), array());
        while ($res = $resOb->Fetch()){
            $lessonData[$res['LESSON_ID']] = $res['UF_LEARN_GALLERY'];
        }

        foreach ($tree as &$item){
            $item['UF_LEARN_GALLERY'] = $lessonData[$item['LESSON_ID']];
        }
        return $tree;
    }
}
