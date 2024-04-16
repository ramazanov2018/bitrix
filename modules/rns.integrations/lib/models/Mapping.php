<?php

namespace RNS\Integrations\Models;

use RNS\Integrations\Helpers\TableHelper;
use RNS\Integrations\MapTypeTable;

class Mapping
{
    /** @var EntityMap */
    private $projectMap;
    /** @var EntityTypeMap */
    private $entityTypeMap;
    /** @var EntityStatusMap */
    private $entityStatusMap;
    /** @var PropertyMap */
    private $entityPropertyMap;
    /** @var UserMap */
    private $userMap;
    /** @var ResponsibleSettings */
    private $responsibleSettings;

    public function __construct()
    {
        $this->projectMap = new EntityMap();
        $this->entityTypeMap = new EntityTypeMap();
        $this->entityStatusMap = new EntityStatusMap();
        $this->entityPropertyMap = new PropertyMap();
        $this->userMap = new UserMap();
        $this->responsibleSettings = new ResponsibleSettings();
    }

    /**
     * @return EntityMap
     */
    public function getProjectMap(): EntityMap
    {
        return $this->projectMap;
    }

    /**
     * @param EntityMap $projectMap
     * @return Mapping
     */
    public function setProjectMap(EntityMap $projectMap): Mapping
    {
        $this->projectMap = $projectMap;
        return $this;
    }

    /**
     * @return EntityTypeMap
     */
    public function getEntityTypeMap(): EntityTypeMap
    {
        return $this->entityTypeMap;
    }

    /**
     * @param EntityTypeMap $entityTypeMap
     * @return Mapping
     */
    public function setEntityTypeMap(EntityTypeMap $entityTypeMap): Mapping
    {
        $this->entityTypeMap = $entityTypeMap;
        return $this;
    }

    /**
     * @return EntityStatusMap
     */
    public function getEntityStatusMap(): EntityStatusMap
    {
        return $this->entityStatusMap;
    }

    /**
     * @param EntityStatusMap $entityStatusMap
     * @return Mapping
     */
    public function setEntityStatusMap(EntityStatusMap $entityStatusMap): Mapping
    {
        $this->entityStatusMap = $entityStatusMap;
        return $this;
    }

    /**
     * @return PropertyMap
     */
    public function getEntityPropertyMap(): PropertyMap
    {
        return $this->entityPropertyMap;
    }

    /**
     * @param PropertyMap $entityPropertyMap
     * @return Mapping
     */
    public function setEntityPropertyMap(PropertyMap $entityPropertyMap): Mapping
    {
        $this->entityPropertyMap = $entityPropertyMap;
        return $this;
    }

    /**
     * @return UserMap
     */
    public function getUserMap(): UserMap
    {
        return $this->userMap;
    }

    /**
     * @param UserMap $userMap
     * @return Mapping
     */
    public function setUserMap(UserMap $userMap): Mapping
    {
        $this->userMap = $userMap;
        return $this;
    }

    /**
     * @return ResponsibleSettings
     */
    public function getResponsibleSettings(): ResponsibleSettings
    {
        return $this->responsibleSettings;
    }

    /**
     * @param ResponsibleSettings $responsibleSettings
     * @return Mapping
     */
    public function setResponsibleSettings(ResponsibleSettings $responsibleSettings): Mapping
    {
        $this->responsibleSettings = $responsibleSettings;
        return $this;
    }
}
