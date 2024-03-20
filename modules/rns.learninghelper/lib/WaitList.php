<?php
namespace LearningHelper;

class WaitList
{
    //ID курса для привязки к листу ожидания
    const COURSE_WAITLIST = 1000000000;

    /**
     * @return array Список пользователей из листа ожидания
     */
    public static function GetWaitList(){
        global $DB;
        $waitList = array();

        $WaitListGroupId = self::GetWaitListGroupId();
        if ($WaitListGroupId == 0)
            return $waitList;

        $arOrder = array('DATE_CREATE' => 'asc');
        $arFilter = ["(LGM.LEARNING_GROUP_ID = '".$WaitListGroupId."')"];
        $arSelect = array('*');
        $arNavParams = array();
        $arFields = array(
            'LEARNING_GROUP_ID'  => 'LGM.LEARNING_GROUP_ID',
            'USER_ID'            => 'LGM.USER_ID',
            'DATE_CREATE'        => 'LGM.DATE_CREATE',
        );

        if (count($arSelect) <= 0 || in_array("*", $arSelect))
            $arSelect = array_keys($arFields);

        if (!is_array($arOrder))
            $arOrder = array();

        $arSqlOrder = [];
        foreach ($arOrder as $by => $order)
        {
            $by = (string) $by;
            $needle = null;
            $order = mb_strtolower($order);

            if ($order != "asc")
                $order = "desc";

            if (array_key_exists($by, $arFields))
            {
                $arSqlOrder[] = ' ' . $by . ' ' . $order . ' ';
                $needle = $by;
            }

            if (
                ($needle !== null)
                && ( ! in_array($needle, $arSelect, true) )
            )
            {
                $arSelect[] = $needle;
            }
        }

        $arSqlSelect = array();
        foreach ($arSelect as $field)
        {
            $field = mb_strtoupper($field);
            if (array_key_exists($field, $arFields))
                $arSqlSelect[$field] = $arFields[$field] . ' AS ' . $field;
        }

        if (!sizeof($arSqlSelect))
            $arSqlSelect[] = 'LGM.USER_ID AS USER_ID';

        $strSql = "
			SELECT 
				" . implode(",\n", $arSqlSelect);

        $strFrom = "
			FROM
				b_learn_groups_member LGM
				"
            . " WHERE " . implode(" AND ", $arFilter) . " ";

        $strSql .= $strFrom;

        $strSqlOrder = "";
        DelDuplicateSort($arSqlOrder);
        for ($i = 0, $arSqlOrderCnt = count($arSqlOrder); $i < $arSqlOrderCnt; $i++)
        {
            if ($i == 0)
                $strSqlOrder = " ORDER BY ";
            else
                $strSqlOrder .= ",";

            $strSqlOrder .= $arSqlOrder[$i];
        }

        $strSql .= $strSqlOrder;

        if (count($arNavParams))
        {
            if (isset($arNavParams['nTopCount']))
            {
                $strSql = $DB->TopSql($strSql, (int) $arNavParams['nTopCount']);
                $res = $DB->Query($strSql, $bIgnoreErrors = false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
            }
            else
            {
                $res_cnt = $DB->Query("SELECT COUNT(LGM.ID) as C " . $strFrom);
                $res_cnt = $res_cnt->Fetch();
                $res = new CDBResult();
                $rc = $res->NavQuery($strSql, $res_cnt["C"], $arNavParams, $bIgnoreErrors = false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
            }
        }
        else
        {
            $res = $DB->Query($strSql, $bIgnoreErrors = false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
        }

        while ($r = $res->fetch()){
            $waitList[] = $r;
        }
        return $waitList;
    }

    protected static function GetWaitListGroupId()
    {
        $res = 0;
        $rOb = \CLearningGroup::GetList(array($by => $order), array('COURSE_LESSON_ID' => self::COURSE_WAITLIST));
        if ($r = $rOb->Fetch()){
            $res = (int)$r['ID'];
        }
        return $res;
    }

    public static function WaitListImport($groupId)
    {
        global $USER_FIELD_MANAGER;
        $memberCount = (int)$USER_FIELD_MANAGER->GetUserFieldValue('LEARNING_LGROUPS', 'UF_MEMBERS_COUNT', $groupId);

        $groupMembers = self::GetGroupMembers($groupId);
        $waitList = self::GetWaitList();

        foreach($waitList as $member)
        {
            if (count($groupMembers) > 0 && count($groupMembers) == $memberCount){
                if (in_array($member['USER_ID'],$groupMembers))
                    \CLearningGroupMember::delete($member['USER_ID'], $member['LEARNING_GROUP_ID']);

                continue;
            }

            if (in_array($member['USER_ID'],$groupMembers) || self::MemberAdd($member['USER_ID'], $groupId)){
                if (!in_array($member['USER_ID'],$groupMembers))
                    $groupMembers[] = $member['USER_ID'];
                \CLearningGroupMember::delete($member['USER_ID'], $member['LEARNING_GROUP_ID']);
            }

        }
    }

    protected static function MemberAdd($member, $groupId)
    {
        return \CLearningGroupMember::add(array(
            'USER_ID'           => $member,
            'LEARNING_GROUP_ID' => $groupId
        ));
    }
    protected static function GetGroupMembers($groupId)
    {
        $groupMembers = [];
        $rOb = \CLearningGroupMember::getList(
            array(),	// arOrder
            array('LEARNING_GROUP_ID' => $groupId),	// arFilter
            array('USER_ID')	// arSelect
        );

        while($r = $rOb->fetch()){
            if (!in_array($r['USER_ID'],$groupMembers))
                $groupMembers[] = $r['USER_ID'];
        }
        return $groupMembers;
    }
}