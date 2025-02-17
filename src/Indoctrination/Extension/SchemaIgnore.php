<?php

/**
 * @package Indoctrination
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Indoctrination\Extension;

use DecodeLabs\Exceptional;
use DecodeLabs\Indoctrination\Extension;
use DecodeLabs\Indoctrination\ExtensionTrait;
use Doctrine\DBAL\Schema\AbstractAsset;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

class SchemaIgnore implements Extension
{
    use ExtensionTrait;

    /**
     * @var array<string,bool>
     */
    protected array $namespaces = [];

    /**
     * @var array<string,array<string,array<string,array<string>>>>
     */
    protected array $foreignKeys = [];

    private ?Schema $schema = null;

    /**
     * @param array<int|string, string|bool> $namespaces
     * @param array<string,array<string,array<string,array<string>>>> $foreignKeys
     */
    public function __construct(
        array $namespaces = [],
        array $foreignKeys = []
    ) {
        $this->foreignKeys = $foreignKeys;
        $this->namespaces = [];

        foreach ($namespaces as $namespace => $enabled) {
            if (is_int($namespace)) {
                $namespace = $enabled;
                $enabled = true;
            }

            $this->namespaces[(string)$namespace] = (bool)$enabled;
        }
    }


    public function loadForEntityManager(
        EntityManager $entityManager
    ): void {
        $entityManager->getEventManager()->addEventListener(
            'postGenerateSchema',
            $this
        );
    }

    public function filterSchemaAsset(
        string|AbstractAsset $asset
    ): ?bool {
        if ($asset instanceof AbstractAsset) {
            $asset = $asset->getName();
        }

        if (!str_contains($asset, '.')) {
            $schema = 'public';
        } else {
            $schema = explode('.', $asset, 2)[0];
        }

        if (isset($this->namespaces[$schema])) {
            return $this->namespaces[$schema];
        }

        return true;
    }


    /**
     * Handle generate schema event
     */
    public function postGenerateSchema(
        GenerateSchemaEventArgs $event
    ): void {
        $this->schema = $event->getSchema();

        $this->addNamespaces();
        $this->addForeignKeys();
    }

    /**
     * Add schema namespaces
     */
    protected function addNamespaces(): void
    {
        $schema = $this->getSchema();

        foreach ($this->namespaces as $namespace => $enabled) {
            $schema->createNamespace($namespace);
        }
    }

    /**
     * Add custom foreign keys to schema
     */
    protected function addForeignKeys(): void
    {
        $schema = $this->getSchema();

        foreach ($this->foreignKeys as $table => $keys) {
            $table = $schema->getTable($table);

            foreach ($keys as $target => $key) {
                $table->addForeignKeyConstraint(
                    $target,
                    // @phpstan-ignore-next-line
                    ...$key
                );
            }
        }
    }

    /**
     * Get schema
     */
    protected function getSchema(): Schema
    {
        if ($this->schema === null) {
            throw Exceptional::Setup(
                message: 'Schema has not been captured yet'
            );
        }

        return $this->schema;
    }
}
