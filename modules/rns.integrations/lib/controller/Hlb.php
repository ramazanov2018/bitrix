<?php

namespace RNS\Integrations\Controller;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\PageNavigation;

class Hlb extends Controller
{
    /**
     * Возвращает список записей указанного HL-блока.
     * @param PageNavigation $pageNavigation
     * @param array $filter
     * @param array $select
     * @param array $group
     * @param array $order
     * @param array $params
     * @return array|bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\LoaderException
     */
    public function getAction(
      PageNavigation $pageNavigation,
      array $filter = [],
      array $select = ['*'],
      array $group = [],
      array $order = [],
      array $params = []
    ) {
        $result = [];
        if (!Loader::includeModule('highloadblock')) {
            return $result;
        }

        $filter = array_change_key_case($filter, CASE_UPPER);
        if (empty($filter['NAME'])) {
            return $result;
        }

        $res = HighloadBlockTable::getList(['filter' => ['NAME' => $filter['NAME']]]);
        unset($filter['NAME']);
        if ($hlb = $res->fetch()) {
            $hlentity = HighloadBlockTable::compileEntity($hlb);
            $strEntityDataClass = $hlentity->getDataClass();
            $res = $strEntityDataClass::getList([
              'filter' => $filter,
              'select' => $select,
              'order' => $order,
              'group' => $group,
              'limit' => $pageNavigation->getLimit(),
              'offset' => $pageNavigation->getOffset()
            ]);
            $data = $res->fetchAll();

            foreach ($data as $datum) {
                $datum = $this->convertKeysToCamelCase($datum);
                $result[] = $datum;
            }
        }
        return $result;
    }
}