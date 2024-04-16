<?php

namespace RNS\Integrations\Processors;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use RNS\Integrations\Models\IntegrationSettings;
use RNS\Integrations\Models\Mapping;
use RNS\Integrations\Models\OptionsBase;

/**
 * Base class for all data providers.
 * Class DataProviderBase
 * @package RNS\Integrations\Processors
 */
abstract class DataProviderBase
{
    /** @var string */
    protected $systemCode;

    protected $options;

    protected $mapping;

    /** @var IntegrationSettings */
    protected $integrationOptions;

    /**
     * DataProviderBase constructor.
     * @param string $systemCode
     * @param IntegrationSettings $integrationOptions
     * @param OptionsBase $options
     * @param Mapping $mapping
     */
    protected function __construct(
      string $systemCode,
      IntegrationSettings $integrationOptions,
      OptionsBase $options,
      Mapping $mapping
    ) {
        $this->systemCode = $systemCode;
        $this->integrationOptions = $integrationOptions;
        $this->options = $options;
        $this->mapping = $mapping;

        Loc::loadMessages(__FILE__);
    }

    public abstract function isAvailable();
    public abstract function getProjects(array $projectIds = []);
    public abstract function getEntities(?DateTime $fromDate = null);
    public abstract function getUsers();
    public abstract function getEntityIdByKey(string $key);
    public abstract function getEntityLevel($id);
}
