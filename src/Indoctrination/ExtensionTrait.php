<?php

/**
 * @package Indoctrination
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Indoctrination;

use Doctrine\DBAL\Schema\AbstractAsset;
use Doctrine\ORM\Configuration as OrmConfig;
use Doctrine\ORM\EntityManager;
use ReflectionClass;

/**
 * @phpstan-require-implements Extension
 */
trait ExtensionTrait
{
    public string $name {
        get => new ReflectionClass($this)
            ->getShortName();
    }

    public function loadGlobal(): void
    {
        // no-op
    }

    public function loadForEntityManager(
        EntityManager $entityManager
    ): void {
        // no-op
    }

    public function loadForOrmConfig(
        OrmConfig $ormConfig
    ): void {
        // no-op
    }

    public function filterSchemaAsset(
        string|AbstractAsset $asset
    ): bool {
        return true;
    }
}
