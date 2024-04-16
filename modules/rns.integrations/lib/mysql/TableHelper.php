<?php

namespace RNS\Integrations\Helpers;

class TableHelper
{
    public static function getTableColumns($tableName)
    {
        global $DB;

        $sql = <<<SQL
SELECT COLUMN_NAME, COLUMN_COMMENT, DATA_TYPE, IS_NULLABLE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE
FROM `INFORMATION_SCHEMA`.`COLUMNS`
WHERE `TABLE_SCHEMA`='{$DB->DBName}'  AND `TABLE_NAME`='{$tableName}';
SQL;
        $res = $DB->Query($sql);
        $result = [];
        while ($row = $res->GetNext()) {
            $result[] = new Column(
              $row['COLUMN_NAME'],
              $row['COLUMN_COMMENT'],
              $row['DATA_TYPE'],
              $row['IS_NULLABLE'] == 'YES',
              $row['CHARACTER_MAXIMUM_LENGTH'],
              $row['NUMERIC_PRECISION'],
              $row['NUMERIC_SCALE']
            );
        }

        return $result;
    }
}
