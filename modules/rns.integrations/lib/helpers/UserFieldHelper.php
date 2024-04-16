<?php

namespace RNS\Integrations\Helpers;

use Bitrix\Main\UserFieldTable;
use CUserFieldEnum;

class UserFieldHelper
{
    /**
     * @param string $entityId
     * @param string $ufName
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getUserFieldEnumValues(string $entityId, string $ufName)
    {
        $uf = UserFieldTable::getList([
          'select' => ['ID'],
          'filter' => ['=ENTITY_ID' => $entityId, 'FIELD_NAME' => $ufName],
          'limit' => 1
        ])->fetch();

        $enum = new CUserFieldEnum();
        $res = $enum->GetList([], ['USER_FIELD_ID' => $uf['ID']]);
        $enumValues = [];
        while ($row = $res->GetNext()) {
            $enumValues[$row['XML_ID']] = (int)$row['ID'];
        }
        return $enumValues;
    }
}
