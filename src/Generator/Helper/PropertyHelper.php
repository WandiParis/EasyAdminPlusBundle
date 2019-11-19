<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Helper;

use Doctrine\ORM\Mapping\Column;

class PropertyHelper
{
    public static function hasClass(array $propertyClasses, string $classTargeted): bool
    {
        return null != self::getClassFromArray($propertyClasses, $classTargeted);
    }

    public static function hasDoctrineColumnType(array $propertyDoctrineClasses, string $doctrineTargetType): bool
    {
        $column = self::getClassFromArray($propertyDoctrineClasses, Column::class);

        if (!$column) {
            return false;
        }

        /* @var Column $column */
        return $column->type == $doctrineTargetType;
    }

    public static function getClassFromArray(array $arrayClasses, string $classTargeted)
    {
        $class = array_filter($arrayClasses, function ($class) use ($classTargeted) {
            return $class instanceof $classTargeted;
        });

        return array_values($class)[0] ?? null;
    }
}
