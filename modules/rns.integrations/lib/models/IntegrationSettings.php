<?php

namespace RNS\Integrations\Models;

use RNS\Integrations\Helpers\HLBlockHelper;

class IntegrationSettings
{
    /** @var string|null */
    private $entitySource;
    /** @var string|null */
    private $entityKeyField;
    /** @var string|null */
    private $modifiedFieldName;
    /** @var string|null */
    private $createdFieldName;
    /** @var string|null */
    private $entityIdFieldName;
    /** @var string|null */
    private $entityParentIdFieldName;
    /** @var string|null */
    private $entityRefFieldName;
    /** @var string|null */
    private $projectSource;
    /** @var string|null */
    private $projectKeyField;
    /** @var string|null */
    private $projectDisplayField;
    /** @var string|null */
    private $userSource;
    /** @var string|null */
    private $userSourceKeyField;
    /** @var string|null */
    private $userSourceDisplayField;
    /** @var bool|null */
    private $translitNeeded;
    /** @var string|null */
    private $valueMapping;

    /**
     * IntegrationSettings constructor.
     * @param string $systemCode
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function __construct(string $systemCode)
    {
        $system = HLBlockHelper::getList('b_hlsys_task_source', ['ID'], [], 'ID',
          ['UF_XML_ID' => strtoupper($systemCode)], false);

        $options = HLBlockHelper::getList('b_hlsys_integration_settings', [], ['ID'], 'ID',
          ['UF_SOURCE_ID' => $system[0]['ID']], false);
        $options = $options[0];

        $this->entitySource = $options['UF_ENTITY_SOURCE'];
        $this->entityKeyField = $options['UF_ENTITY_KEY_FIELD'];
        $this->modifiedFieldName = $options['UF_IS_SAVED_FIELD_NAME'];
        $this->createdFieldName = $options['UF_CREATED_FIELD_NAME'];
        $this->entityIdFieldName = $options['UF_ENTITY_ID_FIELD_NAME'];
        $this->entityParentIdFieldName = $options['UF_PARENT_FIELD_NAME'];
        $this->entityRefFieldName = $options['UF_ENTITY_REF_FIELD_NAME'];
        $this->projectSource = $options['UF_PROJECT_SOURCE'];
        $this->projectKeyField = $options['UF_PROJECT_KEY_FIELD'];
        $this->projectDisplayField = $options['UF_PROJECT_DISPLAY_FIELD'];
        $this->userSource = $options['UF_USER_SOURCE'];
        $this->userSourceKeyField = $options['UF_USER_SOURCE_KEY_FIELD'];
        $this->userSourceDisplayField = $options['UF_USER_DISPLAY_FIELD'];
        $this->translitNeeded = $options['UF_TRANSLIT_NEEDED'];
        $this->valueMapping = $options['UF_VALUE_MAPPING'];
    }

    /**
     * @return string|null
     */
    public function getEntitySource(): ?string
    {
        return $this->entitySource;
    }

    /**
     * @return string|null
     */
    public function getEntityKeyField(): ?string
    {
        return $this->entityKeyField;
    }

    /**
     * @return string|null
     */
    public function getModifiedFieldName(): ?string
    {
        return $this->modifiedFieldName;
    }

    /**
     * @return string|null
     */
    public function getCreatedFieldName(): ?string
    {
        return $this->createdFieldName;
    }

    /**
     * @return string|null
     */
    public function getEntityIdFieldName(): ?string
    {
        return $this->entityIdFieldName;
    }

    /**
     * @return string|null
     */
    public function getEntityParentIdFieldName(): ?string
    {
        return $this->entityParentIdFieldName;
    }

    /**
     * @return string|null
     */
    public function getEntityRefFieldName(): ?string
    {
        return $this->entityRefFieldName;
    }

    /**
     * @return string|null
     */
    public function getProjectSource(): ?string
    {
        return $this->projectSource;
    }

    /**
     * @return string|null
     */
    public function getProjectKeyField(): ?string
    {
        return $this->projectKeyField;
    }

    /**
     * @return string|null
     */
    public function getProjectDisplayField(): ?string
    {
        return $this->projectDisplayField;
    }

    /**
     * @return string|null
     */
    public function getUserSource(): ?string
    {
        return $this->userSource;
    }

    /**
     * @return string|null
     */
    public function getUserSourceKeyField(): ?string
    {
        return $this->userSourceKeyField;
    }

    /**
     * @return string|null
     */
    public function getUserSourceDisplayField(): ?string
    {
        return $this->userSourceDisplayField;
    }

    /**
     * @return bool|null
     */
    public function isTranslitNeeded(): ?bool
    {
        return $this->translitNeeded;
    }

    /**
     * @return string|null
     */
    public function getValueMapping(): ?string
    {
        return $this->valueMapping;
    }

}
