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
                'sharedConnection' => "{{Env::asString(['DATABASE_SHARED', 'DATABASE'])}}",
                'adminConnection' => "{{Env::asString(['DATABASE_ADMIN', 'DATABASE'])}}",

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
        return $this->data->__get($name)->sharedConnection->as('string');
    }

    /**
     * Get connection URL for given name
     */
    public function getAdminConnection(
        ?string $name = null
    ): string {
        $name = $this->normalizeName($name);
        return $this->data->__get($name)->adminConnection->as('string');
    }


    /**
     * Get entity config paths
     */
    public function getPaths(
        ?string $name = null
    ): array {
        $name = $this->normalizeName($name);
        return $this->data->__get($name)->paths->as('string[]');
    }


    /**
     * Get metadata type
     */
    public function getMetadataType(
        ?string $name = null
    ): MetadataType {
        $name = $this->normalizeName($name);
        $output = $this->data->__get($name)->metadata->as('string');

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
        $name = $this->normalizeName($name);
        $output = $this->data->__get($name)->extensions->toArray();

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

        /** @var array<string,array<mixed>> */
        return $output;
    }


    /**
     * Get migration config for given name
     */
    public function getMigrationsConfig(
        ?string $name = null
    ): array {
        $name = $this->normalizeName($name);
        /** @var array<string,array<mixed>> $output */
        $output = $this->data->__get($name)->migrations->toArray();
        return $output;
    }

    /**
     * Normalize name
     */
    protected function normalizeName(?string $name): string
    {
        if (
            $name === null ||
            !$this->data->__isset($name)
        ) {
            $name = 'default';
        }

        return $name;
    }
}
