<?php

namespace Rns\Analytic;

use Bitrix\Highloadblock as HL,
    Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;


class Settings
{
    const HL_DYNAMIC_URL_NAME = 'RnsAnalyticsDynamicUrl';
    public function HighloadBlocksRightSave($rights, $groups)
    {
        Loader::includeModule('highloadblock');

        //Список Highload-блоков
        $arHBlocks = [
            'rns_analytics_log',
            'rns_analytics_dynamic_url',
        ];

        $filter = [
            'select' => ['ID'],
            'filter' => ['TABLE_NAME' => $arHBlocks]
        ];

        $hlblocks = HL\HighloadBlockTable::getList($filter);
        $hlblocksIDs = [];
        while ($hlblock = $hlblocks->fetch()){
            $hlblocksIDs[] = $hlblock['ID'];
        }

        $res = HL\HighloadBlockRightsTable::getList([
            'filter' => [
                'HL_ID' => $hlblocksIDs
            ]
        ]);
        $currentRights = [];
        while ($row = $res->fetch())
        {
            $currentRights[$row['HL_ID']][$row['ID']] = [
                'ACCESS_CODE' => $row['ACCESS_CODE'],
                'TASK_ID' => $row['TASK_ID']
            ];
        }

        $res = \CTask::GetList(['LETTER' => 'ASC'], ['MODULE_ID' => 'highloadblock']);
        $tasks = [];
        while ($row = $res->getNext())
        {
            $tasks[$row['LETTER']] = $row['ID'];
        }

        $HBrights = [];
        foreach ($groups as $k => $g){
            if (!empty($g))
                $HBrights["G".$g] = $tasks[$rights[$k]];
        }

        foreach ($hlblocksIDs as $hlblocksID){
            $currentHBRights = $currentRights[$hlblocksID];

            //delete
            foreach (array_keys($currentHBRights) as $rid)
            {
                HL\HighloadBlockRightsTable::delete($rid);
            }

            //add
            foreach($HBrights as $k => $v){
                HL\HighloadBlockRightsTable::add([
                    'HL_ID' => $hlblocksID,
                    'ACCESS_CODE' => $k,
                    'TASK_ID' => $v
                ]);
            }

        }
    }

    public function ChangeSettingsDynamicUrl($dynamicUrl, $dynamicUrlParam, $dynamicUrlActive)
    {
        $updateElements = [];
        $addElements = [];
        if(is_array($dynamicUrl)){
            foreach ($dynamicUrl as $key => $item){
                if(strpos($key, "id_") !== false){
                    $key = (int)explode('_', $key)[1];
                    $updateElements[$key]["UF_RNS_DYNAMIC_URL"] = trim($item);
                }else{
                    $key = (int)$key;
                    $addElements[$key]["UF_RNS_DYNAMIC_URL"] = trim($item);
                }
            }
        }
        if(is_array($dynamicUrlParam)){
            foreach ($dynamicUrlParam as $key => $item){
                if(strpos($key, "id_") !== false){
                    $key = (int)explode('_', $key)[1];
                    $updateElements[$key]["UF_RNS_DYNAMIC_URL_PARAM"] = trim($item);
                }else{
                    $key = (int)$key;
                    $addElements[$key]["UF_RNS_DYNAMIC_URL_PARAM"] = trim($item);
                }
            }
        }
        if(is_array($dynamicUrlActive)){
            foreach ($dynamicUrlActive as $key => $item){
                if(strpos($key, "id_") !== false){
                    $key = (int)explode('_', $key)[1];
                    $updateElements[$key]["UF_RNS_DYNAMIC_URL_ACTIVE"] = ($item == "Y") ? 1 : 0;
                }else{
                    $key = (int)$key;
                    $addElements[$key]["UF_RNS_DYNAMIC_URL_ACTIVE"] = ($item == "Y") ? 1 : 0;
                }
            }
        }

        $this->DynamicUrlUpdate($updateElements);
        $this->DynamicUrlAdd($addElements);

    }

    private function DynamicUrlUpdate($updateElements)
    {
        if (is_array($updateElements) && count($updateElements) > 0){
            $hlblock = HighloadBlockTable::getList([
                    'filter' => ['NAME' => self::HL_DYNAMIC_URL_NAME]
                ]
            )->fetch();
            $entity = HighloadBlockTable::compileEntity($hlblock);
            $dataClass =  $entity->getDataClass();

            foreach ($updateElements as $id => $values){
                $values['UF_RNS_DYNAMIC_URL'] = substr($values['UF_RNS_DYNAMIC_URL'], 0, 1) == "/" ? $values['UF_RNS_DYNAMIC_URL'] : '/'.$values['UF_RNS_DYNAMIC_URL'];
                $values['UF_RNS_DYNAMIC_URL_ACTIVE'] = $values['UF_RNS_DYNAMIC_URL_ACTIVE'] > 0 ? 1 : 0;
                $id = (int) $id;
                $result = $dataClass::update($id, $values);
            }
        }
    }

    private function DynamicUrlAdd($addElements)
    {
        if (is_array($addElements) && count($addElements) > 0){
            $hlblock = HighloadBlockTable::getList([
                    'filter' => ['NAME' => self::HL_DYNAMIC_URL_NAME]
                ]
            )->fetch();
            $entity = HighloadBlockTable::compileEntity($hlblock);
            $dataClass =  $entity->getDataClass();

            foreach ($addElements as $values){
                $values['UF_RNS_DYNAMIC_URL'] = substr($values['UF_RNS_DYNAMIC_URL'], 0, 1) == "/" ? $values['UF_RNS_DYNAMIC_URL'] : '/'.$values['UF_RNS_DYNAMIC_URL'];
                $values['UF_RNS_DYNAMIC_URL_ACTIVE'] = $values['UF_RNS_DYNAMIC_URL_ACTIVE'] > 0 ? 1 : 0;
                $result = $dataClass::add($values);
            }
        }
    }


    public static function GetDynamicUrlValues($showInactive = false)
    {
        $items = [];
        if (!Loader::includeModule('highloadblock')) {
            return $items;
        }

        $arFilter = [];
        if (!$showInactive)
            $arFilter['UF_RNS_DYNAMIC_URL_ACTIVE'] = 1;

        $rsData = HighloadBlockTable::getList(['filter' => ['NAME' => 'RnsAnalyticsDynamicUrl']]);
        $hldata = $rsData->fetch();
        if ($hldata) {
            $hlDynamicUrl = HighloadBlockTable::compileEntity($hldata);
            $strDynamicUrlDataClass = $hlDynamicUrl->getDataClass();

            $res = $strDynamicUrlDataClass::getList(['filter' => $arFilter, 'select' => ['*'],]);
            while ($ar_res = $res->fetch()) {
                $item = [];
                $item['id'] = $ar_res['ID'];
                $item['active'] = (int)$ar_res['UF_RNS_DYNAMIC_URL_ACTIVE'] ? "Y" : "N";
                $item['url'] = $ar_res['UF_RNS_DYNAMIC_URL'];
                $item['parameter'] = $ar_res['UF_RNS_DYNAMIC_URL_PARAM'];
                $items[] = $item;
            }
            unset($ar_res, $res);
        }

        return $items;
    }
}