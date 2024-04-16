<?php

namespace RNS\Integrations\Controller;

use Bitrix\Main\Engine\Controller;

abstract class ControllerBase extends Controller
{
    protected function removeTildeItems(array $row)
    {
        return array_filter($row, function($key) {
            return strpos($key, '~') === false;
        }, ARRAY_FILTER_USE_KEY);
    }
}
