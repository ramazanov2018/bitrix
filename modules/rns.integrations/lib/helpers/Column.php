<?php

namespace RNS\Integrations\Helpers;

/**
 * Class Column
 * @package RNS\Integrations\Helpers
 * Описывает поле в таблице БД.
 */
class Column
{
    /** @var string */
    private $name;
    /** @var string */
    private $comment;
    /** @var string */
    private $dataType;
    /** @var bool */
    private $isNullable;
    /** @var int|null */
    private $maxLength;
    /** @var int|null */
    private $precision;
    /** @var int|null */
    private $scale;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @return string
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @return bool
     */
    public function isNullable()
    {
        return $this->isNullable;
    }

    /**
     * @return int|null
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    /**
     * @return int|null
     */
    public function getPrecision()
    {
        return $this->precision;
    }

    /**
     * @return int|null
     */
    public function getScale()
    {
        return $this->scale;
    }

    /**
     * Column constructor.
     * @param string $name
     * @param string $comment
     * @param string $dataType
     * @param bool $isNullable
     * @param int|null $maxLength
     * @param int|null $precision
     * @param int|null $scale
     */
    public function __construct($name, $comment, $dataType, $isNullable, $maxLength, $precision, $scale)
    {
        $this->name = $name;
        $this->comment = $comment;
        $this->dataType = $dataType;
        $this->isNullable = $isNullable;
        $this->maxLength = $maxLength;
        $this->precision = $precision;
        $this->scale = $scale;
    }
}
