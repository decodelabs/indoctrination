<?php

/**
 * Indoctrination
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Indoctrination;

use Doctrine\DBAL\Schema\AbstractAsset;
use Doctrine\DBAL\Schema\Name;
use Doctrine\ORM\Configuration as OrmConfig;
use Doctrine\ORM\EntityManager;

interface Extension
{
    public string $name { get; }

    public function loadGlobal(): void;

    public function loadForOrmConfig(
        OrmConfig $ormConfig
    ): void;

    public function loadForEntityManager(
        EntityManager $entityManager
    ): void;

    /**
     * @param string|AbstractAsset<Name> $asset
     */
    public function filterSchemaAsset(
        string|AbstractAsset $asset
    ): ?bool;
}
