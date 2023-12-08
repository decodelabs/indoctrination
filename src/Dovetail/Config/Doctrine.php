<?php

/**
 * @package Indoctrination
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Dovetail\Config;

use DecodeLabs\Dovetail\Config;
use DecodeLabs\Dovetail\ConfigTrait;
use DecodeLabs\Indoctrination\Config as IndoctrinationConfig;
use DecodeLabs\Indoctrination\MetadataType;

class Doctrine implements Config, IndoctrinationConfig
{
    use ConfigTrait;

    public static function getDefaultValues(): array
    {
        return [
            'default' => [
                'sharedConnection' => "{{envString(['DATABASE_SHARED', 'DATABASE'])}}",
                'adminConnection' => "{{envString(['DATABASE_ADMIN', 'DATABASE'])}}",

                'paths' => [],
                'metadata' => 'attributes',
                'extensions' => [],
                'listeners' => [],
                'ignoreSchemas' => [],

                'migrations' => [
                    'table_storage' => [
                        'table_name' => 'doctrine_migration_versions',
                        'version_column_name' => 'version',
                        'version_column_length' => 191,
                        'executed_at_column_name' => 'executed_at',
                        'execution_time_column_name' => 'execution_time',
                    ],

                    'migrations_paths' => [],

                    'all_or_nothing' => true,
                    'transactional' => true,
                    'check_database_platform' => true,
                    'organize_migrations' => 'none',
                ]
            ]
        ];
    }


    /**
     * Get poolable connection URL for given name
     */
    public function getSharedConnection(
        ?string $name = null
    ): string {
        $name = $this->normalizeName($name);
        return $this->data->{$name}->sharedConnection->as('string');
    }

    /**
     * Get connection URL for given name
     */
    public function getAdminConnection(
        ?string $name = null
    ): string {
        $name = $this->normalizeName($name);
        return $this->data->{$name}->adminConnection->as('string');
    }


    /**
     * Get entity config paths
     */
    public function getPaths(
        ?string $name = null
    ): array {
        $name = $this->normalizeName($name);
        return $this->data->{$name}->paths->as('string[]');
    }


    /**
     * Get metadata type
     */
    public function getMetadataType(
        ?string $name = null
    ): MetadataType {
        $name = $this->normalizeName($name);
        $output = $this->data->{$name}->metadata->as('string');

        if (!$output = MetadataType::tryFrom($output)) {
            $output = MetadataType::Attributes;
        }

        return $output;
    }


    /**
     * Get extension config
     */
    public function getExtensions(
        ?string $name = null
    ): array {
        $output = $this->data->{$name}->extensions->toArray();

        foreach ($output as $key => $value) {
            if (is_string($value)) {
                unset($output[$key]);
                $output[$value] = [];
                continue;
            }

            if (!is_string($key)) {
                unset($output[$key]);
                continue;
            }
        }

        return $output;
    }


    /**
     * Get migration config for given name
     */
    public function getMigrationsConfig(
        ?string $name = null
    ): array {
        $name = $this->normalizeName($name);
        return $this->data->{$name}->migrations->toArray();
    }

    /**
     * Normalize name
     */
    protected function normalizeName(?string $name): string
    {
        if (
            $name === null ||
            !isset($this->data->{$name})
        ) {
            $name = 'default';
        }

        return $name;
    }
}
