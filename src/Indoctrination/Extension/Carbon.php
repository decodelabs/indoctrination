<?php

/**
 * @package Indoctrination
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Indoctrination\Extension;

use Carbon\Doctrine\DateTimeImmutableType;
use Carbon\Doctrine\DateTimeType;
use DecodeLabs\Exceptional;
use DecodeLabs\Indoctrination\Extension;
use DecodeLabs\Indoctrination\ExtensionTrait;
use Doctrine\DBAL\Types\Type;

class Carbon implements Extension
{
    use ExtensionTrait;

    public function loadGlobal(): void
    {
        if (!class_exists(DateTimeType::class)) {
            throw Exceptional::ComponentUnavailable(
                'Carbon package is not available'
            );
        }

        Type::overrideType('date', DateTimeType::class);
        Type::overrideType('datetime', DateTimeType::class);
        Type::overrideType('datetime_immutable', DateTimeImmutableType::class);
    }
}
