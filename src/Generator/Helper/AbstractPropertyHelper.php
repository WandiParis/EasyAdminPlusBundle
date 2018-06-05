<?php

namespace Lle\EasyAdminPlusBundle\Generator\Helper;

class AbstractPropertyHelper
{
    private static $helperMask = [
        'function' => '',
        'methods' => [],
    ];

    public static function getMaskHelper(): array
    {
        return self::$helperMask;
    }
}
