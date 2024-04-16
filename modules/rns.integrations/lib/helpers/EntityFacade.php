<?php

namespace RNS\Integrations\Helpers;

use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserFieldLangTable;
use Bitrix\Main\UserFieldTable;
use CMailError;
use CModule;
use CSocNetGroup;
use CUser;
use RNS\Integrations\Models\IntegrationSettings;
use RNS\Integrations\Models\Mapping;
use RNS\Integrations\Models\OptionsBase;

class EntityFacade
{
    private static $statusMap = [
        'TODO' => 1,
        'HOLD' => 2,
        'IN_PROGRESS' => 3,
        'REVIEW' => 4,
        'CLOSED' => 5,
        'DEFERRED' => 6,
        'DECLINED' => 7
    ];

    /**
     * @param array $codes
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getEntityTypes(array $codes = [])
    {
        $list = [
          'REFERENCE_ID' => [],
          'REFERENCE' => []
        ];
        if (self::checkIndustrialOffice()) {
            $filter = ['=UF_ACTIVE' => 1];
            if (!empty($codes)) {
                $filter['UF_CODE'] = $codes;
            }
            $items = HLBlockHelper::getList('b_hlsys_entities', ['ID', 'UF_NAME', 'UF_CODE'], ['ID'],
              'UF_CODE', $filter);
            foreach ($items as $key => $item) {
                $list['REFERENCE_ID'][] = $key;
                $list['REFERENCE'][] = $item['UF_NAME'];
            }
        } else {
            $list['REFERENCE_ID'][] = 'TASK';
            $list['REFERENCE'][] = 'Задача';
        }
        return $list;
    }

    /**
     * @param string $entityType
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getEntityStatuses(string $entityType)
    {
        $list = [
          'REFERENCE_ID' => [],
          'REFERENCE' => []
        ];
        if (self::checkIndustrialOffice()) {
            $items = HLBlockHelper::getList('b_hlsys_status_entity', ['ID', 'UF_CODE', 'UF_RUS_NAME'], ['ID'],
              'UF_CODE', ['UF_ENTITY_TYPE_BIND' => $entityType, 'UF_ACTIVE' => 1]);
            foreach ($items as $key => $item) {
                $list['REFERENCE_ID'][] = $key;
                $list['REFERENCE'][] = $item['UF_RUS_NAME'];
            }
        } else {
            $list['REFERENCE_ID'][] = 'TODO';
            $list['REFERENCE'][] = 'Новая';
            $list['REFERENCE_ID'][] = 'HOLD';
            $list['REFERENCE'][] = 'В ожидании';
            $list['REFERENCE_ID'][] = 'IN_PROGRESS';
            $list['REFERENCE'][] = 'В работе';
            $list['REFERENCE_ID'][] = 'REVIEW';
            $list['REFERENCE'][] = 'На проверке';
            $list['REFERENCE_ID'][] = 'CLOSED';
            $list['REFERENCE'][] = 'Закрыта';
            $list['REFERENCE_ID'][] = 'DEFERRED';
            $list['REFERENCE'][] = 'Отложена';
            $list['REFERENCE_ID'][] = 'DECLINED';
            $list['REFERENCE'][] = 'Отклонена';
        }
        return $list;
    }

    /**
     * @param string $systemCode
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getExternalEntityTypes(string $systemCode)
    {
        return self::getHLBlockItems('b_hlsys_external_entities', $systemCode);
    }

    /**
     * @param string $systemCode
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getExternalEntityStatuses(string $systemCode)
    {
        return self::getHLBlockItems('b_hlsys_external_entity_statuses', $systemCode);
    }

    /**
     * @param bool $userFieldsOnly
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getEntityProperties(bool $userFieldsOnly = false)
    {
        if (!$userFieldsOnly) {
            $fixedFields = [
              'REFERENCE_ID' => [
                'TITLE',
                'PARENT_ID',
                'STATUS',
                'PRIORITY',
                'CREATED_BY',
                'RESPONSIBLE_ID',
                'DESCRIPTION',
                'CREATED_DATE',
                'CHANGED_DATE',
                'START_DATE_PLAN',
                'END_DATE_PLAN',
                'TIME_ESTIMATE',
                'DURATION_FACT',
                'DEADLINE',
                'DATE_START',
                'CLOSED_DATE',
                'GROUP_ID',
                  // virtual fields
                'TAGS',
                'RELATED_TASKS',
                'COMMENTS'
              ],
              'REFERENCE' => [
                'Название задачи',
                'Идентификатор родительской задачи',
                'Статус',
                'Приоритет задачи',
                'Автор',
                'Ответственный',
                'Описание задачи',
                'Дата создания',
                'Дата изменения',
                'Планируемая дата начала',
                'Планируемая дата окончания',
                'Время, отведенное на задачу',
                'Затраченное время',
                'Дедлайн',
                'Дата начала',
                'Дата завершения',
                'Проект',
                  // virtual fields
                'Тэги',
                'Связанные задачи',
                'Комментарии'
              ]
            ];
        }

        $list = [
          'REFERENCE_ID' => [],
          'REFERENCE' => []
        ];
        $rs = UserFieldTable::getList([
          'select' => ['ID', 'FIELD_NAME'],
          'filter' => ['ENTITY_ID' => 'TASKS_TASK'],
          'order' => ['SORT' => 'ASC']
        ]);
        $fields = $rs->fetchAll();
        foreach ($fields as $field) {
            $rs = UserFieldLangTable::getList([
              'select' => ['USER_FIELD_ID', 'LIST_COLUMN_LABEL'],
              'filter' => ['USER_FIELD_ID' => $field['ID'], 'LANGUAGE_ID' => 'ru']
            ]);
            $lang = $rs->fetch();
            $list['REFERENCE_ID'][] = $field['FIELD_NAME'];
            $list['REFERENCE'][] = !empty($lang['LIST_COLUMN_LABEL']) ? '[UF] ' . $lang['LIST_COLUMN_LABEL'] : $field['FIELD_NAME'];
        }
        return !$userFieldsOnly ? array_merge_recursive($fixedFields, $list) : $list;
    }

    /**
     * @param string $systemCode
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getExternalEntityProperties(string $systemCode)
    {
        return self::getHLBlockItems('b_hlsys_external_entity_properties', $systemCode);
    }

    /**
     * Возвращает список активных проектов для выбора.
     * @return array
     */
    public static function getProjects()
    {
        \CModule::IncludeModule('socialnetwork');

        $res = CSocNetGroup::GetList([], ['ACTIVE' => 'Y'], false, false, ['ID', 'NAME']);
        $result = [
          'REFERENCE_ID' => [],
          'REFERENCE' => []
        ];
        while ($row = $res->GetNext()) {
            $result['REFERENCE_ID'][] = $row['ID'];
            $result['REFERENCE'][] = $row['NAME'];
        }
        return $result;
    }

