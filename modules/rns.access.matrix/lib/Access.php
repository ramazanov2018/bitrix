<?php
namespace Rns\AccessMatrix;

use Bitrix\Main\GroupTable;
use Bitrix\Main\UserTable;
use Bitrix\Tasks\TaskTable;
use CAccess;
use CJSCore;
use CUtil;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;

/**
 *
 */
class Access extends AccessMatrix
{

    public static function ShowRights($variable_name, $arPossibleRights, $arActualRights, $bDefault = false, $bForceInherited = false, $arSelected = [], $arHighLight = [])
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
                            <a href="javascript:void(0)"  id="<?echo htmlspecialcharsbx($href_id)?>" class="bx-action-href">Добавить</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <?
    }

    public static function Post2Array($ar, $type = '')
    {
        $arRights = [];
        if (!empty($ar) && is_array($ar))
        {
            switch ($type){
                case "PROJECT":
                    $arRights = self::Post2ArrayProject($ar);
                    break;
                case "TASK":
                    $arRights = self::Post2ArrayTask($ar);
                    break;
                case "RESOLUTIONS":
                    $arRights = self::Post2ArrayResolution($ar);
                    break;
                default:
                    $arRights = self::Post2ArrayDefault($ar);

            }
        }

        return $arRights;
    }

    private static function Post2ArrayDefault(array $ar = array()):array
    {
        $arRights = [];
        $RIGHT_ID = "";
        $i = 0;
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

    private static function Post2ArrayProject(array $ar = array()):array
    {
        $arResult = [];
        //Базовые проекты
        $rights = [];
        $projectsCreateRights = [];
        foreach ($ar["BP_PROJECTS_TASK_ID"] as $rightId => $arRight)
        {
            $groups = self::Explode($arRight);
            if($groups && $rightId != "BP_CREATE" && $rightId != "BP_EXPORT")
                $rights[$rightId] = $groups;
            if ($rightId == "BP_CREATE" || $rightId == "BP_EXPORT" && $groups)
                $projectsCreateRights[$rightId] = $groups;
        }

        $projects =  self::Explode($ar["BP_PROJECTS"]);

        foreach ($projects as $project){
            $item = (int)explode('_', $project)[1];

            if ($item)
                $arResult['PROJECTS'][$item] = $rights;
        }

        //Стратегические проекты
        $rights = [];
        foreach ($ar["SP_PROJECTS_TASK_ID"] as $rightId => $arRight)
        {
            $groups = self::Explode($arRight);
            if($groups && $rightId != "SP_CREATE" && $rightId != "SP_EXPORT")
                $rights[$rightId] = $groups;
            if (($rightId == "SP_CREATE" || $rightId == "SP_EXPORT") && $groups)
                $projectsCreateRights[$rightId] = $groups;
        }

        $projects = self::Explode( $ar["SP_PROJECTS"]);

        foreach ($projects as $project){
            $item = (int)explode('_', $project)[1];

            if ($item)
                $arResult['PROJECTS'][$item] = $rights;
        }
        $projectsCreateRights = array_filter($projectsCreateRights);
        $arResult['BASE'] = $projectsCreateRights;



        return $arResult;
    }

    private static function Post2ArrayResolution(array $ar = array()):array
    {
        $arResult = [];
        $rights = [];
        foreach ($ar["TASK_ID"] as $rightId => $arRight)
        {
            $groups = self::Explode($arRight);

            if($groups)
                $rights[$rightId] = $groups;
        }

        $projects = self::Explode( $ar["PROJECTS"]);


        foreach ($projects as $project){
            $item = (int)explode('_', $project)[1];

            if ($item)
                $arResult['PROJECTS'][$item] = $rights;
        }

        $tasks = self::Explode( $ar["RESOLUTIONS"]);

        foreach ($tasks as $tasks){
            $item = (int)explode('_', $tasks)[1];

            if ($item)
                $arResult['TASKS'][$item] = $rights;
        }

        return $arResult;
    }

    private static function Post2ArrayTask(array $ar = array()):array
    {
        $arResult = [];
        //Базовые проекты
        $rights = [];
        foreach ($ar["BP_PROJECTS_TASK_ID"] as $rightId => $arRight)
        {
            $groups = self::Explode($arRight);

            if($groups)
                $rights[$rightId] = $groups;
        }
        $projects = self::Explode($ar["BP_PROJECTS"]);

        foreach ($projects as $project){
            $item = (int)explode('_', $project)[1];

            if ($item)
                $arResult['PROJECTS'][$item] = $rights;
        }

        $tasks = self::Explode($ar["BT_TASKS"]);

        foreach ($tasks as $tasks){
            $item = (int)explode('_', $tasks)[1];

            if ($item)
                $arResult['TASKS'][$item] = $rights;
        }

        //Стратегические проекты
        $rights = [];
        foreach ($ar["SP_PROJECTS_TASK_ID"] as $rightId => $arRight)
        {
            $groups = self::Explode($arRight);

            if($groups)
                $rights[$rightId] = $groups;
        }

        $projects = self::Explode($ar["SP_PROJECTS"]);
        foreach ($projects as $project){
            $item = (int)explode('_', $project)[1];

            if ($item)
                $arResult['PROJECTS'][$item] = $rights;
        }

        $tasks = self::Explode($ar["ST_TASKS"]);

        foreach ($tasks as $tasks){
            $item = (int)explode('_', $tasks)[1];

            if ($item)
                $arResult['TASKS'][$item] = $rights;
        }


        return $arResult;
    }
    /**
     * Описание прав
     **/
    public static function GetArrRightsPublic($code, $pre = '')
    {
        $rights = [];
        foreach (self::RIGHTS_IDS[$code] as $key){
            $rights[$key] = Loc::getMessage("RIGHT_VALUE_".$pre.$key);
        }
        return $rights;
    }
    /**
     * Форма права на дашборды
     **/
    public static function ShowAccessDashboard()
    {
        $arDashboards[] = ['FIELD_NAME' => self::OPTION_DASHBOARD_KP_FIELD, 'TITLE' => 'Дашбоард КП'];
        $arDashboards[] = ['FIELD_NAME' => self::OPTION_DASHBOARD_GOSTEH_FIELD, 'TITLE' => 'Дашбоард Гостех'];
        $arDashboards[] = ['FIELD_NAME' => self::OPTION_DASHBOARD_DK, 'TITLE' => 'Дашборд ДК'];
        $arDashboards[] = ['FIELD_NAME' => self::OPTION_DASHBOARD_PROJECT, 'TITLE' => 'Дашборд проекта'];
        $arDashboards[] = ['FIELD_NAME' => self::OPTION_DASHBOARD_PROJECTS, 'TITLE' => 'Дашборд всех проектов'];
        foreach ($arDashboards as $item):
            $public_right = self::GetRightByCode($item['FIELD_NAME']);
            if (strlen($public_right) > 0)
                $public_right = json_decode($public_right, true);
            else
                $public_right = [];

            $rights = Access::GetArrRightsPublic('DASHBOARDS');
            ob_start();
            Access::ShowRights($item['FIELD_NAME'], $rights, $public_right);
            $rights_html = ob_get_contents();
            ob_end_clean();
            ?>
            <tr class="heading">
                <td colspan="4"><?= $item['TITLE']?></td>
            </tr>
            <?echo $rights_html;?>
        <?endforeach;

    }
    /**
     * Форма права на настраиваемые дашборды
     **/
    public static function ShowAccessDashboardCustomizable()
    {
        $public_right = self::GetRightByCode( Access::OPTION_DASHBOARD_CUSTOM_FIELD);

        if (strlen($public_right) > 0)
            $public_right = json_decode($public_right, true);
        else
            $public_right = [];

        $rights = Access::GetArrRightsPublic('DASHBOARDS_CUSTOM');
        ob_start();
        Access::ShowRights(Access::OPTION_DASHBOARD_CUSTOM_FIELD, $rights, $public_right);
        $rights_html = ob_get_contents();
        ob_end_clean();
        echo $rights_html;
    }
    /**
     * Форма права на учет трудозатрать
     **/
    public static function ShowAccessTimeman()
    {?>
        <tr class="heading">
            <td colspan="4">Пользователи</td>
        </tr>
        <?
        $selected = json_decode(self::GetRightByCode(Access::OPTION_TIMEMAN_FIELD.'_USERS'), true);
        $selected = self::Explode($selected['USERS']);
        $arSelected = [];
        foreach ($selected as $item){
            $arSelected[] = ['user', (int)$item];
        }


        $AccessTabs = self::CtreateTabs(['user']);
        $AccessItems = self::CtreateTabItems(['user']);
        ?>
        <tr>
            <td colspan="1" width="20%">Пользователи</td>
            <td colspan="3">
                <?self::ShowSelectorDialog(self::OPTION_TIMEMAN_FIELD.'[USERS]', $AccessTabs, $AccessItems, $arSelected)?>
            </td>
        </tr>
        <tr class="heading">
            <td colspan="4">Права</td>
        </tr>
        <?
        $public_right = self::GetRightByCode(Access::OPTION_TIMEMAN_FIELD);

        if (strlen($public_right) > 0)
            $public_right = json_decode($public_right, true);
        else
            $public_right = [];

        $rights = Access::GetArrRightsPublic('TIMEMAN');
        ob_start();
        Access::ShowRights(Access::OPTION_TIMEMAN_FIELD.'[RIGHTS]', $rights, $public_right);
        $rights_html = ob_get_contents();
        ob_end_clean();
        echo $rights_html;
    }
    /**
     * Форма права на проекты
     **/
    public static function ShowAccessProject()
    {
        $AccessTabs = self::CtreateTabs(['projectsGroup', 'users']);
        $BProjectTabs = self::CtreateTabs(['BaseProjects']);
        $SProjectTabs = self::CtreateTabs(['StrategicProjects']);
        $AccessItems = self::CtreateTabItems(['projectsGroup', 'users']);
        $BProjectItems = self::CtreateTabItems(['BaseProjects']);
        $SProjectItems = self::CtreateTabItems(['StrategicProjects']);
        ob_start();
        ?>
        <tr class="heading">
            <td colspan="4"><a target="_blank" href="show_access.php?lang=<?=LANGUAGE_ID?>&access_code=<?=self::OPTION_PROJECTS_FIELD?>">Сохраненные права</a></td>
        </tr>
        <tr class="heading">
            <td colspan="4">Базовые проекты</td>
        </tr>
        <tr>
            <td colspan="1">Проекты</td>
            <td colspan="3">
                <?self::ShowSelectorDialog(self::OPTION_PROJECTS_FIELD.'[BP_PROJECTS]', $BProjectTabs, $BProjectItems)?>
            </td>
        </tr>
        <tr class="heading">
            <td colspan="4">Права</td>
        </tr>
        <?php
        $r = self::GetArrRightsPublic('BP_PROJECTS')
        ?>
        <?foreach ($r as $key => $value){?>
            <tr>
                <td colspan="1" width="20%"><?=$value?></td>
                <td colspan="3">
                    <?self::ShowSelectorDialog(self::OPTION_PROJECTS_FIELD.'[BP_PROJECTS_TASK_ID]['.$key.']', $AccessTabs, $AccessItems)?>
                </td>
            </tr>
        <?}?>

        <tr class="heading">
            <td colspan="4">Стратегические проекты</td>
        </tr>
        <tr>
            <td colspan="1">Проекты</td>
            <td colspan="3">
                <?self::ShowSelectorDialog(self::OPTION_PROJECTS_FIELD.'[SP_PROJECTS]', $SProjectTabs, $SProjectItems)?>
            </td>
        </tr>
        <tr class="heading">
            <td colspan="4">Права</td>
        </tr>
        <?php
        $r = self::GetArrRightsPublic('SP_PROJECTS')
        ?>
        <?foreach ($r as $key => $value){?>
            <tr>
                <td colspan="1" width="20%"><?=$value?></td>
                <td colspan="3">
                    <?self::ShowSelectorDialog(self::OPTION_PROJECTS_FIELD.'[SP_PROJECTS_TASK_ID]['.$key.']', $AccessTabs, $AccessItems)?>
                </td>
            </tr>
        <?}?>
        <?php $rights_html = ob_get_contents();
        ob_end_clean();
        echo $rights_html;
    }
    public static function ShowSavedAccessProject($arAccess = array())
    {
        $AccessTabs = self::CtreateTabs(['projectsGroup', 'users']);
        $AccessItems = self::CtreateTabItems(['projectsGroup', 'users']);
        $ProjectsItems = self::CtreateTabItems(['Projects']);
        //$arAccess = self::GetRightsByCode(self::OPTION_PROJECTS_FIELD);
        ob_start();
        foreach ($arAccess as $value){
            $R = json_decode($value['UF_ACCESS_GROUP_VALUE'], true);
            if(empty($R))
                continue;

            $Title = '';
            if ($value['UF_ACCESS_GROUP_CODE'] == self::OPTION_PROJECTS_FIELD) {
                $Title = 'Общие права';
            }else{
                $projId = (int)end(explode('_',$value['UF_ACCESS_GROUP_CODE']));
                foreach ($ProjectsItems as $item)
                    if("P_".$projId == $item["id"])
                        $Title = "Проект: ".$item["title"];

            }

            ?>

            <tr class="heading">
                <td colspan="4"><?=$Title?></td>
            </tr>
            <?php
            foreach ($R as $key=>$item){?>
                <tr>
                    <td colspan="1" width="20%"><?=Loc::getMessage("RIGHT_VALUE_".$key)?></td>
                    <td colspan="3">
                        <?self::ShowSavedSelectorDialog($value['UF_ACCESS_GROUP_CODE']."_".$key, $AccessTabs, $AccessItems, $item);?>
                    </td>
                </tr>
            <?php }
            ?>
        <?php
        }
        ?>
        <?php $rights_html = ob_get_contents();
        ob_end_clean();
        echo $rights_html;
    }
    /**
     * Форма права на задачи
     **/
    public static function ShowAccessTasks()
    {
        $AccessTabs = self::CtreateTabs(['tasksGroup', 'users']);
        $BProjectTabs = self::CtreateTabs(['Projects']);
        $BTaskTabs = self::CtreateTabs(['Tasks']);
        $AccessItems = self::CtreateTabItems(['tasksGroup', 'users']);
        $BProjectItems = self::CtreateTabItems(['Projects']);
        $BTaskItems = self::CtreateTabItems(['Tasks']);
        //$BProjectTabs = self::CtreateTabs(['BaseProjects']);
        //$SProjectTabs = self::CtreateTabs(['StrategicProjects']);
        //$BTaskTabs = self::CtreateTabs(['BaseTasks']);
        //$STaskTabs = self::CtreateTabs(['StrategicTasks']);
        /*$BProjectItems = self::CtreateTabItems(['BaseProjects']);
        $SProjectItems = self::CtreateTabItems(['StrategicProjects']);
        $BTaskItems = self::CtreateTabItems(['BaseTasks']);
        $STaskItems = self::CtreateTabItems(['StrategicTasks']);*/
        ob_start();
        ?>
        <tr class="heading">
            <td colspan="4"><a target="_blank" href="show_access.php?lang=<?=LANGUAGE_ID?>&access_code=<?=self::OPTION_TASKS_FIELD?>">Сохраненные права</a></td>
        </tr>
        <tr class="heading">
            <td colspan="4">Задачи</td>
        </tr>
        <tr>
            <td colspan="1">Проекты</td>
            <td colspan="3">
                <?self::ShowSelectorDialog(self::OPTION_TASKS_FIELD.'[BP_PROJECTS]', $BProjectTabs, $BProjectItems)?>
            </td>
        </tr>
        <tr>
            <td colspan="1">Задачи</td>
            <td colspan="3">
                <?self::ShowSelectorDialog(self::OPTION_TASKS_FIELD.'[BT_TASKS]', $BTaskTabs, $BTaskItems)?>
            </td>
        </tr>

        <tr class="heading">
            <td colspan="4">Права</td>
        </tr>
        <?php
        $r = self::GetArrRightsPublic('BT_TASKS')
        ?>
        <?foreach ($r as $key => $value){?>
            <tr>
                <td colspan="1" width="20%"><?=$value?></td>
                <td colspan="3">
                    <?self::ShowSelectorDialog(self::OPTION_TASKS_FIELD.'[BP_PROJECTS_TASK_ID]['.$key.']', $AccessTabs, $AccessItems)?>
                </td>
            </tr>
        <?}?>
        <?php $rights_html = ob_get_contents();
        ob_end_clean();
        echo $rights_html;
    }
    public static function ShowSavedAccessTasks($arAccess = array())
    {
        $AccessTabs = self::CtreateTabs(['tasksGroup', 'users']);
        $AccessItems = self::CtreateTabItems(['tasksGroup', 'users']);
        $ProjectsItems = self::CtreateTabItems(['Projects']);
        $TasksItems = self::CtreateTabItems(['Tasks']);

        //$arAccess = self::GetRightsByCode(self::OPTION_TASKS_FIELD);
        ob_start();
        foreach ($arAccess as $value){
            $R = json_decode($value['UF_ACCESS_GROUP_VALUE'], true);
            if(empty($R))
                continue;

            $Title = '';
            $array = explode('_', $value['UF_ACCESS_GROUP_CODE']);
            $elementId = (int)end($array);
            if (str_contains($value['UF_ACCESS_GROUP_CODE'], '_P_')) {
                foreach ($ProjectsItems as $item)
                    if("P_".$elementId == $item["id"])
                        $Title = "Проект: ".$item["title"];
            }
            if (str_contains($value['UF_ACCESS_GROUP_CODE'], '_T_')) {
                foreach ($TasksItems as $item)
                    if("T_".$elementId == $item["id"])
                        $Title = "Задача: ".$item["title"];
            }

            ?>

            <tr class="heading">
                <td colspan="4"><?=$Title?></td>
            </tr>
            <?php
            foreach ($R as $key=>$item){?>
                <tr>
                    <td colspan="1" width="20%"><?=Loc::getMessage("RIGHT_VALUE_".$key)?></td>
                    <td colspan="3">
                        <?self::ShowSavedSelectorDialog($value['UF_ACCESS_GROUP_CODE']."_".$key, $AccessTabs, $AccessItems, $item);?>
                    </td>
                </tr>
            <?php }
            ?>
            <?php
        }
        ?>
        <?php $rights_html = ob_get_contents();
        ob_end_clean();
        echo $rights_html;
    }
    /**
     * Форма права на поручения
     **/
    public static function ShowAccessResolutions()
    {
        $AccessTabs = self::CtreateTabs(['tasksGroup', 'users']);
        $ProjectTabs = self::CtreateTabs(['Projects']);
        $TaskTabs = self::CtreateTabs(['Resolutions']);
        $AccessItems = self::CtreateTabItems(['tasksGroup', 'users']);
        $ProjectItems = self::CtreateTabItems(['Projects']);
        $TaskItems = self::CtreateTabItems(['Resolutions']);
        ob_start();
        ?>
        <tr class="heading">
            <td colspan="4"><a target="_blank" href="show_access.php?lang=<?=LANGUAGE_ID?>&access_code=<?=self::OPTION_RESOLUTION_FIELD?>">Сохраненные права</a></td>
        </tr>
        <tr class="heading">
            <td colspan="4">Поручения</td>
        </tr>
        <tr>
            <td colspan="1">Проекты</td>
            <td colspan="3">
                <?self::ShowSelectorDialog(self::OPTION_RESOLUTION_FIELD.'[PROJECTS]', $ProjectTabs, $ProjectItems)?>
            </td>
        </tr>
        <tr>
            <td colspan="1">Поручения</td>
            <td colspan="3">
                <?self::ShowSelectorDialog(self::OPTION_RESOLUTION_FIELD.'[RESOLUTIONS]', $TaskTabs, $TaskItems)?>
            </td>
        </tr>

        <tr class="heading">
            <td colspan="4">Права</td>
        </tr>
        <?php
        $r = self::GetArrRightsPublic('RESOLUTIONS')
        ?>
        <?foreach ($r as $key => $value){?>
            <tr>
                <td colspan="1" width="20%"><?=$value?></td>
                <td colspan="3">
                    <?self::ShowSelectorDialog(self::OPTION_RESOLUTION_FIELD.'[TASK_ID]['.$key.']', $AccessTabs, $AccessItems)?>
                </td>
            </tr>
        <?}?>
        <?php $rights_html = ob_get_contents();
        ob_end_clean();
        echo $rights_html;
    }
    public static function ShowSavedAccessResolutions($arAccess = array())
    {
        $AccessTabs = self::CtreateTabs(['tasksGroup', 'users']);
        $AccessItems = self::CtreateTabItems(['tasksGroup', 'users']);
        $ProjectsItems = self::CtreateTabItems(['Projects']);
        $TasksItems = self::CtreateTabItems(['Resolutions']);
        //$arAccess = self::GetRightsByCode(self::OPTION_RESOLUTION_FIELD);
        ob_start();
        foreach ($arAccess as $value){$R = json_decode($value['UF_ACCESS_GROUP_VALUE'], true);
            if(empty($R))
                continue;

            $Title = '';
            $array = explode('_', $value['UF_ACCESS_GROUP_CODE']);
            $elementId = (int)end($array);
            if (str_contains($value['UF_ACCESS_GROUP_CODE'], '_P_')) {
                foreach ($ProjectsItems as $item)
                    if("P_".$elementId == $item["id"])
                        $Title = "Проект: ".$item["title"];
            }
            if (str_contains($value['UF_ACCESS_GROUP_CODE'], '_T_')) {
                foreach ($TasksItems as $item)
                    if("R_".$elementId == $item["id"])
                        $Title = "Поручение: ".$item["title"];
            }

            ?>

            <tr class="heading">
                <td colspan="4"><?=$Title?></td>
            </tr>
            <?php
            $R = json_decode($value['UF_ACCESS_GROUP_VALUE'], true);
            foreach ($R as $key=>$item){?>
                <tr>
                    <td colspan="1" width="20%"><?=Loc::getMessage("RIGHT_VALUE_".$key)?></td>
                    <td colspan="3">
                        <?self::ShowSavedSelectorDialog($value['UF_ACCESS_GROUP_CODE']."_".$key, $AccessTabs, $AccessItems, $item);?>
                    </td>
                </tr>
            <?php }
            ?>
            <?php
        }
        ?>
        <?php $rights_html = ob_get_contents();
        ob_end_clean();
        echo $rights_html;
    }

    /**
     * Отображение фильтра
     */
    public static function ShowFilter($filterParam, $access_code)
    {
        $Tabs = self::CtreateTabs(['Projects', 'Tasks', 'Resolutions']);
        $Items = self::CtreateTabItems(['Projects', 'Tasks', 'Resolutions']);
        ob_start();
        ?>
        <div class="access_filter">
            <form action="">
                <input type="hidden" name="access_code" value="<?=$access_code?>">
                <?self::ShowSavedSelectorDialog('access_filter', $Tabs, $Items, $filterParam)?>
                <div class="ui-btn-container ui-btn-container-center">
                    <button class="ui-btn ui-btn-primary" name="filterBtn" value="Y">Найти</button>
                    <button class="ui-btn ui-btn-light-border"  name="filterBtn" value="N">Отменить</button>
                </div>
            </form>
        </div>

        <?php $rights_html = ob_get_contents();
        ob_end_clean();
        echo $rights_html;
    }

    /**
     * Диалоговые окна
     **/
    private static function ShowSelectorDialog($key, $tabs, $items, $selected = [[]])
    {
        \Bitrix\Main\UI\Extension::load('ui.entity-selector');
        $inputKey = 'input'.$key;
        $selectedValue = "";
        foreach ($selected as $item)
            if (!empty($item[1]))
                $selectedValue .= $item[1].";";
        ?>
        <input type="hidden" name="<?=$key?>" id="<?=$inputKey?>" value="<?=$selectedValue?>"/>
        <div id="<?=$key?>"></div>
        <script>
            (function() {
                const tagSelector = new BX.UI.EntitySelector.TagSelector({
                    id: '<?=$key?>',
                    multiple: 'Y',
                    dialogOptions: {
                        context: 'MY_MODULE_CONTEXT',
                        tabs:<?=CUtil::PhpToJSObject($tabs, false, false, true)?>,
                        items:<?=CUtil::PhpToJSObject($items, false, false, true)?>,
                        dropdownMode:true,
                        preselectedItems:<?=CUtil::PhpToJSObject($selected, false, false, true)?>,
                        events: {
                            'Item:onSelect': function(event) {
                                var dialog = event.getData().item.getDialog()
                                var SelectedIitems = dialog.getSelectedItems();
                                var AlItems = dialog.getItems();
                                let arSelectedItems = [];
                                SelectedIitems.forEach(function (entity, index) {
                                    if(entity.id === "all"){
                                        AlItems.forEach(function (ent, i) {
                                            if (ent.id !== "all" && entity.entityId === ent.entityId)
                                                arSelectedItems.push(ent.id);
                                        },);
                                    }else
                                        arSelectedItems.push(entity.id);
                                },);
                                arSelectedItems = [...new Set(arSelectedItems)];

                                document.getElementById('<?=$inputKey?>').value = arSelectedItems.join(';');
                            }.bind(this),
                            'Item:onDeselect': function(event) {
                                var dialog = event.getData().item.getDialog()
                                var SelectedIitems = dialog.getSelectedItems();
                                var AlItems = dialog.getItems();
                                let arSelectedItems = [];
                                SelectedIitems.forEach(function (entity, index) {
                                    if(entity.id === "all"){
                                        AlItems.forEach(function (ent, i) {
                                            if (ent.id !== "all" && entity.entityId === ent.entityId)
                                                arSelectedItems.push(ent.id);
                                        },);
                                    }else
                                        arSelectedItems.push(entity.id);
                                },);
                                arSelectedItems = [...new Set(arSelectedItems)];
                                document.getElementById('<?=$inputKey?>').value = arSelectedItems.join(';');
                            }.bind(this),
                        }
                    }
                });
                tagSelector.renderTo(document.getElementById('<?=$key?>'))
            })();

        </script>
    <?}
    private static function ShowSavedSelectorDialog($key, $tabs, $items, $selected = [[]])
    {

        \Bitrix\Main\UI\Extension::load('ui.entity-selector');
        $selectedItems = [];
        $inputKey = 'input'.$key;
        $selectedValue = "";
        foreach ($selected as $Sitem){
            foreach ($items as $item)
                if (trim($item['id']) == trim($Sitem))
                    $selectedItems[] = $item;
        }
        ?>
        <input type="hidden" name="<?=$key?>" id="<?=$inputKey?>" value="<?=$selectedValue?>"/>
        <div id="<?=$key?>"></div>
        <script>
            (function() {
                const tagSelector = new BX.UI.EntitySelector.TagSelector({
                    id: '<?=$key?>',
                    multiple: 'Y',
                    addButtonCaption: 'Выбрать',
                    addButtonCaptionMore: 'Посмотреть',
                    dialogOptions: {
                        context: 'MY_MODULE_CONTEXT',
                        tabs:<?=CUtil::PhpToJSObject($tabs, false, false, true)?>,
                        items:<?=CUtil::PhpToJSObject($items, false, false, true)?>,
                        selectedItems:<?=CUtil::PhpToJSObject($selectedItems, false, false, true)?>,
                        dropdownMode:true,
                        events: {
                            'Item:onSelect': function(event) {
                                var dialog = event.getData().item.getDialog()
                                var SelectedIitems = dialog.getSelectedItems();
                                var AlItems = dialog.getItems();
                                let arSelectedItems = [];
                                SelectedIitems.forEach(function (entity, index) {
                                    if(entity.id === "all"){
                                        AlItems.forEach(function (ent, i) {
                                            if (ent.id !== "all" && entity.entityId === ent.entityId)
                                                arSelectedItems.push(ent.id);
                                        },);
                                    }else
                                        arSelectedItems.push(entity.id);
                                },);
                                arSelectedItems = [...new Set(arSelectedItems)];

                                document.getElementById('<?=$inputKey?>').value = arSelectedItems.join(';');
                            }.bind(this),
                            'Item:onDeselect': function(event) {
                                var dialog = event.getData().item.getDialog()
                                var SelectedIitems = dialog.getSelectedItems();
                                var AlItems = dialog.getItems();
                                let arSelectedItems = [];
                                SelectedIitems.forEach(function (entity, index) {
                                    if(entity.id === "all"){
                                        AlItems.forEach(function (ent, i) {
                                            if (ent.id !== "all" && entity.entityId === ent.entityId)
                                                arSelectedItems.push(ent.id);
                                        },);
                                    }else
                                        arSelectedItems.push(entity.id);
                                },);
                                arSelectedItems = [...new Set(arSelectedItems)];
                                document.getElementById('<?=$inputKey?>').value = arSelectedItems.join(';');
                            }.bind(this),
                        }
                    }
                });
                tagSelector.renderTo(document.getElementById('<?=$key?>'))
            })();

        </script>
    <?}

    /**
     * Сохранение прав
    **/
    public static function AccessMatrixSave($request)
    {
        self::RightsSave(self::OPTION_DASHBOARD_KP_FIELD, json_encode(self::Post2Array($request->getPost(self::OPTION_DASHBOARD_KP_FIELD))));
        self::RightsSave(self::OPTION_DASHBOARD_GOSTEH_FIELD, json_encode(self::Post2Array($request->getPost(self::OPTION_DASHBOARD_GOSTEH_FIELD))));
        self::RightsSave(self::OPTION_DASHBOARD_DK, json_encode(self::Post2Array($request->getPost(self::OPTION_DASHBOARD_DK))));
        self::RightsSave(self::OPTION_DASHBOARD_PROJECT, json_encode(self::Post2Array($request->getPost(self::OPTION_DASHBOARD_PROJECT))));
        self::RightsSave(self::OPTION_DASHBOARD_PROJECTS, json_encode(self::Post2Array($request->getPost(self::OPTION_DASHBOARD_PROJECTS))));
        self::RightsSave(self::OPTION_DASHBOARD_CUSTOM_FIELD, json_encode(self::Post2Array($request->getPost(self::OPTION_DASHBOARD_CUSTOM_FIELD))));
        //self::RightsSave(self::OPTION_ORGSTRUCTURE_FIELD, json_encode(self::Post2Array($request->getPost(self::OPTION_ORGSTRUCTURE_FIELD))));

        //Права на учеть трудозатрат
        self::RightsSave(self::OPTION_TIMEMAN_FIELD.'_USERS', json_encode(array('USERS' => $request->getPost(self::OPTION_TIMEMAN_FIELD)['USERS'])));
        self::RightsSave(self::OPTION_TIMEMAN_FIELD, json_encode(self::Post2Array($request->getPost(self::OPTION_TIMEMAN_FIELD)['RIGHTS'])));

        //Права на проекты
        $projectRights = self::Post2Array($request->getPost(self::OPTION_PROJECTS_FIELD), 'PROJECT');
        foreach ($projectRights['PROJECTS'] as $id => $value){
            $access_key = self::OPTION_PROJECTS_FIELD.'_'.$id;
            $saveResult = json_decode(self::GetRightByCode($access_key), true);
            foreach ($value as $key=>$item){
                $saveResult[$key] = $item;
            }
            $saveResult = json_encode($saveResult);
            self::RightsSave($access_key, $saveResult);
        }
        if (!empty($projectRights['BASE'])) {
            $access_key = self::OPTION_PROJECTS_FIELD;
            $saveResult = json_decode(self::GetRightByCode($access_key), true);
            foreach ($projectRights['BASE'] as $key=>$item){
                $saveResult[$key] = $item;
            }
            $saveResult = json_encode($saveResult);
            self::RightsSave($access_key, $saveResult);
        }

        //Права на задачи
        $taskRights = self::Post2Array($request->getPost(self::OPTION_TASKS_FIELD), 'TASK');
        foreach ($taskRights['PROJECTS'] as $id => $value){
            $access_key = self::OPTION_TASKS_FIELD.'_P_'.$id;
            $saveResult = json_decode(self::GetRightByCode($access_key), true);
            foreach ($value as $key=>$item){
                $saveResult[$key] = $item;
            }
            $saveResult = json_encode($saveResult);
            self::RightsSave($access_key, $saveResult);
        }
        foreach ($taskRights['TASKS'] as $id => $value){
            $access_key = self::OPTION_TASKS_FIELD.'_T_'.$id;
            $saveResult = json_decode(self::GetRightByCode($access_key), true);
            foreach ($value as $key=>$item){
                $saveResult[$key] = $item;
            }
            $saveResult = json_encode($saveResult);
            self::RightsSave($access_key, $saveResult);
        }

        //Права на поручения
        $taskRights = self::Post2Array($request->getPost(self::OPTION_RESOLUTION_FIELD), 'RESOLUTIONS');
        foreach ($taskRights['PROJECTS'] as $id => $value){
            $access_key = self::OPTION_RESOLUTION_FIELD.'_P_'.$id;
            $saveResult = json_decode(self::GetRightByCode($access_key), true);
            foreach ($value as $key=>$item){
                $saveResult[$key] = $item;
            }
            $saveResult = json_encode($saveResult);
            self::RightsSave($access_key, $saveResult);
        }
        foreach ($taskRights['TASKS'] as $id => $value){
            $access_key = self::OPTION_RESOLUTION_FIELD.'_T_'.$id;
            $saveResult = json_decode(self::GetRightByCode($access_key), true);
            foreach ($value as $key=>$item){
                $saveResult[$key] = $item;
            }
            $saveResult = json_encode($saveResult);
            self::RightsSave($access_key, $saveResult);
        }

    }

    private static function GetProjects($strategic = false)
    {
        $arGroups = [];
        $filter = [];
        if ($strategic)
            $filter['UF_STRATEGY'] = '1';
        else
            $filter['UF_STRATEGY'] = '0';

        $resGroup = \Bitrix\Socialnetwork\WorkgroupTable::getList([
            'filter' => $filter,
            'select' => ['ID','NAME'],
            'order' => ["ID" => "DESC"],
        ]);
        while ($group = $resGroup->fetch())
        {
            $group["NAME"] = "[".$group['ID']."] " . $group['NAME'];
            $arGroups[$group['ID']] = $group;
        }

        return $arGroups;
    }

    private static function GetAllProjects()
    {
        $arGroups = [];
        $filter = [];

        $resGroup = \Bitrix\Socialnetwork\WorkgroupTable::getList([
            'filter' => $filter,
            'select' => ['ID','NAME'],
            'order' => ["ID" => "DESC"],
        ]);
        while ($group = $resGroup->fetch())
        {
            $group["NAME"] = "[".$group['ID']."] " . $group['NAME'];
            $arGroups[] = $group;
        }

        return $arGroups;
    }

    private static function CtreateTabs($tabsId){
        $tabs = [];
        foreach ($tabsId as $tabId){
            switch ($tabId){
                case 'projectsGroup':
                    $tabs[] = ['id' => $tabId, 'title' => 'Группы и роли', 'itemOrder' => ['title' => 'asc'] ];
                    break;
                case 'tasksGroup':
                    $tabs[] = ['id' => $tabId, 'title' => 'Группы и роли', 'itemOrder' => ['title' => 'asc'] ];
                    break;
                case 'BaseProjects':
                    $tabs[] = ['id' => $tabId, 'title' => 'Базовые'];
                    break;
                case 'Projects':
                    $tabs[] = ['id' => $tabId, 'title' => 'Проекты'];
                    break;
                case 'StrategicProjects':
                    $tabs[] = ['id' => $tabId, 'title' => 'Стратегические'];
                case 'BaseTasks':
                    $tabs[] = ['id' => $tabId, 'title' => 'Базовые'];
                    break;
                case 'StrategicTasks':
                    $tabs[] = ['id' => $tabId, 'title' => 'Стратегические'];
                    break;
                case 'Tasks':
                    $tabs[] = ['id' => $tabId, 'title' => 'Задачи'];
                    break;
                case 'Resolutions':
                    $tabs[] = ['id' => $tabId, 'title' => 'Поручения'];
                    break;
                case 'users':
                    $tabs[] = ['id' => $tabId, 'title' => 'Пользователи', 'itemOrder' => ['title' => 'asc'] ];
                case 'user':
                    $tabs[] = ['id' => $tabId, 'title' => 'Пользователи', 'itemOrder' => ['title' => 'asc'] ];
            }
        }
        return $tabs;
    }

    private static function CtreateTabItems($tabsId)
    {
        $items = [];
        foreach ($tabsId as $tabId){
            switch ($tabId){
                case 'projectsGroup':
                    $arUserGroups = self::GetGroups();
                    $items[] = ['id' => 'all', 'entityId'=> 'group', 'tabs' => $tabId, 'title' => ' Все группы и роли', 'textColor' => 'orange'];
                    $items[] = ['id' => 'R', 'entityId'=> 'group', 'tabs' => $tabId, 'title' => 'Руководитель проекта (роль в проекте)'];
                    $items[] = ['id' => 'M', 'entityId'=> 'group', 'tabs' => $tabId, 'title' => 'Помощник руководителя проекта (роль в проекте)'];
                    $items[] = ['id' => 'P', 'entityId'=> 'group', 'tabs' => $tabId, 'title' => 'Участники проекта (роль в проекте)'];
                    foreach ($arUserGroups as $group):
                        $items[] = ['id' => 'G_'.$group['ID'], 'entityId'=> 'group', 'tabs' => $tabId, 'title' => $group['NAME'], 'url' => '/'];
                    endforeach;
                    break;
                case 'tasksGroup':
                    $arUserGroups = self::GetGroups();
                    $items[] = ['id' => 'all', 'entityId'=> 'group', 'tabs' => $tabId, 'title' => ' Все группы и роли', 'textColor' => 'orange'];
                    $items[] = ['id' => 'R', 'entityId'=> 'group', 'tabs' => $tabId, 'title' => 'Руководитель проекта (роль в проекте)'];
                    $items[] = ['id' => 'M', 'entityId'=> 'group', 'tabs' => $tabId, 'title' => 'Помощник руководителя проекта (роль в проекте)'];
                    $items[] = ['id' => 'P', 'entityId'=> 'group', 'tabs' => $tabId, 'title' => 'Участники проекта (роль в проекте)'];
                    $items[] = ['id' => 'TR', 'entityId'=> 'group', 'tabs' => $tabId, 'title' => 'Ответственный (роль в задаче)'];
                    $items[] = ['id' => 'TA', 'entityId'=> 'group', 'tabs' => $tabId, 'title' => 'Постановщик (роль в задаче)'];
                    $items[] = ['id' => 'TS', 'entityId'=> 'group', 'tabs' => $tabId, 'title' => 'Соисполнители (роль в задаче)'];
                    $items[] = ['id' => 'TN', 'entityId'=> 'group', 'tabs' => $tabId, 'title' => 'Наблюдатели (роль в задаче)'];
                    foreach ($arUserGroups as $group):
                        $items[] = ['id' => 'G_'.$group['ID'], 'entityId'=> 'group', 'tabs' => $tabId, 'title' => $group['NAME'], 'url' => '/'];
                    endforeach;
                    break;
                case 'users':
                    $arUsers = self::GetUsers();
                    foreach ($arUsers as $arUser):
                        $items[] = ['id' => 'U_'.$arUser['ID'], 'entityId'=> 'user1', 'tabs' => $tabId, 'title' => $arUser['NAME']];
                    endforeach;
                    break;
                case 'user':
                    $arUsers = self::GetUsers();
                    foreach ($arUsers as $arUser):
                        $items[] = ['id' => $arUser['ID'], 'entityId'=> 'user', 'tabs' => $tabId, 'title' => $arUser['NAME']];
                    endforeach;
                    break;
                case 'BaseProjects':
                    $arItems = self::GetProjects();
                    foreach ($arItems as $arItem):
                        $items[] = ['id' => 'P_'.$arItem['ID'], 'entityId'=> 'BProjects', 'tabs' => $tabId, 'title' => $arItem['NAME']];
                    endforeach;
                    break;
                case 'Projects':
                    $arItems = self::GetAllProjects();
                    foreach ($arItems as $arItem):
                        $items[] = ['id' => 'P_'.$arItem['ID'], 'entityId'=> 'BProjects', 'tabs' => $tabId, 'title' => $arItem['NAME']];
                    endforeach;
                    break;
                case 'StrategicProjects':
                    $arItems = self::GetProjects(true);
                    foreach ($arItems as $arItem):
                        $items[] = ['id' => 'P_'.$arItem['ID'], 'entityId'=> 'SProjects', 'tabs' => $tabId, 'title' => $arItem['NAME']];
                    endforeach;
                    break;
                case 'BaseTasks':
                    $arItems = self::GetTasks();
                    foreach ($arItems as $arItem):
                        $items[] = ['id' => 'T_'.$arItem['ID'], 'entityId'=> 'BTrojects', 'tabs' => $tabId, 'title' => $arItem['NAME']];
                    endforeach;
                    break;
                case 'Tasks':
                    $arItems = self::GetAllTasks();
                    foreach ($arItems as $arItem):
                        $items[] = ['id' => 'T_'.$arItem['ID'], 'entityId'=> 'Tasks', 'tabs' => $tabId, 'title' => $arItem['NAME']];
                    endforeach;
                    break;
                case 'StrategicTasks':
                    $arItems = self::GetTasks(true);
                    foreach ($arItems as $arItem):
                        $items[] = ['id' => 'T_'.$arItem['ID'], 'entityId'=> 'STrojects', 'tabs' => $tabId, 'title' => $arItem['NAME']];
                    endforeach;
                    break;
                case 'Resolutions':
                    $arItems = self::GetResolutions();
                    foreach ($arItems as $arItem):
                        $items[] = ['id' => 'R_'.$arItem['ID'], 'entityId'=> 'STrojects', 'tabs' => $tabId, 'title' => $arItem['NAME']];
                    endforeach;
                    break;
            }
        }
        return $items;
    }

    private static function GetUsers()
    {
        $users = [];
        $resOb = UserTable::getList([
            'filter' => ["ACTIVE" => "Y"],
            'select' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN'],
            'order' => ["ID" => "ASC"],
        ]);
        while ($res = $resOb->fetch())
        {
            $res['NAME'] = $res['LAST_NAME'] . ' '. $res['NAME']. ' ' . $res['SECOND_NAME']. '('.$res['LOGIN']. ')';
            $users[] = $res;
        }

        return $users;
    }

    private static function GetGroups()
    {
        $arResult = [];
        $resOb = GroupTable::getList([
            'filter' => ["ACTIVE" => "Y"],
            'select' => ['ID', 'NAME'],
            'order' => ["ID" => "ASC"],
        ]);
        while ($res = $resOb->fetch())
        {
            $arResult[$res["ID"]] = $res;
        }
        return $arResult;
    }

    private static function GetTasks($strategic = false){
        $projectsId = array_keys(self::GetProjects(true));
        $arResult = [];
        $filter = ['!UF_RESOLUTION' => 'Y'];
        if ($strategic)
            $filter = ['GROUP_ID' => $projectsId];
        else
            $filter = ['!GROUP_ID' => $projectsId];



        $resOb = TaskTable::getList([
            'filter' => $filter,
            'select' => ['ID', 'TITLE'],
            'order' => ["ID" => "DESC"],
        ]);
        while ($res = $resOb->fetch())
        {
            $res["NAME"] = "[".$res['ID']."] " . $res['TITLE'];
            $arResult[$res["ID"]] = $res;
        }
        return $arResult;
    }

    private static function GetAllTasks(){
        $filter = ['UF_RESOLUTION' => ''];

        $resOb = TaskTable::getList([
            'filter' => $filter,
            'select' => ['ID', 'TITLE'],
            'order' => ["ID" => "DESC"],
        ]);
        while ($res = $resOb->fetch())
        {
            $res["NAME"] = "[".$res['ID']."] " . $res['TITLE'];
            $arResult[$res["ID"]] = $res;
        }
        return $arResult;
    }

    private static function GetResolutions(){
        $filter = ['=UF_RESOLUTION' => 'Y'];

        $resOb = TaskTable::getList([
            'filter' => $filter,
            'select' => ['ID', 'TITLE'],
            'order' => ["ID" => "DESC"],
        ]);
        while ($res = $resOb->fetch())
        {
            $res["NAME"] = "[".$res['ID']."] " . $res['TITLE'];
            $arResult[$res["ID"]] = $res;
        }
        return $arResult;
    }
}