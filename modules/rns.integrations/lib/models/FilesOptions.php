<?php

namespace RNS\Integrations\Models;

/**
 * Настройки лоя интеграции путем обмена файлами.
 * @package RNS\Integrations\Models
 */
class FilesOptions extends OptionsBase implements \JsonSerializable
{
    /** @var string|null */
    private $converterUrl;

    /** @var integer|null */
    private $fileMaxSize;

    /** @var integer|null */
    private $taskMaxCount;

    /**
     * @return string|null
     */
    public function getConverterUrl(): ?string
    {
        return $this->converterUrl;
    }

    /**
     * @param string|null $converterUrl
     * @return FilesOptions
     */
    public function setConverterUrl(?string $converterUrl): FilesOptions
    {
        $this->converterUrl = $converterUrl;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getFileMaxSize(): ?int
    {
        return $this->fileMaxSize;
    }

    /**
     * @param mixed $fileMaxSize
     * @return FilesOptions
     */
    public function setFileMaxSize($fileMaxSize): FilesOptions
    {
        $this->fileMaxSize = intval($fileMaxSize);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getTaskMaxCount(): ?int
    {
        return $this->taskMaxCount;
    }

    /**
     * @param mixed $taskMaxCount
     * @return FilesOptions
     */
    public function setTaskMaxCount($taskMaxCount): FilesOptions
    {
        $this->taskMaxCount = intval($taskMaxCount);
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
