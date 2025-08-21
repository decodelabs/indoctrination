<?php

/**
 * @package Indoctrination
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs;

use Closure;
use DecodeLabs\Cipher\Payload as CipherPayload;
use DecodeLabs\Dovetail\Config\Doctrine as DoctrineConfig;
use DecodeLabs\Indoctrination\Extension;
use DecodeLabs\Indoctrination\MetadataType;
use DecodeLabs\Kingdom\ContainerAdapter;
use DecodeLabs\Kingdom\Service;
use DecodeLabs\Kingdom\ServiceTrait;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\AbstractAsset;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\ORM\Configuration as OrmConfig;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

class Indoctrination implements Service
{
    use ServiceTrait;

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

    public static function provideService(
        ContainerAdapter $container
    ): static {
        $output = $container->getOrCreate(static::class);

        if (!$container->has(EntityManager::class)) {
            $container->setFactory(
                EntityManager::class,
                fn () => $output->getEntityManager()
            );
        }

        return $output;
    }

    public function __construct(
        protected DoctrineConfig $config,
        protected Stash $stash,
        ?EntityManager $entityManager = null
    ) {
        if ($entityManager) {
            $this->entityManagers['default'] = $entityManager;
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

        foreach ($this->config->getExtensions($name) as $extName => $extConfig) {
            /** @var array<string,mixed> $extConfig */
            $extension = $slingshot->resolveNamedInstance(
                interface: Extension::class,
                name: $extName,
                parameters: $extConfig
            );

            $extName = $extension->name;

            if (!isset($this->extensions['__GLOBAL__'][$extName])) {
                $this->extensions['__GLOBAL__'][$extName] = $extension;
                $extension->loadGlobal();
            }

            $extensions[$extension->name] = $extension;
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
        // Environment
        $appPath = Monarch::getPaths()->run;
        $devMode = !Monarch::isProduction();

        // Paths
        $paths = [];

        foreach ($this->config->getPaths($name) as $path) {
            $paths[] = $appPath . '/' . $path;
        }

        // Orm Config
        $output = match ($this->config->getMetadataType($name)) {
            MetadataType::Attributes => ORMSetup::createAttributeMetadataConfiguration(
                paths: $paths,
                isDevMode: $devMode,
                cache: $this->stash->load(__CLASS__)
            ),
            MetadataType::Xml => ORMSetup::createXMLMetadataConfiguration(
                paths: $paths,
                isDevMode: $devMode,
                cache: $this->stash->load(__CLASS__)
            )
        };

        $output->setProxyDir(Monarch::getPaths()->localData . '/doctrine/proxies');


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
        if (isset($_SERVER['HTTP_HOST'])) {
            // Shared connection for web
            $dsn = $this->config->getSharedConnection($name);
        } else {
            // Direct connection for cli
            $dsn = $this->config->getAdminConnection($name);
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
            $this->slingshot = new Slingshot();
        }

        return $this->slingshot;
    }


    /**
     * Clear cache
     */
    public function clearCache(): void
    {
        $this->stash->load(__CLASS__)->clear();
    }



    /**
     * Transaction with RLS
     *
     * @template TReturn
     * @template TPayload of CipherPayload
     * @param TPayload $payload
     * @param Closure(EntityManager, TPayload):TReturn $callback
     * @return TReturn
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
     *
     * @template TReturn
     * @param Closure(EntityManager):TReturn $callback
     * @return TReturn
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
