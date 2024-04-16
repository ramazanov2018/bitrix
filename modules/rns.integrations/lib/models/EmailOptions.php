<?php

namespace RNS\Integrations\Models;

class EmailOptions extends OptionsBase implements \JsonSerializable
{
    /** @var string */
    private $type = '_default';
    /** @var integer|null */
    private $mailboxId;
    /** @var string|null */
    private $regexpTitle;
    /** @var string|null */
    private $regexpProject;
    /** @var string|null */
    private $regexpEndDate;
    /** @var string|null */
    private $regexpPriority;
    /** @var string|null */
    private $regexpTag;
    /** @var string|null */
    private $beginMarker;
    /** @var string|null */
    private $endMarker;
    /** @var string|null */
    private $acceptComment;
    /** @var string|null */
    private $refuseComment;
    /** @var string|null */
    private $errorMessage;
    /** @var string|null */
    private $taskIdTemplate;
    /** @var string|null */
    private $commentIdTemplate;
    /** @var string|null */
    private $subjectAcceptedTemplate;
    /** @var string|null */
    private $subjectDeclinedTemplate;

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return int|null
     */
    public function getMailboxId(): ?int
    {
        return $this->mailboxId;
    }

    /**
     * @param int|null $mailboxId
     * @return EmailOptions
     */
    public function setMailboxId(?int $mailboxId): EmailOptions
    {
        $this->mailboxId = $mailboxId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegexpTitle(): ?string
    {
        return $this->regexpTitle;
    }

    /**
     * @param string|null $regexpTitle
     * @return EmailOptions
     */
    public function setRegexpTitle(?string $regexpTitle): EmailOptions
    {
        $this->regexpTitle = $regexpTitle;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegexpProject(): ?string
    {
        return $this->regexpProject;
    }

    /**
     * @param string|null $regexpProject
     * @return EmailOptions
     */
    public function setRegexpProject(?string $regexpProject): EmailOptions
    {
        $this->regexpProject = $regexpProject;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegexpEndDate(): ?string
    {
        return $this->regexpEndDate;
    }

    /**
     * @param string|null $regexpEndDate
     * @return EmailOptions
     */
    public function setRegexpEndDate(?string $regexpEndDate): EmailOptions
    {
        $this->regexpEndDate = $regexpEndDate;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegexpPriority(): ?string
    {
        return $this->regexpPriority;
    }

    /**
     * @param string|null $regexpPriority
     * @return EmailOptions
     */
    public function setRegexpPriority(?string $regexpPriority): EmailOptions
    {
        $this->regexpPriority = $regexpPriority;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegexpTag(): ?string
    {
        return $this->regexpTag;
    }

    /**
     * @param string|null $regexpTag
     * @return EmailOptions
     */
    public function setRegexpTag(?string $regexpTag): EmailOptions
    {
        $this->regexpTag = $regexpTag;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBeginMarker(): ?string
    {
        return $this->beginMarker;
    }

    /**
     * @param string|null $beginMarker
     * @return EmailOptions
     */
    public function setBeginMarker(?string $beginMarker): EmailOptions
    {
        $this->beginMarker = $beginMarker;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEndMarker(): ?string
    {
        return $this->endMarker;
    }

    /**
     * @param string|null $endMarker
     * @return EmailOptions
     */
    public function setEndMarker(?string $endMarker): EmailOptions
    {
        $this->endMarker = $endMarker;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAcceptComment(): ?string
    {
        return $this->acceptComment;
    }

    /**
     * @param string|null $acceptComment
     * @return EmailOptions
     */
    public function setAcceptComment(?string $acceptComment): EmailOptions
    {
        $this->acceptComment = $acceptComment;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRefuseComment(): ?string
    {
        return $this->refuseComment;
    }

    /**
     * @param string|null $refuseComment
     * @return EmailOptions
     */
    public function setRefuseComment(?string $refuseComment): EmailOptions
    {
        $this->refuseComment = $refuseComment;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * @param string|null $errorMessage
     * @return EmailOptions
     */
    public function setErrorMessage(?string $errorMessage): EmailOptions
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTaskIdTemplate(): ?string
    {
        return $this->taskIdTemplate;
    }

    /**
     * @param string|null $taskIdTemplate
     * @return EmailOptions
     */
    public function setTaskIdTemplate(?string $taskIdTemplate): EmailOptions
    {
        $this->taskIdTemplate = $taskIdTemplate;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCommentIdTemplate(): ?string
    {
        return $this->commentIdTemplate;
    }

    /**
     * @param string|null $commentIdTemplate
     * @return EmailOptions
     */
    public function setCommentIdTemplate(?string $commentIdTemplate): EmailOptions
    {
        $this->commentIdTemplate = $commentIdTemplate;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSubjectAcceptedTemplate(): ?string
    {
        return $this->subjectAcceptedTemplate;
    }

    /**
     * @param string|null $subjectAcceptedTemplate
     * @return EmailOptions
     */
    public function setSubjectAcceptedTemplate(?string $subjectAcceptedTemplate): EmailOptions
    {
        $this->subjectAcceptedTemplate = $subjectAcceptedTemplate;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSubjectDeclinedTemplate(): ?string
    {
        return $this->subjectDeclinedTemplate;
    }

    /**
     * @param string|null $subjectDeclinedTemplate
     * @return EmailOptions
     */
    public function setSubjectDeclinedTemplate(?string $subjectDeclinedTemplate): EmailOptions
    {
        $this->subjectDeclinedTemplate = $subjectDeclinedTemplate;
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
