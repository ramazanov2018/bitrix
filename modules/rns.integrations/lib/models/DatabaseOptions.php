<?php

namespace RNS\Integrations\Models;

/**
 * Настройки для интеграции с помощью запросов к СУБД.
 * @package RNS\Integrations\Models
 */
class DatabaseOptions extends OptionsBase implements \JsonSerializable
{
    /** @var string */
    private $type = 'pgsql';
    /** @var string|null */
    private $hostName;
    /** @var int|null */
    private $port;
    /** @var string|null */
    private $databaseName;
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
     * @param string|null $type
     * @return DatabaseOptions
     */
    public function setType(?string $type): DatabaseOptions
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHostName(): ?string
    {
        return $this->hostName;
    }

    /**
     * @param string|null $hostName
     * @return DatabaseOptions
     */
    public function setHostName(?string $hostName): DatabaseOptions
    {
        $this->hostName = $hostName;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * @param mixed $port
     * @return DatabaseOptions
     */
    public function setPort($port): DatabaseOptions
    {
        $this->port = intval($port);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDatabaseName(): ?string
    {
        return $this->databaseName;
    }

    /**
     * @param string|null $databaseName
     * @return DatabaseOptions
     */
    public function setDatabaseName(?string $databaseName): DatabaseOptions
    {
        $this->databaseName = $databaseName;
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
     * @return DatabaseOptions
     */
    public function setUserName(?string $userName): DatabaseOptions
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
     * @return DatabaseOptions
     */
    public function setPassword(?string $password): DatabaseOptions
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
