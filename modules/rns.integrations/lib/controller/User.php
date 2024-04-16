<?php /** @noinspection ALL */

namespace RNS\Integrations\Controller;

use Bitrix\Iblock\SectionTable;
use Bitrix\Main\UserTable;
use CUser;

class User extends ControllerBase
{
    /**
     * Возвращает данные текущего пользователя.
     * @return array|bool|mixed|string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function currentAction()
    {
        global $USER;
        $res = UserTable::getById($USER->getID());
        if ($row = $res->fetch()) {
            unset($row['PASSWORD']);
            return $this->convertKeysToCamelCase($row);
        }
        return false;
    }

    /**
     * Поиск пользователя по различным полям.
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function searchAction()
    {
        $request = $this->getRequest();

        $values = $request->getValues();

        $filterFields = ['LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'WORK_POSITION', 'UF_DEPARTMENT_NAME', 'USER_TYPE', 'EMAIL'];

        $values = array_change_key_case($values, CASE_UPPER);

        $filter = ['ACTIVE' => 'Y'];

        foreach ($filterFields as $filterField) {
            if (!empty($values[$filterField])) {
                if ($filterField == 'UF_DEPARTMENT_NAME') {
                    $res = SectionTable::getList([
                      'select' => ['ID'],
                      'filter' => ['%NAME' => $values[$filterField], '=IBLOCK.CODE' => 'departments']
                    ]);
                    if ($row = $res->fetch()) {
                        $filter['UF_DEPARTMENT'] = [$row['ID']];
                    } else {
                        $filter['UF_DEPARTMENT'] = false;
                    }
                } else {
                    $filter[$filterField] = $values[$filterField];
                }
            }
        }

        $sortFields = ['LAST_NAME' => 'ASC', 'NAME' => 'ASC', 'SECOND_NAME' => 'ASC'];
        $fieldList = $request->get('fields') ?: '';
        $fieldList = $fieldList ? explode(',', $fieldList) : ['ID', 'LAST_NAME', 'NAME', 'SECOND_NAME', 'LOGIN'];
        $select = [];
        $fields = [];
        foreach ($fieldList as $item) {
            if (strpos($item,'UF_') === 0) {
                $select[] = $item;
            } else {
                $fields[] = $item;
            }
        }

        $params = [
            'SELECT' => $select,
            'FIELDS' => $fields,
            'NAV_PARAMS' => ['nTopCount' =>  $request->get('limit') ?: 50]
        ];

        $res = CUser::GetList($sortFields, $sortDir, $filter, $params);
        $data = [];
        while ($row = $res->GetNext()) {
            $item = $this->removeTildeItems($row);
            $item = $this->convertKeysToCamelCase($item);
            $data[] = $item;
        }

        return $data;
    }
}
