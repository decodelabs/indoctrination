<?php

/**
 * Indoctrination
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Indoctrination;

enum MetadataType: string
{
    case Attributes = 'attributes';
    case Xml = 'xml';
}
