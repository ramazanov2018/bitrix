<?php
namespace Rns\Analytic;

use CAccess;
use CJSCore;
use CUtil;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Page\Asset;

class Access
{
    protected const RIGHTS_IDS = [
            "CLOSE" => 11,
            "READE" => 22,
            "WRITE" => 33,
    ];

    public const OPTION_FIELD_NAME = 'public_rights';
    public const RNSANALYTICS_OPT_ACTIVE = 'RNSANALYTICS_OPT_ACTIVE';
    public const RNSANALYTICS_OPT_API_URL = 'RNSANALYTICS_OPT_API_URL';

    protected static $module_id = 'rns.analytics';

    public static function AnalyticsShowRights($variable_name, $arPossibleRights, $arActualRights, $bDefault = false, $bForceInherited = false, $arSelected = [], $arHighLight = [])
    {
        global $APPLICATION;
        Asset::getInstance()->addJs('/bitrix/js/iblock/iblock_edit.js');

        $entity_type = ''; $iblock_id =''; $id =''; $section_title='';
        $js_var_name = preg_replace("/[^a-zA-Z0-9_]/", "_", $variable_name);
        $html_var_name = htmlspecialcharsbx($variable_name);

        $sSelect = '<select name="'.$html_var_name.'[][TASK_ID]" style="vertical-align:middle">';
        foreach($arPossibleRights as $value => $title)
            $sSelect .= '<option value="'.htmlspecialcharsbx($value).'">'.htmlspecialcharsex($title).'</option>';
        $sSelect .= '</select>';

        if($bForceInherited != true)
        {
            foreach($arActualRights as $RIGHT_ID => $arRightSet)
                if($arRightSet["IS_INHERITED"] <> "Y")
                    $arSelected[$arRightSet["GROUP_CODE"]] = true;
        }

        $table_id = $variable_name."_table";
        $href_id = $variable_name."_href";

        CJSCore::Init(['access']);
        ?>
        <tr>
            <td colspan="3" align="center">
                <script type="text/javascript">
                    var obIBlockAccess_<?=$js_var_name?> = new JCIBlockAccess(
                        '<?=CUtil::JSEscape($entity_type)?>',
                        <?=intval($iblock_id)?>,
                        <?=intval($id)?>,
                        <?=CUtil::PhpToJsObject($arSelected)?>,
                        '<?=CUtil::JSEscape($variable_name)?>',
                        '<?=CUtil::JSEscape($table_id)?>',
                        '<?=CUtil::JSEscape($href_id)?>',
                        '<?=CUtil::JSEscape($sSelect)?>',
                        <?=CUtil::PhpToJsObject($arHighLight)?>
                    );
                </script>
                <table width="100%" class="internal" id="<?echo htmlspecialcharsbx($table_id)?>" align="center">
                    <?if($section_title != ""):?>
                        <tr id="<?echo $html_var_name?>_heading" class="heading">
                            <td colspan="2">
                                <?echo $section_title?>
                            </td>
                        </tr>
                    <?endif?>
                    <?
                    $arNames = [];
                    foreach($arActualRights as $arRightSet)
                        $arNames[] = $arRightSet["GROUP_CODE"];

                    $access = new CAccess();
                    $arNames = $access->GetNames($arNames);

                    foreach($arActualRights as $RIGHT_ID => $arRightSet)
                    {
                        if($bForceInherited || $arRightSet["IS_INHERITED"] == "Y")
                        {
                            ?>
                            <tr class="<?echo $html_var_name?>_row_for_<?echo htmlspecialcharsbx($arRightSet["GROUP_CODE"])?><?if($arRightSet["IS_OVERWRITED"] == "Y") echo " iblock-strike-out";?>">
                                <td style="width:40%!important; text-align:right"><?echo htmlspecialcharsex($arNames[$arRightSet["GROUP_CODE"]]["provider"]." ".$arNames[$arRightSet["GROUP_CODE"]]["name"])?>:</td>
                                <td align="left">
                                    <?if($arRightSet["IS_OVERWRITED"] != "Y"):?>
                                        <input type="hidden" name="<?echo $html_var_name?>[][RIGHT_ID]" value="<?echo htmlspecialcharsbx($RIGHT_ID)?>">
                                        <input type="hidden" name="<?echo $html_var_name?>[][GROUP_CODE]" value="<?echo htmlspecialcharsbx($arRightSet["GROUP_CODE"])?>">
                                        <input type="hidden" name="<?echo $html_var_name?>[][TASK_ID]" value="<?echo htmlspecialcharsbx($arRightSet["TASK_ID"])?>">
                                    <?endif;?>
                                    <?echo htmlspecialcharsex($arPossibleRights[$arRightSet["TASK_ID"]])?>
                                </td>
                            </tr>
                            <?
                        }
                    }

                    if($bForceInherited != true)
                    {
                        foreach($arActualRights as $RIGHT_ID => $arRightSet)
                        {
                            if($arRightSet["IS_INHERITED"] <> "Y")
                            {
                                ?>
                                <tr>
                                    <td style="width:40%!important; text-align:right; vertical-align:middle"><?echo htmlspecialcharsex($arNames[$arRightSet["GROUP_CODE"]]["provider"]." ".$arNames[$arRightSet["GROUP_CODE"]]["name"])?>:</td>
                                    <td align="left">
                                        <input type="hidden" name="<?echo $html_var_name?>[][RIGHT_ID]" value="<?echo htmlspecialcharsbx($RIGHT_ID)?>">
                                        <input type="hidden" name="<?echo $html_var_name?>[][GROUP_CODE]" value="<?echo htmlspecialcharsbx($arRightSet["GROUP_CODE"])?>">
                                        <select name="<?echo $html_var_name?>[][TASK_ID]" style="vertical-align:middle">
                                            <?foreach($arPossibleRights as $value => $title):?>
                                                <option value="<?echo htmlspecialcharsbx($value)?>" <?if($value == $arRightSet["TASK_ID"]) echo "selected"?>><?echo htmlspecialcharsex($title)?></option>
                                            <?endforeach?>
                                        </select>
                                        <a href="javascript:void(0);" onclick="JCIBlockAccess.DeleteRow(this, '<?=htmlspecialcharsbx(CUtil::addslashes($arRightSet["GROUP_CODE"]))?>', '<?=CUtil::JSEscape($variable_name)?>')" class="access-delete"></a>
                                        <?if($bDefault):?>
                                            <span title="<?echo GetMessage("IBLOCK_AT_OVERWRITE_TIP")?>"><?
                                                if(
                                                    is_array($arRightSet["OVERWRITED"])
                                                    && $arRightSet["OVERWRITED"][0] > 0
                                                    && $arRightSet["OVERWRITED"][1] > 0
                                                )
                                                {
                                                    ?>
                                                    <br><input name="<?echo $html_var_name?>[][DO_CLEAN]" value="Y" type="checkbox"><?echo GetMessage("IBLOCK_AT_OVERWRITE_1")?> (<?echo intval($arRightSet["OVERWRITED"][0]+$arRightSet["OVERWRITED"][1])?>)
                                                    <?
                                                }
                                                elseif(
                                                    is_array($arRightSet["OVERWRITED"])
                                                    && $arRightSet["OVERWRITED"][0] > 0
                                                )
                                                {
                                                    ?>
                                                    <br><input name="<?echo $html_var_name?>[][DO_CLEAN]" value="Y" type="checkbox"><?echo GetMessage("IBLOCK_AT_OVERWRITE_2")?> (<?echo intval($arRightSet["OVERWRITED"][0])?>)
                                                    <?
                                                }
                                                elseif(
                                                    is_array($arRightSet["OVERWRITED"])
                                                    && $arRightSet["OVERWRITED"][1] > 0
                                                )
                                                {
                                                    ?>
                                                    <br><input name="<?echo $html_var_name?>[][DO_CLEAN]" value="Y" type="checkbox"><?echo GetMessage("IBLOCK_AT_OVERWRITE_3")?> (<?echo intval($arRightSet["OVERWRITED"][1])?>)
									<?
                                                }?></span>
                                        <?endif;?>
                                    </td>
                                </tr>
                                <?
                            }
                        }
                    }
                    ?>
                    <tr>
                        <td width="40%" align="right">&nbsp;</td>
                        <td width="60%" align="left">
                            <a href="javascript:void(0)"  id="<?echo htmlspecialcharsbx($href_id)?>" class="bx-action-href"><?=Loc::getMessage("RIGHT_BUTTON_ADD")?></a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <?
    }

