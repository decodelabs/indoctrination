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

interface Extension
{
    public function getName(): string;

    public function loadGlobal(): void;

    public function loadForOrmConfig(
        OrmConfig $ormConfig
    ): void;

    public function loadForEntityManager(
        EntityManager $entityManager
    ): void;

    public function filterSchemaAsset(
        string|AbstractAsset $asset
    ): ?bool;
}
