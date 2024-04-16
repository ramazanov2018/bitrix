<?php

namespace RNS\Integrations\Helpers;

use Bitrix\Main\Loader;
use CMailbox;
use COption;
use RNS\Integrations\Models\IntegrationOptionsTableWrapper;
use RNS\Integrations\IntegrationOptionsTable;

class ImportHelper
{
    /**
     * Возвращает максимально допустимый размер импортируемого файла указанного формата.
     * @param string $format
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getFileMaxSize(string $format)
    {
        $systemCode = '';
        switch ($format) {
            case 'mpp':
            case 'mpx':
                $systemCode = 'ms_project';
                break;
        }
        if (!$systemCode) {
            return 0;
        }
        $res = IntegrationOptionsTable::getList([
          'select' => ['ID'],
          'filter' => ['=SYSTEM.CODE' => $systemCode]
        ]);
        if (!$row = $res->fetch()) {
            return 0;
        }
        $obj = IntegrationOptionsTableWrapper::getById($row['ID']);
        $options = $obj->getOptions();
        return $options->getFileMaxSize() ?: 0;
    }

    /**
     * Возвращает список проектов, в которых участвует текущий пользователь.
     * @return array
     * @throws \Bitrix\Main\LoaderException
     */
    public static function getUserProjects()
    {
        global $USER;

        Loader::includeModule('socialnetwork');

        $groupIds = [];
        $res = \CSocNetUserToGroup::GetList([], ['USER_ID' => $USER->getID()], false, false, ['GROUP_ID']);
        while ($row = $res->GetNext()) {
            $groupIds[] = $row['GROUP_ID'];
        }
        $result = [];
        $res = \CSocNetGroup::GetList([], ['ID' => $groupIds], false, false, ['ID', 'NAME']);
        while ($row = $res->GetNext()) {
            $result[$row['ID']] = $row['NAME'];
        }
        return $result;
    }

    /**
     * Возвращает список почтовых ящиков.
     * @return array
     */
    public static function getMailboxList()
    {
        $res = CMailBox::GetList([],['ACTIVE' => 'Y']);
        $list = [
          'REFERENCE_ID' => [],
          'REFERENCE' => []
        ];
        while ($row = $res->GetNext()) {
            $list['REFERENCE_ID'][] = $row['ID'];
            $list['REFERENCE'][] = $row['NAME'] . ' (' . $row['LOGIN'] . ')';
        }
        return $list;
    }

    /**
     * Возвращает код внешней системы по формату (расширению) файла.
     * @param string $format
     * @return string
     * @throws \Exception
     */
    public static function getSystemCodeByFormat(string $format)
    {
        switch ($format) {
            case 'mpp':
                return 'ms_project';
            default:
                throw new \Exception('Unsupported format: ' . $format);
        }
    }

    /**
     * Форматирует заданный в секундах интервал времени в hh:mm
     * @param $seconds
     * @return string
     */
    public static function formatTime($seconds)
    {
        $fullHours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        return sprintf('%02d:%02d', $fullHours, $minutes);
    }

    /**
     * Автоматическое сопоставление пользователей
     */
    public static function automaticUserMapping(int $systemId = 0)
    {
        $Filter["active"] = "Y";

        if($systemId > 0)
            $Filter["ID"] = $systemId;

        $ResOb = IntegrationOptionsTable::getList(["filter" => $Filter, "select" => ["ID", "MAPPING"]]);

        while ($Res = $ResOb->fetch()){
            $systemId = (int)$Res["ID"];
            $optionMapping = json_decode($Res["MAPPING"]??"{}", true);
            $integrationOptions = IntegrationOptionsTableWrapper::getById($systemId);
            $systemCode = $integrationOptions->getSystemCode();
            $exchType = $integrationOptions->getExchangeTypeCode();

            try {
                $users = EntityFacade::getExternalUsers($exchType, $systemCode, $integrationOptions->getOptions(), $integrationOptions->getMapping());
            } catch (\Throwable $ex) {
                continue;
            }

            $UsersEmail = [];
            foreach ($users as $id => $name) {
                $nameParams = explode(",", $name);
                foreach ($nameParams as $key => $value){
                    $value = trim($value);
                    if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $UsersEmail[$value] = $id;
                    }
                }
            }


            $emails = implode("|", array_keys($UsersEmail));
            $portalUsers = [];
            if (strlen($emails) > 0){
                $resOb = \CUser::GetList(($by="ID"), ($order="desc"), ["EMAIL" => $emails,  "!EMAIL" => ""]);
                while($res = $resOb->fetch()) {
                    $portalUsers[($res["EMAIL"])] = (int)$res["ID"];
                }
            }

            $optionsUserItems = $optionMapping['userMap']['items']??[];
            foreach ($UsersEmail as $email => $id){
                if($portalUsers[$email] > 0){
                    $itemValue['externalId'] = $id;
                    $itemValue['internalId'] = $portalUsers[$email];
                    $found_key = array_search($id, array_column($optionsUserItems, 'externalId'));
                    if($found_key !== false)
                        $optionMapping['userMap']['items'][$found_key] = $itemValue;
                    else
                        $optionMapping['userMap']['items'][] = $itemValue;

                }
            }
            $Fields['MAPPING'] = json_encode($optionMapping);
            $res = IntegrationOptionsTable::update($systemId, $Fields);
        }
    }
}
