<?php

/**
 * @package Indoctrination
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Indoctrination;

use Closure;
use DecodeLabs\Archetype;
use DecodeLabs\Cipher\Payload as CipherPayload;
use DecodeLabs\Dovetail;
use DecodeLabs\Dovetail\Config\Doctrine as DoctrineConfig;
use DecodeLabs\Genesis;
use DecodeLabs\Glitch\Dumper\Entity;
use DecodeLabs\Indoctrination;
use DecodeLabs\Slingshot;
use DecodeLabs\Stash;
use DecodeLabs\Veneer;
use DecodeLabs\Veneer\LazyLoad;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\AbstractAsset;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\ORM\Configuration as OrmConfig;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

#[LazyLoad]
class Context
{
    protected static bool $init = false;

    /**
     * @var array<string,EntityManager>
     */
    protected array $entityManagers = [];
    protected ?Slingshot $slingshot = null;

    /**
     * @var array<string,array<string,Extension>>
     */
    protected array $extensions = [];

    public function __construct()
    {
        $this->init();
    }

    public function init(): void
    {
        if (static::$init) {
            return;
        }

        static::$init = true;

        // Register in Genesis container
        if (
            class_exists(Genesis::class) &&
            !Genesis::$container->has(EntityManager::class)
        ) {
            Genesis::$container->bindShared(
                EntityManager::class,
                fn () => $this->getEntityManager()
            );
        }
    }

    /**
     * Get EntityManager for given name
     */
    public function getEntityManager(
        ?string $name = null
    ): EntityManager {
        if ($name === null) {
            $name = 'default';
        }

        if (!isset($this->entityManagers[$name])) {
            $this->entityManagers[$name] = $this->loadEntityManager($name);
        }

        return $this->entityManagers[$name];
    }

    /**
     * Load entity manager for given name
     */
    protected function loadEntityManager(
        string $name
    ): EntityManager {
        $extensions = $this->loadExtenions($name);
        $config = $this->loadOrmConfig($name);
        $connection = $this->loadConnection($name, $config);
        $em = new EntityManager($connection, $config);


        // Extensions
        foreach ($extensions as $extension) {
            $extension->loadForEntityManager($em);
        }

        return $em;
    }

    /**
     * Load extensions
     *
     * @return array<string,Extension>
     */
    protected function loadExtenions(
        string $name
    ): array {
        $slingshot = $this->getSlingshot();
        $extensions = [];

        foreach (DoctrineConfig::load()->getExtensions($name) as $extName => $extConfig) {
            $class = Archetype::resolve(Extension::class, $extName);
            $extension = $slingshot->newInstance($class, $extConfig);
            $extName = $extension->getName();

            if (!isset($this->extensions['__GLOBAL__'][$extName])) {
                $this->extensions['__GLOBAL__'][$extName] = $extension;
                $extension->loadGlobal();
            }

            $extensions[$extension->getName()] = $extension;
        }

        $this->extensions[$name] = $extensions;
        return $extensions;
    }

    /**
     * Load ORM config for given name
     */
    protected function loadOrmConfig(
        string $name
    ): OrmConfig {
        $config = DoctrineConfig::load();

        // Environment
        if (class_exists(Genesis::class)) {
            $devMode = !Genesis::$environment->isProduction();
            $appPath = Genesis::$hub->getApplicationPath();
        } else {
            $devMode = Dovetail::envString('ENV_MODE', 'production') !== 'production';
            $appPath = dirname(Dovetail::getFinder()->findEnv()?->getPath() ?? '');
        }

        // Paths
        $paths = [];

        foreach ($config->getPaths($name) as $path) {
            $paths[] = $appPath . '/' . $path;
        }


        // Orm Config
        $method = match ($config->getMetadataType($name)) {
            MetadataType::Attributes => 'createAttributeMetadataConfiguration',
            MetadataType::Annotations => 'createAnnotationMetadataConfiguration',
            MetadataType::Xml => 'createXMLMetadataConfiguration'
        };

        $output = ORMSetup::$method(
            paths: $paths,
            isDevMode: $devMode,
            cache: Stash::load(__CLASS__)
        );


        // Schema filter
        $output->setSchemaAssetsFilter(function (
            string|AbstractAsset $asset
        ) use ($name): bool {
            foreach ($this->extensions[$name] ?? [] as $extension) {
                if (null !== ($result = $extension->filterSchemaAsset($asset))) {
                    return $result;
                }
            }

            return true;
        });


        // Extensions
        foreach ($this->extensions[$name] ?? [] as $extension) {
            $extension->loadForOrmConfig($output);
        }

        return $output;
    }


    /**
     * Load connection for given name
     */
    protected function loadConnection(
        string $name,
        OrmConfig $ormConfig
    ): Connection {
        $config = DoctrineConfig::load();

        if (isset($_SERVER['HTTP_HOST'])) {
            // Shared connection for web
            $dsn = $config->getSharedConnection($name);
        } else {
            // Direct connection for cli
            $dsn = $config->getAdminConnection($name);
        }

        $connectionParams = (new DsnParser())->parse($dsn);
        return DriverManager::getConnection($connectionParams, $ormConfig);
    }

    /**
     * Get slingshot instance
     */
    protected function getSlingshot(): Slingshot
    {
        if (!$this->slingshot) {
            $this->slingshot = new Slingshot(
                class_exists(Genesis::class) ? Genesis::$container : null
            );
        }

        return $this->slingshot;
    }


    /**
     * Clear cache
     */
    public function clearCache(): void
    {
        Stash::load(__CLASS__)->clear();
    }



    /**
     * Transaction with RLS
     */
    public function withJwt(
        CipherPayload $payload,
        Closure $callback,
        string|EntityManager|null $entityManager = null
    ): mixed {
        if (!$entityManager instanceof EntityManager) {
            $entityManager = $this->getEntityManager($entityManager);
        }

        return $entityManager->getConnection()->transactional(function () use ($entityManager, $callback, $payload) {
            $jwt = json_encode($payload);
            $jwtKey = 'request.jwt.claims';

            $entityManager->getConnection()->executeStatement(
                <<<SQL
                SELECT set_config('$jwtKey', '$jwt', TRUE)
SQL
            );

            return $callback($entityManager, $payload);
        });
    }


    /**
     * Bypass RLS
     */
    public function bypassJwt(
        Closure $callback,
        string|EntityManager|null $entityManager = null
    ): mixed {
        if (!$entityManager instanceof EntityManager) {
            $entityManager = $this->getEntityManager($entityManager);
        }

        return $entityManager->getConnection()->transactional(function () use ($entityManager, $callback) {
            $bypassKey = 'request.jwt.bypass';

            $entityManager->getConnection()->executeStatement(
                <<<SQL
                SELECT set_config('$bypassKey', 'on', TRUE)
SQL
            );

            return $callback($entityManager);
        });
    }
}

// Register
Veneer::register(
    Context::class,
    Indoctrination::class // @phpstan-ignore-line
);
