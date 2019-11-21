<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Exception;

use Symfony\Component\Console\Exception\ExceptionInterface;

/**
 * An exception whose output is displayed as a clean error.
 */
final class RuntimeCommandException extends \RuntimeException implements ExceptionInterface
{
}
