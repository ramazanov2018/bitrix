<?php

namespace RNS\Integrations\Models;

class EntityStatusMapItem
{
    /** @var mixed */
    private $externalProjectId;
    /** @var mixed */
    private $externalTypeId;
    /** @var mixed */
    private $externalStatusId;
    /** @var mixed */
    private $internalTypeId;
    /** @var mixed */
    private $internalStatusId;

    /**
     * @return mixed
     */
    public function getExternalProjectId()
    {
        return $this->externalProjectId;
    }

    /**
     * @param mixed $externalProjectId
     * @return EntityStatusMapItem
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
     * @return EntityStatusMapItem
     */
    public function setExternalTypeId($externalTypeId): EntityStatusMapItem
    {
        $this->externalTypeId = $externalTypeId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExternalStatusId()
    {
        return $this->externalStatusId;
    }

    /**
     * @param mixed $externalStatusId
     * @return EntityStatusMapItem
     */
    public function setExternalStatusId($externalStatusId): EntityStatusMapItem
    {
        $this->externalStatusId = $externalStatusId;
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
     * @return EntityStatusMapItem
     */
    public function setInternalTypeId($internalTypeId): EntityStatusMapItem
    {
        $this->internalTypeId = $internalTypeId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInternalStatusId()
    {
        return $this->internalStatusId;
    }

    /**
     * @param mixed $internalStatusId
     * @return EntityStatusMapItem
     */
    public function setInternalStatusId($internalStatusId): EntityStatusMapItem
    {
        $this->internalStatusId = $internalStatusId;
        return $this;
    }
}