    /**
     * Возвращает список активных пользователей для выбора.
     * @return array
     */
    public static function getUsers()
    {
        $by = 'LAST_NAME, NAME, SECOND_NAME';
        $order = 'ASC';
        $res = CUser::GetList($by, $order, ['ACTIVE' => 'Y']);
        $result = [
          'REFERENCE_ID' => [],
          'REFERENCE' => []
        ];
        while ($row = $res->GetNext()) {
            $result['REFERENCE_ID'][] = $row['ID'];
            $result['REFERENCE'][] = $row['LAST_NAME'] . ' ' . $row['NAME'] . ' ' . ($row['SECOND_NAME'] ?? '') .
              ' (' . $row['EMAIL'] . ')';
        }
        return $result;
    }

    public static function getExternalProjects(
      string $exchangeTypeCode,
      string $systemCode,
      OptionsBase $options,
      Mapping $mapping,
      array $projectIds = [])
    {
        $provider = self::getDataProvider($exchangeTypeCode, $systemCode, $options, $mapping);
        return $provider->getProjects($projectIds);
    }

    public static function getExternalUsers(string $echangeTypeCode, string $systemCode, OptionsBase $options, Mapping $mapping)
    {
        $provider = self::getDataProvider($echangeTypeCode, $systemCode, $options, $mapping);
        return $provider->getUsers();
    }

