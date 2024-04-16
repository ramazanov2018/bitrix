<?php

namespace RNS\Integrations\Processors\database\pgsql;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use RNS\Integrations\Helpers\EntityFacade;
use RNS\Integrations\Models\IntegrationSettings;
use RNS\Integrations\Models\Mapping;
use RNS\Integrations\Models\OptionsBase;
use RNS\Integrations\Processors\DataProviderBase;

class DataProvider extends DataProviderBase
{
    private $conn;

    /**
     * DataProvider constructor.
     * @param string $systemCode
     * @param IntegrationSettings $integrationOptions
     * @param OptionsBase $options
     * @param Mapping $mapping
     */
    public function __construct(
      string $systemCode,
      IntegrationSettings $integrationOptions,
      OptionsBase $options,
      Mapping $mapping
    ) {
        parent::__construct($systemCode, $integrationOptions, $options, $mapping);
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {
        return extension_loaded('php_sqlsrv');
    }

    /**
     * @param array $projectIds
     * @return array
     * @throws \Exception
     */
    public function getProjects(array $projectIds = [])
    {
        if (!$this->isAvailable()) {
            return [];
        }
        $this->connect();

        $tableName = $this->integrationOptions->getProjectSource();
        $keyField = $this->integrationOptions->getProjectKeyField();
        $displayField = $this->integrationOptions->getProjectDisplayField();

        $displayField = explode(',', $displayField);

        if (count($displayField) > 1) {
            $displayField = implode("|| ', ' ||", $displayField);
        } else {
            $displayField = $displayField[0];
        }

        $sql = "select {$keyField} as id, {$displayField} as name from {$tableName}";

        if (!empty($projectIds)) {
            $sql .= " where {$keyField} in (" .  implode(',', array_map(function($item) {
                  return "'" . $item . "'";
              }, $projectIds)) . ')';
        }

        $res = sqlsrv_query($this->conn, $sql);

        $data = [];
        while ($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
            $data[$row['id']] = $row['name'];
        }

        $this->disconnect();

        return $data;
    }

    /**
     * Возвращает массив сущностей для импорта. Если указана $fromDate,
     * то ограничивает выборку только измененными после этой даты сущносятми.
     * @param DateTime|null $fromDate
     * @return array
     * @throws \Exception
     */
    public function getEntities(?DateTime $fromDate = null)
    {
        $result = [];
        if (!$this->isAvailable()) {
            return $result;
        }

        $srcTableName = $this->integrationOptions->getEntitySource();
        $prjTableName = $this->integrationOptions->getProjectSource();
        $prjKeyFieldName = $this->integrationOptions->getProjectKeyField();
        $refFieldName = $this->integrationOptions->getEntityRefFieldName();
        $modifiedFieldName = $this->integrationOptions->getModifiedFieldName();
        $createdFieldName = $this->integrationOptions->getCreatedFieldName();
        $idFieldName = $this->integrationOptions->getEntityIdFieldName();

        $fields = EntityFacade::getExternalEntityProperties($this->systemCode);
        $fields = array_map(function($item) {
            return 't.' . $item;
        },  $fields['REFERENCE_ID']);
        $fields[] = 't.' . $modifiedFieldName;
        $fieldNames = implode(', ', $fields);

        $fromDateStr = $fromDate ? $fromDate->format('Y-m-d H:i:s') : '2000-01-01 00:00:00';

        $sql =
          "select {$fieldNames} from {$srcTableName} t
           inner join {$prjTableName} p on t.{$refFieldName} = p.{$prjKeyFieldName}
           where ISNULL(t.{$modifiedFieldName}, t.{$createdFieldName}) > '{$fromDateStr}'
           order by t.{$createdFieldName}, t.{$idFieldName}";

        $this->connect();

        $res = sqlsrv_query($this->conn, $sql);

        while ($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
            $result[] = $row;
        }

        $this->disconnect();

        return $result;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getUsers()
    {
        if (!$this->isAvailable()) {
            return [];
        }

        $this->connect();

        $tableName = $this->integrationOptions->getUserSource();
        $keyField = $this->integrationOptions->getUserSourceKeyField();
        $displayField = $this->integrationOptions->getUserSourceDisplayField();

        $displayField = explode(',', $displayField);

        if (count($displayField) > 1) {
            $displayField = implode(" || ', ' || ", $displayField);
        } else {
            $displayField = $displayField[0];
        }

        $sql = "select {$keyField} as id, {$displayField} as name from {$tableName}";

        $res = sqlsrv_query($this->conn, $sql);

        $data = [];
        while ($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
            $data[$row['id']] = $row['name'];
        }

        $this->disconnect();

        return $data;
    }

    /**
     * @param string $key
     * @return bool|mixed
     * @throws \Exception
     */
    public function getEntityIdByKey(string $key)
    {
        $result = false;
        if (!$this->isAvailable()) {
            return $result;
        }

        $srcTableName = $this->integrationOptions->getEntitySource();
        $keyFieldName = $this->integrationOptions->getEntityKeyField();
        $idFieldName = $this->integrationOptions->getEntityIdFieldName();

        $this->connect();

        $sql = "select {$idFieldName} from {$srcTableName} where {$keyFieldName} = '{$key}'";

        $res = sqlsrv_query($this->conn, $sql);

        if ($row = sqlsrv_fetch_array($res)) {
            $result = $row[0];
        }

        $this->disconnect();

        return $result;
    }

    /**
     * @param $id
     * @return int
     * @throws \Exception
     */
    public function getEntityLevel($id)
    {
        $result = 1;
        if (!$this->isAvailable()) {
            return $result;
        }

        $srcTableName = $this->integrationOptions->getEntitySource();
        $idFieldName = $this->integrationOptions->getEntityIdFieldName();
        $parentFieldName = $this->integrationOptions->getEntityParentIdFieldName();

        $sql = "select {$idFieldName}, {$parentFieldName} from {$srcTableName} where {$idFieldName} = {$id}";

        $this->connect();

        while (true) {
            $res = sqlsrv_query($this->conn, $sql);
            if ((!$row = sqlsrv_fetch_array($res)) || empty($row[1])) {
                break;
            }
            sqlsrv_free_stmt($res);
            $result++;
            $id = $row[1];
            $sql = "select {$idFieldName}, {$parentFieldName} from {$srcTableName} where {$idFieldName} = {$id}";
        }

        return $result;
    }

    /**
     * @throws \Exception
     */
    private function connect()
    {
        $info = [
          'Database' => $this->options->getDatabaseName(),
          'UID' => $this->options->getUserName(),
          'PWD' => $this->options->getPassword()
        ];
        $this->conn = sqlsrv_connect($this->options->getHostName(), $info);
        if (!$this->conn) {
            throw new \Exception(Loc::getMessage('INTEGRATIONS_PROCESSOR_BASE_CONNECT_ERROR'));
        }
    }

    private function disconnect()
    {
        sqlsrv_close($this->conn);
    }
}
