<?php

namespace RNS\Integrations;

use Bitrix\Main\Entity;

class ExchangeTypeTable extends Entity\DataManager
{
    const TYPE_API      = 'api';
    const TYPE_EMAIL    = 'email';
    const TYPE_DATABASE = 'database';
    const TYPE_FILES    = 'files';

    public static function getTableName()
    {
        return 'integration_exchange_type';
    }

    /**
     * @return array
     * @throws \Bitrix\Main\SystemException
     */
    public static function getMap()
    {
        return [
          new Entity\IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
          new Entity\StringField('NAME'),
          new Entity\StringField('CODE')
        ];
    }
}