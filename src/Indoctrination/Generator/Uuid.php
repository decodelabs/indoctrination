<?php

/**
 * @package Indoctrination
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Indoctrination\Generator;

use DecodeLabs\Guidance;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AbstractIdGenerator;

class Uuid extends AbstractIdGenerator
{
    /**
     * @param object $entity
     */
    public function generate(
        EntityManager $entityManager,
        $entity
    ): string {
        return (string)Guidance::createV7();
    }
}
