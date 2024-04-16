<?php

namespace RNS\Integrations\Processors\Hooks\jira;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Type\DateTime;
use RNS\Integrations\Helpers\HLBlockHelper;
use RNS\Integrations\Processors\FieldHandlerBase;

class FieldHandler extends FieldHandlerBase
{
    /**
     * @param string $name
     * @param $value
     * @param array $data
     * @return mixed
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function processField(string $name, $value, array $data)
    {
        switch ($name) {
            case 'cf_sprint':
                return $this->processSprint($value, $data);
        }
        return $value;
    }

    /**
     * @param $value
     * @param array $data
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    private function processSprint($value, array $data)
    {
        $regExp = '/\[id=(?<id>\d+),rapidViewId=\d+,state=(?<state>\w+),name=(?<name>[^,]+),startDate=(?<startDate>[^,]+),endDate=(?<endDate>[^,]+),completeDate=(?<completeDate>[^,]+),sequence=\d+,goal=.*?\]/i';
        $nullValue = '<null>';
        $result = [];

        if (!preg_match_all($regExp, $value, $matches, PREG_SET_ORDER, 0)) {
            return $value;
        }

        $hlb = HighloadBlockTable::getList(['filter' => ['NAME' => 'Sprint']])
          ->fetch();

        $statusMap = HLBlockHelper::getUserFieldEnumValues($hlb['ID'], 'UF_RNS_STATUS');

        foreach ($matches as $match) {
            $list = HLBlockHelper::getList($hlb['ID'], ['ID'], [], 'ID', ['=UF_RNS_EXTERNAL_ID' => $match['id']], false);
            $item = [];
            if (!empty($list)) {
                 $item = $list[0];
            }
            $item['UF_RNS_EXTERNAL_ID'] = intval($match['id']);
            $item['UF_NAME'] = trim($match['name']);
            $state = trim($match['state']);
            if (array_key_exists($state, $statusMap)) {
                $item['UF_RNS_STATUS'] = $statusMap[$state];
            }
            if ($match['startDate'] && $match['startDate'] != $nullValue) {
                $item['UF_DATE_START'] = DateTime::createFromTimestamp(strtotime($match['startDate']));
            }
            if ($match['endDate'] && $match['endDate'] != $nullValue) {
                $item['UF_DATE_FINISH'] = DateTime::createFromTimestamp(strtotime($match['endDate']));
            }
            if ($match['completeDate'] && $match['completeDate'] != $nullValue) {
                $item['UF_DATE_COMPLETE'] = DateTime::createFromTimestamp(strtotime($match['completeDate']));
            }
            if (!empty($data['GROUP_ID'])) {
                $item['UF_RNS_PROJECT'] = $data['GROUP_ID'];
            }
            $res = HLBlockHelper::save('Sprint', $item);
            if ($res->isSuccess()) {
                $id = get_class($res) == 'Bitrix\Main\ORM\Data\AddResult' ? $res->getId() : $item['ID'];
                $result[] = (int)$id;
            } else {
                $messages = array_map(function($err) {
                    return $err->getMessage();
                }, $res->getErrors());
                throw new \Exception(implode("\n", $messages));
            }
        }
        return $result;
    }
}
