<?php

namespace RNS\Integrations\Processors;

class DataTransferResult
{
    /** @var bool */
    public $success = false;
    /** @var int */
    public $objectsTotal = 0;
    /** @var int */
    public $objectsAdded = 0;
    /** @var int */
    public $objectsUpdated = 0;
    /** @var array */
    public $errors = [];
}
