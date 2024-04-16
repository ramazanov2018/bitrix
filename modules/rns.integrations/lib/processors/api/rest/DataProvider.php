<?php

namespace RNS\Integrations\Processors\api\rest;

use Bitrix\Main\Type\DateTime;
use RNS\Integrations\Models\IntegrationSettings;
use RNS\Integrations\Models\Mapping;
use RNS\Integrations\Models\OptionsBase;
use RNS\Integrations\Processors\DataProviderBase;

class DataProvider extends DataProviderBase
{
    public function __construct(
      string $systemCode,
      IntegrationSettings $integrationOptions,
      OptionsBase $options,
      Mapping $mapping
    ) {
        parent::__construct($systemCode, $integrationOptions, $options, $mapping);
    }

    public function isAvailable()
    {
        return false;
    }

    public function getProjects(array $projectIds = [])
    {
        // TODO: Implement getProjects() method.
    }

    public function getEntities(?DateTime $fromDate = null)
    {
        // TODO: Implement getEntities() method.
    }

    public function getUsers()
    {
        // TODO: Implement getUsers() method.
    }

    public function getEntityIdByKey(string $key)
    {
        // TODO: Implement getEntityKeyById() method.
    }

    public function getEntityLevel($id)
    {
        return 1;
    }
}
