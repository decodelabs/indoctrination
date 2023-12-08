<?php

/**
 * @package Indoctrination
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Indoctrination;

interface Config
{
    /**
     * Get poolable connection URL for given name
     */
    public function getSharedConnection(
        ?string $name = null
    ): string;

    /**
     * Get connection URL for given name
     */
    public function getAdminConnection(
        ?string $name = null
    ): string;

    /**
     * Get entity config paths
     *
     * @return array<string>
     */
    public function getPaths(
        ?string $name = null
    ): array;

    /**
     * Get metadata type
     */
    public function getMetadataType(
        ?string $name = null
    ): MetadataType;

    /**
     * Get extension name list
     *
     * @return array<string, array<mixed>>
     */
    public function getExtensions(
        ?string $name = null
    ): array;

    /**
     * Get migration config for given name
     *
     * @return array<string, mixed>
     */
    public function getMigrationsConfig(
        ?string $name = null
    ): array;
}
