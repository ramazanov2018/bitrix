<?php

namespace RNS\Integrations\Models;

class PropertyMapItem
{
    /** @var mixed */
    private $externalProjectId;
    /** @var mixed */
    private $externalTypeId;
    /** @var mixed */
    private $externalPropertyId;
    /** @var mixed */
    private $internalTypeId;
    /** @var mixed */
    private $internalPropertyId;

    /**
     * @return mixed
     */
    public function getExternalProjectId()
    {
        return $this->externalProjectId;
    }

    /**
     * @param mixed $externalProjectId
     * @return PropertyMapItem
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
     * @return PropertyMapItem
     */
    public function setExternalTypeId($externalTypeId): PropertyMapItem
    {
        $this->externalTypeId = $externalTypeId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExternalPropertyId()
    {
        return $this->externalPropertyId;
    }

    /**
     * @param mixed $externalPropertyId
     * @return PropertyMapItem
     */
    public function setExternalPropertyId($externalPropertyId): PropertyMapItem
    {
        $this->externalPropertyId = $externalPropertyId;
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
     * @return PropertyMapItem
     */
    public function setInternalTypeId($internalTypeId): PropertyMapItem
    {
        $this->internalTypeId = $internalTypeId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInternalPropertyId()
    {
        return $this->internalPropertyId;
    }

    /**
     * @param mixed $internalPropertyId
     * @return PropertyMapItem
     */
    public function setInternalPropertyId($internalPropertyId): PropertyMapItem
    {
        $this->internalPropertyId = $internalPropertyId;
        return $this;
    }
}
