<?php

namespace RNS\Integrations\Controller;

use Bitrix\Main\Loader;
use CSocNetGroup;

class Project extends ControllerBase
{
    /**
     * Возвращает список проектов.
     * @param array $order
     * @param array $filter
     * @param bool $groupBy
     * @param bool $navStartParams
     * @param array $fields
     * @return array
     * @throws \Bitrix\Main\LoaderException
     */
    public function getAction(
      $order = [],
      $filter = [],
      $groupBy = false,
      $navStartParams = false,
      $fields = []
    ) {
        Loader::includeModule('socialnetwork');

        $result = [];
        $res = CSocNetGroup::GetList($order, $filter, $groupBy, $navStartParams, $fields);
        while ($row = $res->GetNext()) {
            $item = $this->removeTildeItems($row);
            $item = $this->convertKeysToCamelCase($item);
            $result[] = $item;
        }

        return $result;
    }
}
