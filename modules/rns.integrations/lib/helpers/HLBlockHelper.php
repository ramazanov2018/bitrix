<?php

namespace RNS\Integrations\Helpers;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Iblock\ORM\Query;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserFieldTable;
use CUserFieldEnum;

class HLBlockHelper
{
    /**
     * @param $id
     * @param array $arSelect
     * @param array $arOrder
     * @param string $key
     * @param array $arWhere
     * @param bool $assoc
     * @return array
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getList(
      $id,
      array $arSelect = [],
      array $arOrder = [],
      string $key = 'ID',
      array $arWhere = [],
      bool $assoc = true
    ): array
    {
        if (!Loader::includeModule('highloadblock')) {
            throw new LoaderException('Module Highload Blocks not installed.');
        }

        if (is_numeric($id)) {
            $arFilter = ['ID' => $id];
        } else {
            $arFilter = ['TABLE_NAME' => $id];
        }

        $arHl = HighloadBlockTable::getList(['filter' => $arFilter])
            ->fetch();

        $hlQuery = new Query(HighloadBlockTable::compileEntity($arHl));

        if ($arSelect) {
            $hlQuery->setSelect($arSelect);
        } else {
            $hlQuery->addSelect("*");
        }

        if ($arWhere) {
            foreach ($arWhere as $field => $value) {
                $hlQuery->addFilter($field, $value);
            }
        }

        if ($arOrder) {
            if ($arOrder[1]) {
                $hlQuery->addOrder($arOrder[0], $arOrder[1]);

            } else {
                $hlQuery->addOrder($arOrder[0]);
            }
        }

        $rsData = $hlQuery->exec();

        $arItems = [];
        if ($assoc) {
            while ($arItem = $rsData->Fetch()) {
                $arItems[$arItem[$key]] = $arItem;
            }
        } else {
            while ($arItem = $rsData->Fetch()) {
                $arItems[] = $arItem;
            }
        }

        return $arItems;
    }

    /**
     * @param $hlbId
     * @param array $data
     * @return \Bitrix\Main\ORM\Data\Result
     * @throws LoaderException
     * @throws \Exception
     */
    public static function save($hlbId, array $data)
    {
        if (!Loader::includeModule('highloadblock')) {
            throw new LoaderException('Module Highload Blocks not installed.');
        }

        $dataClass = self::getEntityDataClass($hlbId);
        return empty($data['ID']) ? $dataClass::add($data) : $dataClass::update($data['ID'], $data);
    }

    /**
     * @param $hlbId
     * @param string $ufName
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getUserFieldEnumValues($hlbId, string $ufName)
    {
        return UserFieldHelper::getUserFieldEnumValues('HLBLOCK_' . $hlbId, $ufName);
    }

    /**
     * @param $id
     * @return \Bitrix\Main\ORM\Data\DataManager|bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private static function getEntityDataClass($id)
    {
        if (empty($id)) {
            return false;
        }

        if ((int)$id) {
            $arFilter = ['ID' => $id];
        } else {
            $arFilter = ['NAME' => $id];
        }

        $hlblock = HighloadBlockTable::getList([
          'filter' => $arFilter
          ]
        )->fetch();
        $entity = HighloadBlockTable::compileEntity($hlblock);
        return $entity->getDataClass();
    }
}
