<?php

/**
 * @package Indoctrination
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Indoctrination\Type;

use DecodeLabs\Exceptional;
use DecodeLabs\Guidance;
use DecodeLabs\Guidance\Uuid as UuidObject;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Stringable;

class Uuid extends Type
{
    /**
     * Get name
     */
    public function getName(): string
    {
        return 'uuid';
    }

    /**
     * Get SQL declaration
     */
    public function getSQLDeclaration(
        array $column,
        AbstractPlatform $platform
    ): string {
        if ($this->hasNativeGuidType($platform)) {
            return $platform->getGuidTypeDeclarationSQL($column);
        }

        return $platform->getBinaryTypeDeclarationSQL([
            'length' => 16,
            'fixed' => true,
        ]);
    }

    /**
     * Convert to PHP value
     */
    public function convertToPHPValue(
        mixed $value,
        AbstractPlatform $platform
    ): ?UuidObject {
        if (
            $value instanceof UuidObject ||
            null === $value
        ) {
            return $value;
        }

        if (
            !is_string($value) &&
            !$value instanceof Stringable
        ) {
            throw Exceptional::InvalidType([
                'message' => 'Invalid type: ' . gettype($value),
                'data' => $value
            ]);
        }

        return Guidance::fromString($value);
    }

    /**
     * Convert to database value
     */
    public function convertToDatabaseValue(
        mixed $value,
        AbstractPlatform $platform
    ): ?string {
        $toString = $this->hasNativeGuidType($platform) ? '__toString' : 'getBytes';

        if ($value instanceof UuidObject) {
            return $value->$toString();
        }

        if (
            $value === null ||
            $value === ''
        ) {
            return null;
        }

        if (
            !is_string($value) &&
            !$value instanceof Stringable
        ) {
            throw Exceptional::InvalidType([
                'message' => 'Invalid type: ' . gettype($value),
                'data' => $value
            ]);
        }

        return Guidance::fromString($value)->$toString();
    }

    public function requiresSQLCommentHint(
        AbstractPlatform $platform
    ): bool {
        return true;
    }

    private function hasNativeGuidType(
        AbstractPlatform $platform
    ): bool {
        return $platform->getGuidTypeDeclarationSQL([]) !== $platform->getStringTypeDeclarationSQL(['fixed' => true, 'length' => 36]);
    }
}