    /**
     * @param int $mailboxId
     * @param DateTime|null $fromDate
     * @return array
     */
    public static function getMailMessages(int $mailboxId, ?DateTime $fromDate)
    {
        global $DB;
//        \Bitrix\Main\Loader::includeModule('mail');
//        CMailError::ResetErrors();
//        $newMessages = \Bitrix\Mail\Helper::syncMailbox($mailboxId, $error);

        $filter = [
            'MAILBOX_ID' => $mailboxId,
            'NEW_MESSAGE' => 'Y'
        ];

        $res = \CMailMessage::GetList(['FIELD_DATE' => 'ASC'], $filter);
        $result = [];
        while ($row = $res->GetNext()) {
//            if ($fromDate && (new DateTime($row['DATE_INSERT'], 'd.m.Y H:i:s')) < $fromDate) {
//                continue;
//            }
            $comment = '';
            if (!empty($row['OPTIONS'])) {
                $options = $row['OPTIONS'];
                if (!empty($options['iCal'])) {
                    $iCal = $options['iCal'];
                    $label = 'COMMENT:';
                    $pos = mb_strpos($iCal, $label);
                    if ($pos !== false) {
                        $endPos = mb_strpos($iCal, '\n', $pos);
                        if ($endPos !== false) {
                            $pos += strlen($label);
                            $comment = mb_substr($iCal, $pos, $endPos - $pos);
                            $comment = str_replace(["\t", "\r", "\n"], '', $comment);
                        }
                    }
                }
            }
            $item  = [
              'id' => $row['ID'],
              'author' => $row['FIELD_FROM'],
              'recipients' => $row['FIELD_TO'],
              'created' => $row['FIELD_DATE'],
              'subject' => $row['SUBJECT'],
              'body' => $row['BODY'],
              'coexecutors' => $row['FIELD_CC'],
              'comment' => $comment,
              'files' => []
            ];
            $rs = \CMailAttachment::GetList([], ['MESSAGE_ID' => $row['ID']]);
            while ($attachment = $rs->fetch()) {
                $ext = pathinfo($attachment['FILE_NAME'], PATHINFO_EXTENSION);
                if (strtolower($ext) == 'file') {
                    continue;
                }
                $fileId = $attachment['FILE_ID'];
                $DB->Update('b_file', [
                  'ORIGINAL_NAME' => "'" . str_replace("'", "''", $attachment['FILE_NAME']) . "'"
                ], "WHERE ID = {$fileId}");
                $item['files'][] = $fileId;
            }
            $result[] = $item;
        }
        return $result;
    }

    /**
     * @param string $exchangeTypeCode
     * @param string $systemCode
     * @param OptionsBase $options
     * @param Mapping $mapping
     * @return mixed
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getDataProvider(
      string $exchangeTypeCode,
      string $systemCode,
      OptionsBase $options,
      Mapping $mapping
    )
    {
        $providerClassPath = $_SERVER['DOCUMENT_ROOT'] . '/local/modules/rns.integrations/lib/processors/' . $exchangeTypeCode . '/' .
          $options->getType() . '/DataProvider.php';
        include_once($providerClassPath);
        $providerClass = "RNS\\Integrations\\Processors\\{$exchangeTypeCode}\\{$options->getType()}\\DataProvider";
        $integrationOptions = new IntegrationSettings($systemCode);
        return new $providerClass($systemCode, $integrationOptions, $options, $mapping);
    }

    /**
     * @param $type
     * @param $host
     * @param $port
     * @param $dbName
     * @param $userName
     * @param $password
     * @return bool
     */
    public static function testDbConnection($type, $host, $port, $dbName, $userName, $password)
    {
        switch ($type) {
            case 'pgsql':
                $connStr = "host={$host} port={$port} dbname={$dbName} user={$userName} password={$password}";
                $conn = pg_connect($connStr);
                if ($conn) {
                    pg_close($conn);
                    return true;
                }
                return false;
            default:
                return false;
        }
    }

    /**
     * @return bool
     */
    public static function checkIndustrialOffice()
    {
        return CModule::IncludeModule('industrial.office');
    }

    /**
     * @param string|null $code
     * @return array|mixed
     */
    public static function getStatusMap(?string $code = null)
    {
        return $code ? static::$statusMap[$code] : static::$statusMap;
    }

    /**
     * @param string $tableName
     * @param string $systemCode
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private static function getHLBlockItems(string $tableName, string $systemCode)
    {
        $items = HLBlockHelper::getList($tableName, ['ID', 'UF_NAME', 'UF_CODE'], ['ID'],
          'UF_CODE', ['UF_SYSTEM_CODE' => $systemCode, 'UF_ACTIVE' => 1]);
        $list = [
          'REFERENCE_ID' => [],
          'REFERENCE' => []
        ];
        foreach ($items as $key => $item) {
            $list['REFERENCE_ID'][] = $key;
            $list['REFERENCE'][] = $item['UF_NAME'];
        }
        return $list;
    }
}
