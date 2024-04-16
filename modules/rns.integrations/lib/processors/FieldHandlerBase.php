<?php

namespace RNS\Integrations\Processors;

/**
 * Базовый класс для кастомных обработчиков полей из внешних систем.
 * Class FieldHandlerBase
 * @package RNS\Integrations\Processors
 */
abstract class FieldHandlerBase
{
    public abstract function processField(string $name, $value, array $data);
}
