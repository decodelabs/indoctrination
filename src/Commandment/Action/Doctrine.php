<?php

/**
 * @package Indoctrination
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Commandment\Action;

use DecodeLabs\Coercion;
use DecodeLabs\Commandment\Action;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Dovetail\Config\Doctrine as DoctrineConfig;
use DecodeLabs\Indoctrination;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\ConsoleRunner as MigrationsConsoleRunner;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;

class Doctrine implements Action
{
    protected const array Commands = [];

    public function execute(
        Request $request
    ): bool {
        Indoctrination::clearCache();

        $argv = Coercion::toArray($_SERVER['argv'] ?? []);

        if (isset($argv[1])) {
            unset($argv[1]);
        }

        $argv = array_values($argv);
        $command = Coercion::asString($argv[1] ?? '');
        $target = explode(':', $command)[0];

        $_SERVER['argv'] = $argv;
        $_SERVER['argc'] = count($argv);

        match ($target) {
            'migrations' => $this->runMigrations(),
            default => $this->runRoot()
        };

        return true;
    }

    protected function runRoot(): void
    {
        $entityManager = Indoctrination::getEntityManager();

        ConsoleRunner::run(
            new SingleManagerProvider($entityManager),
            self::Commands
        );
    }

    protected function runMigrations(): void
    {
        $config = DoctrineConfig::load();
        $migrationConfig = new ConfigurationArray($config->getMigrationsConfig());
        $entityManager = Indoctrination::getEntityManager();
        $dependencyFactory = DependencyFactory::fromEntityManager($migrationConfig, new ExistingEntityManager($entityManager));
        MigrationsConsoleRunner::run([], $dependencyFactory);
    }
}
