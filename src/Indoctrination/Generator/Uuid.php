<?php

/**
 * @package Indoctrination
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Indoctrination\Generator;

use DecodeLabs\Guidance;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AbstractIdGenerator;

class Uuid extends AbstractIdGenerator
{
    public function generateId(
        EntityManagerInterface $entityManager,
        ?object $entity
    ): string {
        return Guidance::createV7UuidString();
    }
}
