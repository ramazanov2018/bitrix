<?php

namespace RNS\Integrations\Models;

class UserMapItem
{
    /** @var string|null */
    private $externalId;
    /** @var int|null */
    private $internalId;

    /**
     * @return string|null
     */
    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    /**
     * @param string|null $externalId
     * @return UserMapItem
     */
    public function setExternalId(?string $externalId): UserMapItem
    {
        $this->externalId = $externalId;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getInternalId(): ?int
    {
        return $this->internalId;
    }

    /**
     * @param int|null $internalId
     * @return UserMapItem
     */
    public function setInternalId(?int $internalId): UserMapItem
    {
        $this->internalId = $internalId;
        return $this;
    }
}
