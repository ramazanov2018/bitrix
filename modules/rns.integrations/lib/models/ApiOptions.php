<?php

namespace RNS\Integrations\Models;

/**
 * Настройки для интеграции посредством API.
 * @package RNS\Integrations\Models
 */
class ApiOptions extends OptionsBase implements \JsonSerializable
{
    /** @var string */
    private $type = 'rest';
    /** @var string|null */
    private $endpoint;
    /** @var string|null */
    private $userName;
    /** @var string|null */
    private $password;

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    /**
     * @param string|null $endpoint
     * @return ApiOptions
     */
    public function setEndpoint(?string $endpoint): ApiOptions
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUserName(): ?string
    {
        return $this->userName;
    }

    /**
     * @param string|null $userName
     * @return ApiOptions
     */
    public function setUserName(?string $userName): ApiOptions
    {
        $this->userName = $userName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     * @return ApiOptions
     */
    public function setPassword(?string $password): ApiOptions
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), get_object_vars($this));
    }
}
