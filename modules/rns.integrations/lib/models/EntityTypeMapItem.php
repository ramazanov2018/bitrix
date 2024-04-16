<?php

namespace RNS\Integrations\Models;

class EntityTypeMapItem
{
    /** @var mixed */
    private $externalProjectId;
    /** @var mixed */
    private $externalTypeId;
    /** @var mixed */
    private $internalTypeId;

    /**
     * @return mixed
     */
    public function getExternalProjectId()
    {
        return $this->externalProjectId;
    }

    /**
     * @param mixed $externalProjectId
     * @return EntityTypeMapItem
     */
    public function setExternalProjectId($externalProjectId)
    {
        $this->externalProjectId = $externalProjectId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExternalTypeId()
    {
        return $this->externalTypeId;
    }

    /**
     * @param mixed $externalTypeId
     * @return EntityTypeMapItem
     */
    public function setExternalTypeId($externalTypeId): EntityTypeMapItem
    {
        $this->externalTypeId = $externalTypeId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInternalTypeId()
    {
        return $this->internalTypeId;
    }

    /**
     * @param mixed $internalTypeId
     * @return EntityTypeMapItem
     */
    public function setInternalTypeId($internalTypeId): EntityTypeMapItem
    {
        $this->internalTypeId = $internalTypeId;
        return $this;
    }
}