    public static function Post2Array($ar)
    {
        $arRights = [];
        $RIGHT_ID = "";
        $i = 0;
        if (!empty($ar) && is_array($ar))
        {
            foreach ($ar as $arRight)
            {
                if (isset($arRight["RIGHT_ID"]))
                {
                    $RIGHT_ID = "n".$i++;

                    $arRights[$RIGHT_ID] = [
                        "GROUP_CODE" => "",
                        "DO_CLEAN" => "N",
                        "TASK_ID" => 0,
                    ];
                }
                elseif (isset($arRight["GROUP_CODE"]))
                {
                    $arRights[$RIGHT_ID]["GROUP_CODE"] = $arRight["GROUP_CODE"];
                }
                elseif (isset($arRight["DO_CLEAN"]))
                {
                    $arRights[$RIGHT_ID]["DO_CLEAN"] = $arRight["DO_CLEAN"] == "Y" ? "Y" : "N";
                }
                elseif (isset($arRight["TASK_ID"]))
                {
                    $arRights[$RIGHT_ID]["TASK_ID"] = $arRight["TASK_ID"];
                }
            }
        }

        foreach($arRights as $RIGHT_ID => $arRightSet)
        {
            if(mb_substr($RIGHT_ID, 0, 1) == "n")
            {
                if($arRightSet["GROUP_CODE"] == '')
                    unset($arRights[$RIGHT_ID]);
                elseif($arRightSet["TASK_ID"] > 0)
                {
                    //Mark inherited rights to overwrite
                    foreach($arRights as $RIGHT_ID2 => $arRightSet2)
                    {
                        if(
                            (int)$RIGHT_ID2 > 0
                            && $arRightSet2["GROUP_CODE"] === $arRightSet["GROUP_CODE"]
                        )
                        {
                            unset($arRights[$RIGHT_ID2]);
                        }
                    }
                }
            }
        }

        return $arRights;
    }

