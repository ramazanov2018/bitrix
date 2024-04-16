<?php

namespace RNS\Integrations\Models;

class ResponsibleSettings
{
    /** @var int|null */
    private $defaultResponsibleId;
    /** @var bool */
    private $executorLoading = false;
    /** @var int|null */
    private $defaultAuthorId;
    /** @var bool */
    private $authorLoading = false;
    /** @var int|null */
    private $defaultDeadlineDays = 0;

    /**
     * @return int|null
     */
    public function getDefaultResponsibleId(): ?int
    {
        return $this->defaultResponsibleId;
    }

    /**
     * @param int|null $defaultResponsibleId
     * @return ResponsibleSettings
     */
    public function setDefaultResponsibleId(?int $defaultResponsibleId): ResponsibleSettings
    {
        $this->defaultResponsibleId = $defaultResponsibleId;
        return $this;
    }

    /**
     * @return bool
     */
    public function isExecutorLoading(): bool
    {
        return $this->executorLoading;
    }

    /**
     * @param bool $executorLoading
     * @return ResponsibleSettings
     */
    public function setExecutorLoading(bool $executorLoading): ResponsibleSettings
    {
        $this->executorLoading = $executorLoading;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getDefaultAuthorId(): ?int
    {
        return $this->defaultAuthorId;
    }

    /**
     * @param int|null $defaultAuthorId
     * @return ResponsibleSettings
     */
    public function setDefaultAuthorId(?int $defaultAuthorId): ResponsibleSettings
    {
        $this->defaultAuthorId = $defaultAuthorId;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAuthorLoading(): bool
    {
        return $this->authorLoading;
    }

    /**
     * @param bool $authorLoading
     * @return ResponsibleSettings
     */
    public function setAuthorLoading(bool $authorLoading): ResponsibleSettings
    {
        $this->authorLoading = $authorLoading;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getDefaultDeadlineDays(): ?int
    {
        return $this->defaultDeadlineDays;
    }

    /**
     * @param int|null $defaultDeadlineDays
     * @return ResponsibleSettings
     */
    public function setDefaultDeadlineDays(?int $defaultDeadlineDays): ResponsibleSettings
    {
        $this->defaultDeadlineDays = $defaultDeadlineDays;
        return $this;
    }
}
