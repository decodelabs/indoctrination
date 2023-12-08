<?php

/**
 * @package Indoctrination
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Indoctrination\Extension;

use DecodeLabs\Indoctrination\Extension;
use DecodeLabs\Indoctrination\ExtensionTrait;
use DecodeLabs\Indoctrination\Type\Uuid as UuidType;
use Doctrine\DBAL\Types\Type;

class Uuid implements Extension
{
    use ExtensionTrait;

    public function loadGlobal(): void
    {
        if (!Type::hasType('uuid')) {
            Type::addType('uuid', UuidType::class);
        }
    }
}
