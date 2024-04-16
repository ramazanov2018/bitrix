<?php

namespace RNS\Integrations\Models;

class OptionsBase implements \JsonSerializable
{
    /** @var int|null */
    private $taskLevel = 1;

    /**
     * @return int|null
     */
    public function getTaskLevel(): ?int
    {
        return $this->taskLevel;
    }

    /**
     * @param int|null $taskLevel
     * @return OptionsBase
     */
    public function setTaskLevel(?int $taskLevel): OptionsBase
    {
        $this->taskLevel = $taskLevel;
        return $this;
    }

    public function __get($name)
    {
        return $this->{'get'.ucwords($name)}();
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
