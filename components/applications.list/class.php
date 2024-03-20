<?php

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Highloadblock as HL;
use Extyl\ORM\WorkappsFavoritesTable;


class ApplicationList extends CBitrixComponent implements Controllerable
{

    public function executeComponent()
    {
        $this->GetResult();
        $this->includeComponentTemplate();
    }

    private function GetResult()
    {
        $property_enums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$this->getIblockIdByCode('work-apps'), "CODE"=>"APP_SECTION"));
        while($enum_fields = $property_enums->GetNext())
        {
            $this->arResult['APP_CATEGORIES'][$enum_fields['ID']] = ['ID' => $enum_fields['ID'], 'NAME' => $enum_fields['VALUE']];
        }
        $favorites = $this->getFavorites();

        $arExamResult = [];
        Loader::includeModule("iblock");

        //Результаты экзаменов
        $obj = \Bitrix\Iblock\Elements\ElementWorkAppsTable::getList([
            'cache' => [
                'ttl' => 3600
            ],
            'select' => [
                'ID',
                'NAME',
                'APP_ICON_VALUE'  => 'APP_ICON.VALUE',
                'APP_URL_VALUE' => 'APP_URL.VALUE',
                'APP_SECTION_VALUE' => 'APP_SECTION.VALUE',
                'APP_DESCRIPTION_VALUE' => 'APP_DESCRIPTION.VALUE',
                'APP_TYPE_VALUE' => 'APP_TYPE.VALUE',
                'APP_POPUP_VALUE' => 'APP_POPUP.VALUE',
                'PREVIEW_TEXT',
            ],
            'order' => ['sort' => 'ASC'],
        ]);
        $apps = [];
        $allApps = [];
        while ($row = $obj->fetch()) {
            $row['FAV'] = in_array($row['ID'], $favorites);
            $row['APP_ICON_VALUE'] = CFile::GetPath((int)$row["APP_ICON_VALUE"]);
            $apps[$row['APP_SECTION_VALUE']][] = $row;
            $allApps[] = $row;
        }
        $this->arResult['APPLICATIONS'] = $apps;
        $this->arResult['ALL_APPLICATIONS'] = $allApps;
    }

    public  function getIblockIdByCode($iblock_code)
    {
        \Bitrix\Main\Loader::includeModule('iblock');
        $iblock_obj = \Bitrix\Iblock\IblockTable::getList(
            [
                'filter' => [
                    '=CODE' => $iblock_code
                ],
                'select' => [
                    'ID'
                ]
            ]
        );
        return $iblock_obj->fetchObject()->getId();
    }

    public function executingAction($linkId = ''): array
    {
        $linkId = (int) $linkId;
        $resId = $this->executionRecord($linkId);
        return ['status' => 'success', 'resId' => $resId];
    }
    public function favoriteAction($appId = ''): array
    {
        $appId = (int) $appId;
        $res = $this->favoriteRecord($appId);
        return ['status' => 'success','id'=> $appId, 'result' => $res];
    }
    public function configureActions()
    {
        return [
            'executing' => [
                'prefilters' => [
                ]
            ]
        ];
    }
    public static function executionRecord(int $appId): int
    {
        global $USER;
        $param = [
            'order' => ['UF_EXECUTE_TIME' => 'desc'],
            'limit' => 1,
            'filter' => [
                'UF_USER' => $USER->GetID(),
                'UF_APP' => $appId
            ],
            'select' => ['ID']
        ];
        Loader::includeModule("highloadblock");
        Loader::includeModule('intranet');
        $hlblock_Release = HL\HighloadBlockTable::getList(['filter' => ['NAME' => 'WorkappsLastExecuted']])->fetch();
        if($hlblock_Release){
            $Release = HL\HighloadBlockTable::compileEntity($hlblock_Release);
            $Release_data_class = $Release->getDataClass();
            $Departments = \CIntranetUtils::GetUserDepartments($USER->GetID());
            if ($arRes = $Release_data_class::getList($param)->fetch()) {
                $resId=$arRes['ID'];
                $Release_data_class::update($arRes['ID'], ['UF_APP' => $appId, 'UF_EXECUTE_TIME' => new DateTime(), 'UF_USER' => $USER->GetID(), 'UF_DEPARTMENT' => $Departments,
                    'UF_LOGIN_FIO' =>'('.$USER->GetLogin().')'.$USER->GetFullName().' '. $USER->GetSecondName()]);
            } else {
                $resId = $Release_data_class::add(['UF_APP' => $appId, 'UF_EXECUTE_TIME' => new DateTime(), 'UF_USER' => $USER->GetID(), 'UF_DEPARTMENT' => $Departments,
                    'UF_LOGIN_FIO' =>'('.$USER->GetLogin().')'.$USER->GetFullName().' '. $USER->GetSecondName()])->getId();
            }
        }

        return (int)$resId;
    }

    public function favoriteRecord(int $appId): string
    {
        $result = 'error';
        $userId = CurrentUser::get()->getId();
        $param = [
            'limit' => 1,
            'filter' => [
                'UF_USER' => $userId,
                'UF_APP' => $appId
            ],
            'select' => ['ID']
        ];

        if ($arRes = WorkappsFavoritesTable::getList($param)->fetch()) {
            $result = 'delete';
            WorkappsFavoritesTable::delete($arRes['ID']);
        } else {
            if(WorkappsFavoritesTable::add(['UF_APP' => $appId, 'UF_USER' => $userId])->getId()){
                $result = 'add';
            }

        }
        return $result;
    }

    private function getFavorites(): array
    {
        $result = [];
        $userId = CurrentUser::get()->getId();
        $param = [
            'filter' => [
                'UF_USER' => $userId,
            ],
            'select' => ['UF_APP']
        ];
        if ($arRes = WorkappsFavoritesTable::getList($param)->fetchAll()) {
            $result = array_column($arRes, 'UF_APP');
        }
        return $result;
    }
}