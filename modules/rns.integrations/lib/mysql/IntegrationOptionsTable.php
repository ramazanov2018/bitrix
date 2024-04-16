<?php

namespace RNS\Integrations;

use Bitrix\Main\Entity;
use Bitrix\Main\Entity\Query\Join;
use Bitrix\Main\UserTable;

class IntegrationOptionsTable extends Entity\DataManager
{
    const DIRECTION_IMPORT = 0;
    const DIRECTION_EXPORT = 1;

    public static function getTableName()
    {
        return 'integration_options';
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getMap()
    {
        return [
          new Entity\IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
          new Entity\IntegerField('SYSTEM_ID'),
          new Entity\ReferenceField('SYSTEM', ExternalSystemTable::class,
            Join::on('this.SYSTEM_ID', 'ref.ID')),
          new Entity\IntegerField('EXCHANGE_TYPE_ID'),
          new Entity\ReferenceField('EXCHANGE_TYPE', ExchangeTypeTable::class,
            Join::on('this.EXCHANGE_TYPE_ID', 'ref.ID')),
          new Entity\IntegerField('DIRECTION'),
          new Entity\StringField('NAME'),
          new Entity\IntegerField('SCHEDULE'),
          new Entity\BooleanField('ACTIVE', ['values' => ['N', 'Y'], 'default_value' => 'Y']),
          new Entity\DatetimeField('CREATED'),
          new Entity\DatetimeField('CREATED_BY'),
          new Entity\IntegerField('MODIFIED_BY'),
          new Entity\ReferenceField('AUTHOR', UserTable::class,
            Join::on('this.CREATED_BY', 'ref.ID')),
          new Entity\DatetimeField('MODIFIED'),
          new Entity\IntegerField('MODIFIED_BY'),
          new Entity\ReferenceField('EDITOR', UserTable::class,
            Join::on('this.MODIFIED_BY', 'ref.ID')),
          new Entity\StringField('PROCESSOR_CLASS_NAME'),
          new Entity\TextField('OPTIONS'),
          new Entity\TextField('MAPPING'),
          new Entity\DatetimeField('LAST_OPERATION_DATE'),
          new Entity\TextField('DESCRIPTION')
        ];
    }
}
