<?php

namespace RNS\Integrations;

use Bitrix\Main\Entity;
use Bitrix\Main\Entity\Query\Join;
use Bitrix\Main\UserTable;

class ExternalSystemTable extends Entity\DataManager
{


    public static function getTableName()
    {
        return 'integration_external_system';
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
          new Entity\StringField('NAME'),
          new Entity\StringField('CODE'),
          new Entity\DatetimeField('CREATED'),
          new Entity\IntegerField('CREATED_BY'),
          new Entity\ReferenceField('AUTHOR', UserTable::class,
            Join::on('this.CREATED_BY', 'ref.ID')),
          new Entity\DatetimeField('MODIFIED'),
          new Entity\IntegerField('MODIFIED_BY'),
          new Entity\ReferenceField('EDITOR', UserTable::class,
            Join::on('this.MODIFIED_BY', 'ref.ID')),
          new Entity\StringField('DESCRIPTION'),
          new Entity\BooleanField('ACTIVE', ['values' => ['N', 'Y'], 'default_value' => 'Y'])
        ];
    }
}