    public static function GetArrRightsPublic()
    {
        $rights = [];
        foreach (self::RIGHTS_IDS as $key => $ID){
            $rights[$ID] = Loc::getMessage("RIGHT_VALUE_".$key);
        }
        return $rights;
    }

    public static function GetCurrentUserRightId()
    {
        $CurUserRightID = 0;

        global $USER;
        $userID = $USER->GetID();
        $OptionValue = self::GetOptionValue();

        //Если админ - полный доступ
        if ($USER->IsAdmin())
            $CurUserRightID = self::RIGHTS_IDS["WRITE"];

        //установлены права на пользователя?
        if ($CurUserRightID == 0)
            $CurUserRightID = self::isUserRight($userID, $OptionValue);

        //установлены права на группу пользователя?
        if ($CurUserRightID == 0)
            $CurUserRightID = self::isUserGroupRight($userID, $OptionValue);

        //по умолчании закрыто
        if ($CurUserRightID == 0)
            $CurUserRightID = self::RIGHTS_IDS["CLOSE"];

        return $CurUserRightID;

    }

    protected static function GetOptionValue()
    {
        $ArRights = [];
        $OptionValue = Option::get(self::$module_id, Access::OPTION_FIELD_NAME);

        if (strlen($OptionValue) > 0){
            $OptionValue = unserialize($OptionValue);
            foreach ($OptionValue as $value){
                $ArRights[$value["GROUP_CODE"]] = $value;
            }
        }

        return $ArRights;
    }

    protected static function isUserRight($userID, $OptionValue)
    {
        if (array_key_exists("U".$userID, $OptionValue) && in_array($OptionValue["U".$userID]["TASK_ID"], self::RIGHTS_IDS))
            return $OptionValue["U".$userID]["TASK_ID"];

        return 0;
    }

    protected static function isUserGroupRight($userID, $OptionValue)
    {
        $grRight = 0;
        $arGroups = \CUser::GetUserGroup($userID); // ID групп текущего пользователя
        foreach ($arGroups as $curUserGroupID) {
            if (array_key_exists("G".$curUserGroupID, $OptionValue)
                && in_array($OptionValue["G".$curUserGroupID]["TASK_ID"], self::RIGHTS_IDS)
                && $OptionValue["G".$curUserGroupID]["TASK_ID"] > $grRight){
                $grRight = $OptionValue["G".$curUserGroupID]["TASK_ID"];
            }
        }
        return $grRight;
    }

    public static function GetRightByKey($key){
        return self::RIGHTS_IDS[$key];
    }

    public static function MenuShow()
    {
        $show = false;

        $Active = Option::get(self::$module_id, Access::RNSANALYTICS_OPT_ACTIVE);
        $apiUrl = Option::get(self::$module_id, Access::RNSANALYTICS_OPT_API_URL).'/';

        if (self::GetCurrentUserRightId()!=self::GetRightByKey("CLOSE") && $Active == "Y" && self::isDomainAvailible($apiUrl) && self::isModuleInstalled()){
            $show = true;
        }
        return $show;
    }

    private static function isModuleInstalled()
    {
        if(\Bitrix\Main\ModuleManager::isModuleInstalled(self::$module_id) && \Bitrix\Main\Loader::includeModule(self::$module_id))
            return true;

        return false;
    }

    private static function isDomainAvailible($domain)
    {
        //проверка на валидность урла
        if(!filter_var($domain, FILTER_VALIDATE_URL)){
            return false;
        }
        //инициализация curl
        $curlInit = curl_init($domain);
        curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,10);
        curl_setopt($curlInit,CURLOPT_HEADER,true);
        curl_setopt($curlInit,CURLOPT_NOBODY,true);
        curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);
        //получение ответа
        $response = curl_exec($curlInit);

        if (!curl_errno($curlInit)) {
            switch ($http_code = curl_getinfo($curlInit, CURLINFO_HTTP_CODE)) {
                case 200:  # OK
                    curl_close($curlInit);
                    return true;
                default:
                    curl_close($curlInit);
                    return false;
            }
        }
        curl_close($curlInit);
        return false;
    }

}