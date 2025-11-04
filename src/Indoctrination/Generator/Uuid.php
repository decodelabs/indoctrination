<?php

/**
 * Indoctrination
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Indoctrination\Generator;

use DecodeLabs\Guidance;
use DecodeLabs\Monarch;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AbstractIdGenerator;

class Uuid extends AbstractIdGenerator
{
    public function generateId(
        EntityManagerInterface $entityManager,
        ?object $entity
    ): string {
        $guidance = Monarch::getService(Guidance::class);
        return $guidance->createV7UuidString();
    }
}
