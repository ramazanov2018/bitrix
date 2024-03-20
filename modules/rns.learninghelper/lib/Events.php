<?php
namespace LearningHelper;
use \Bitrix\Main\Type\DateTime;

class Events
{
    //
    public static function CreateAdminMenu(&$aGlobalMenu, &$aModuleMenu)
    {
        $key = self::searchForId('menu_learning',$aModuleMenu);
        unset($aModuleMenu[$key]);
        //self::RecursiveUrl($aModuleMenu[$key]['items'], 'url', 'learn_unilesson_admin','learn_helper_unilesson_admin');
        //self::LearnGroupUrl($aModuleMenu[$key]['items']);
        //self::RecursiveUrl($aModuleMenu[$key]['items'], 'url', 'learn_group_admin','learn_helper_group_admin');

        //self::AddMenuItemWaitList($aModuleMenu[$key]['items']);
    }

    public static function OnLearningAdd(&$arFields)
    {
        $objDateTime = new DateTime();
        $arFields['UF_DATE_CREATE'] = $objDateTime->format('d.m.Y H:i:s');
        return true;
    }

    protected static function searchForId($id, $array)
    {
        foreach ($array as $key => $val) {
            if ($val['items_id'] === $id) {
                return $key;
            }
        }
        return null;
    }

    protected static function RecursiveUrl(&$ar, $key, $search, $replace)
    {
        foreach($ar as &$item){
            $item[$key] = str_replace($search, $replace, $item['url']);
            if (is_array($item['items']) && count($item['items']) > 0)
                self::RecursiveUrl($item['items'], $key, $search, $replace);
        }
    }

    protected static function LearnGroupUrl(&$items)
    {
        foreach($items as &$item){
            if ($item['items_id'] == 'menu_learning_groups')
                $item['url'] = 'learn_helper_group_admin.php?lang=ru&del_filter=Y';
        }
    }

    protected static function AddMenuItemWaitList(&$item)
    {
        $menu1 = array_pop($item);
        $menu2 = array_pop($item);
        $item[] = [
            'text' => 'Лист ожидания',
            'url' => 'learn_helper_group_admin.php?del_filter=Y&PAGEN_1=1&SIZEN_1=20&amp%3Bfilter=Y&amp%3Bset_filter=Y&lang=ru&set_filter=Y&adm_filter_applied=0&filter_course_lesson_id=1000000000',
            'title' => 'Управление учебными группами',
            'items_id' => 'menu_learning_groups',
            'icon' => 'learning_icon_groups',
        ];

        $item[] = $menu1;
        $item[] = $menu2;
    }
}