# Indoctrination — Package Specification

> **Cluster:** `data`
> **Language:** `php`
> **Milestone:** `null`
> **Repo:** `https://github.com/decodelabs/indoctrination`
> **Role:** Doctrine integration

## Overview

### Purpose

Indoctrination provides integration tools for Doctrine DBAL and ORM within the Decode Labs ecosystem. It simplifies Doctrine setup and configuration by providing:

- Service container integration with Kingdom
- Extension system for customizing Doctrine behavior
- Built-in extensions for UUID, Carbon date/time, PostgreSQL features, and schema management
- JWT-based Row Level Security (RLS) support for PostgreSQL
- Multiple entity manager support
- Integration with Dovetail for configuration management
- Commandment integration for CLI tools
- Caching integration with Stash

Indoctrination bridges Doctrine's powerful ORM capabilities with Decode Labs' service architecture, providing a seamless development experience for database-driven applications.

### Non-Goals

- Indoctrination does not provide database abstraction beyond Doctrine
- It does not implement query builders or custom ORM features
- It does not provide migration generation or schema diff tools (uses Doctrine's built-in tools)
- It does not handle database connection pooling (relies on Doctrine's connection management)
- It does not provide entity mapping or annotation processing (uses Doctrine's metadata system)

## Role in the Ecosystem

### Cluster & Positioning

Indoctrination belongs to the **data** cluster, providing database access and ORM capabilities. It sits alongside other data packages like Dovetail (configuration), Stash (caching), and Supermodel (data modeling interfaces).

### Usage Contexts

Indoctrination is used for:

- Entity management and persistence in Decode Labs applications
- Database schema management and migrations
- Complex database queries using Doctrine's Query Builder and DQL
- Integration with PostgreSQL-specific features (arrays, JSONB, custom functions)
- Applications requiring Row Level Security with JWT-based authentication
- Multi-database applications using multiple entity managers

## Public Surface

### Key Types

- **`Indoctrination`** — Main service class implementing `Service`. Manages entity managers, extensions, and provides JWT-based RLS support.

- **`Indoctrination\Config`** — Interface for Doctrine configuration, defining methods for connection strings, entity paths, metadata types, extensions, and migration configuration.

- **`Indoctrination\Extension`** — Interface for Doctrine extensions that can customize ORM configuration, entity manager setup, and schema filtering.

- **`Indoctrination\ExtensionTrait`** — Trait providing default implementations for extension methods.

- **`Indoctrination\MetadataType`** — Enum defining metadata types: `Attributes` and `Xml`.

- **`Indoctrination\Type\Uuid`** — Doctrine DBAL type for UUID values, integrating with Guidance for UUID handling.

- **`Indoctrination\Generator\Uuid`** — Doctrine ID generator for UUID primary keys using Guidance V7 UUIDs.

- **`Indoctrination\Extension\Uuid`** — Extension that registers the UUID type globally.

- **`Indoctrination\Extension\Carbon`** — Extension that overrides Doctrine date/time types with Carbon implementations. Detected at runtime if Carbon is installed.

- **`Indoctrination\Extension\Postgres`** — Extension that registers PostgreSQL-specific types (arrays, JSONB) and custom DQL functions. Requires `martin-georgiev/postgresql-for-doctrine` package.

- **`Indoctrination\Extension\SchemaIgnore`** — Extension that filters schema assets by namespace and allows custom foreign key definitions.

- **`Dovetail\Config\Doctrine`** — Dovetail configuration class implementing `Indoctrination\Config`, providing configuration from Dovetail's config system.

- **`Commandment\Action\Doctrine`** — Commandment action that provides CLI access to Doctrine tools and migrations.

### Main Entry Points

- **`Indoctrination::provideService()`** — Service factory method for Kingdom integration. Creates an `Indoctrination` instance and registers `EntityManager` factory.

- **`Indoctrination::getEntityManager(?string $name)`** — Returns the entity manager for the given name (defaults to 'default'). Lazy-loads and caches entity managers.

- **`Indoctrination::clearCache()`** — Clears Doctrine's metadata cache.

- **`Indoctrination::withJwt(CipherPayload $payload, Closure $callback, string|EntityManager|null $entityManager)`** — Executes a callback within a transaction with JWT-based RLS enabled for PostgreSQL.

- **`Indoctrination::bypassJwt(Closure $callback, string|EntityManager|null $entityManager)`** — Executes a callback within a transaction with JWT RLS bypass enabled for PostgreSQL.

## Dependencies

### Decode Labs

- **`coercion`** — Used for type coercion in configuration and CLI handling.

- **`dovetail`** — Used for configuration management via `Dovetail\Config\Doctrine`.

- **`exceptional`** — Used for exception handling throughout the package.

- **`guidance`** — Used for UUID generation and parsing in UUID type and generator.

- **`kingdom`** — Used for service container integration via `Service` interface.

- **`monarch`** — Used to determine application paths, development mode, and build information for cache management.

- **`slingshot`** — Used to instantiate extension classes with dependency injection.

- **`stash`** — Used for Doctrine metadata caching.

### External

- **`doctrine/orm`** — Doctrine ORM for entity management and persistence.

- **`doctrine/dbal`** — Doctrine DBAL for database abstraction and connection management.

- **`doctrine/migrations`** — Doctrine Migrations for database schema versioning.

## Behaviour & Contracts

### Invariants

- Entity managers are lazy-loaded and cached per name
- Extensions are loaded globally once, then per entity manager
- Shared connections are used for web requests, admin connections for CLI
- Metadata cache is stored in Stash using the class name as the key
- Proxy directory is set to `{localData}/doctrine/proxies`
- Schema assets are filtered by extensions before inclusion
- JWT RLS methods require transactions and PostgreSQL

### Input & Output Contracts

- **`getEntityManager(?string $name): EntityManager`** — Returns an entity manager instance. Creates and caches if not already loaded. Default name is 'default'.

- **`clearCache(): void`** — Clears the Doctrine metadata cache stored in Stash.

- **`withJwt(CipherPayload $payload, Closure $callback, string|EntityManager|null $entityManager): mixed`** — Executes callback within a transaction, setting PostgreSQL session variable `request.jwt.claims` to JSON-encoded payload. Returns callback result.

- **`bypassJwt(Closure $callback, string|EntityManager|null $entityManager): mixed`** — Executes callback within a transaction, setting PostgreSQL session variable `request.jwt.bypass` to 'on'. Returns callback result.

- **`Config::getSharedConnection(?string $name): string`** — Returns DSN string for poolable connection (web requests).

- **`Config::getAdminConnection(?string $name): string`** — Returns DSN string for direct connection (CLI).

- **`Config::getPaths(?string $name): array<string>`** — Returns array of entity mapping paths.

- **`Config::getMetadataType(?string $name): MetadataType`** — Returns metadata type enum value.

- **`Config::getExtensions(?string $name): array<string, array<mixed>>`** — Returns extension configuration array.

- **`Config::getMigrationsConfig(?string $name): array<string, mixed>`** — Returns migration configuration array.

- **`Extension::loadGlobal(): void`** — Called once globally to register types or perform global setup.

- **`Extension::loadForOrmConfig(OrmConfig $ormConfig): void`** — Called per entity manager to customize ORM configuration.

- **`Extension::loadForEntityManager(EntityManager $entityManager): void`** — Called per entity manager to customize entity manager setup.

- **`Extension::filterSchemaAsset(string|AbstractAsset $asset): ?bool`** — Returns true to include asset, false to exclude, null to use default behavior.

## Error Handling

Indoctrination uses the Exceptional pattern for error handling. Key exception types:

- **`ComponentUnavailable`** — Thrown when required optional dependencies (Carbon, PostgreSQL extension package) are not available.

- **`InvalidType`** — Thrown when UUID type conversion receives invalid input types.

- **`Setup`** — Thrown when schema operations are attempted before schema is captured.

Exceptions preserve the original service context and include detailed error messages.

## Configuration & Extensibility

### Extension Points

- **Custom Extensions** — Implement `Extension` interface to add custom Doctrine type registrations, ORM configuration, entity manager setup, or schema filtering.

- **Configuration** — Implement `Indoctrination\Config` interface or use `Dovetail\Config\Doctrine` to provide connection strings, paths, metadata types, and extension configuration.

- **Custom Types** — Register custom Doctrine DBAL types via extensions.

- **Custom Generators** — Use custom ID generators in entity mappings.

### Configuration

- **Connection Management** — Separate shared (poolable) and admin (direct) connections for web and CLI contexts.

- **Metadata Type** — Choose between Attributes or XML for entity mapping metadata.

- **Extension Configuration** — Configure extensions per entity manager with optional parameters.

- **Schema Filtering** — Use `SchemaIgnore` extension to filter schema assets by namespace and define custom foreign keys.

- **Caching** — Metadata cache is automatically managed via Stash, with cache directory based on Monarch paths.

## Interactions with Other Packages

- **Dovetail** — Provides configuration via `Dovetail\Config\Doctrine` class.

- **Stash** — Used for Doctrine metadata caching.

- **Monarch** — Used to determine application paths, development mode, and build information.

- **Slingshot** — Used to instantiate extension classes with dependency injection.

- **Guidance** — Used for UUID generation and parsing in UUID type and generator.

- **Kingdom** — Integrated as a service, allowing automatic resolution from the service container.

- **Commandment** — Integrated via `Commandment\Action\Doctrine` for CLI access to Doctrine tools.

- **Cipher** — Detected at runtime if installed, used for JWT payload handling in `withJwt()` method.

## Usage Examples

### Basic Setup

```php
use DecodeLabs\Indoctrination;
use DecodeLabs\Dovetail\Config\Doctrine as DoctrineConfig;
use DecodeLabs\Stash;

$config = $dovetail->load(DoctrineConfig::class);
$stash = new Stash();

$indoctrination = new Indoctrination($config, $stash);
$entityManager = $indoctrination->getEntityManager();
```

### Using Entity Manager

```php
use Doctrine\ORM\EntityManager;

$entityManager = $indoctrination->getEntityManager();

// Find entity
$user = $entityManager->find(User::class, $userId);

// Persist entity
$user = new User('John Doe');
$entityManager->persist($user);
$entityManager->flush();
```

### Multiple Entity Managers

```php
// Get default entity manager
$defaultEm = $indoctrination->getEntityManager();

// Get named entity manager
$analyticsEm = $indoctrination->getEntityManager('analytics');
```

### JWT-Based Row Level Security

```php
use DecodeLabs\Cipher\Payload;

$payload = new Payload([
    'userId' => 'user-123',
    'role' => 'admin'
]);

$result = $indoctrination->withJwt(
    payload: $payload,
    callback: function(EntityManager $em, Payload $payload) {
        // PostgreSQL RLS policies can access JWT claims via
        // current_setting('request.jwt.claims')
        $users = $em->getRepository(User::class)->findAll();
        return $users;
    }
);
```

### Bypassing RLS

```php
$result = $indoctrination->bypassJwt(
    callback: function(EntityManager $em) {
        // RLS is bypassed for this transaction
        $users = $em->getRepository(User::class)->findAll();
        return $users;
    }
);
```

### Custom Extension

```php
use DecodeLabs\Indoctrination\Extension;
use DecodeLabs\Indoctrination\ExtensionTrait;
use Doctrine\ORM\Configuration as OrmConfig;
use Doctrine\ORM\EntityManager;

class MyExtension implements Extension
{
    use ExtensionTrait;

    public function loadGlobal(): void
    {
        // Register custom types globally
    }

    public function loadForOrmConfig(OrmConfig $ormConfig): void
    {
        // Customize ORM configuration
    }

    public function loadForEntityManager(EntityManager $entityManager): void
    {
        // Customize entity manager
    }

    public function filterSchemaAsset(string|AbstractAsset $asset): ?bool
    {
        // Filter schema assets
        return null; // Use default behavior
    }
}
```

### Configuration via Dovetail

```php
// In Dovetail configuration
'doctrine' => [
    'default' => [
        'sharedConnection' => 'postgresql://user:pass@localhost/db',
        'adminConnection' => 'postgresql://admin:pass@localhost/db',
        'paths' => ['src/Entity'],
        'metadata' => 'attributes',
        'extensions' => [
            'uuid' => [],
            'carbon' => [],
            'postgres' => [],
            'schemaIgnore' => [
                'namespaces' => ['audit' => false],
                'foreignKeys' => []
            ]
        ],
        'migrations' => [
            'migrations_paths' => ['src/Migration' => 'App\\Migration']
        ]
    ]
]
```

## Implementation Notes (for Contributors)

### Architecture

- **Service Integration** — `Indoctrination` implements `Service` for Kingdom integration, automatically registering `EntityManager` factory.

- **Extension System** — Extensions are loaded in three phases: global (once), ORM config (per entity manager), and entity manager (per entity manager). Extensions can filter schema assets during schema generation.

- **Connection Management** — Separate connection strings for web (shared/poolable) and CLI (admin/direct) contexts, determined by `HTTP_HOST` server variable.

- **Metadata Caching** — Doctrine metadata is cached in Stash using the class name as the key. Cache is cleared via `clearCache()` method.

- **UUID Integration** — UUID type and generator integrate with Guidance package, supporting both native GUID types and binary storage.

- **PostgreSQL Features** — Postgres extension registers array types, JSONB types, and custom DQL functions from `martin-georgiev/postgresql-for-doctrine` package.

- **JWT RLS** — JWT-based RLS uses PostgreSQL session variables (`request.jwt.claims` and `request.jwt.bypass`) set within transactions. Requires Cipher package for JWT payload handling.

- **Schema Filtering** — `SchemaIgnore` extension filters schema assets by namespace and allows custom foreign key definitions during schema generation.

### Performance Considerations

- Entity managers are cached per name to avoid repeated initialization
- Metadata cache significantly improves performance by avoiding repeated reflection
- Extension loading is optimized to load global extensions only once

### Design Decisions

- **Multiple Entity Managers** — Support for multiple entity managers allows applications to work with multiple databases or separate concerns.

- **Extension System** — Pluggable extension system allows customization without modifying core code.

- **Connection Separation** — Separate shared and admin connections allow connection pooling for web requests while maintaining direct connections for CLI operations.

- **JWT RLS Support** — Built-in support for PostgreSQL RLS with JWT authentication enables secure multi-tenant applications.

- **Dovetail Integration** — Configuration via Dovetail provides consistent configuration management across the ecosystem.

## Testing & Quality

**Code Quality:** 2/5 — Early development stage. Core functionality is implemented but may require refinement.

**README Quality:** 2/5 — Minimal documentation. Usage examples are not yet provided.

**Documentation:** 0/5 — No formal documentation beyond README.

**Tests:** 0/5 — No test suite currently.

See `composer.json` for supported PHP versions.

## Roadmap & Future Ideas

- Enhanced documentation and usage examples
- Test suite implementation
- Additional extension examples
- Performance optimizations
- Support for additional database platforms
- Enhanced migration tooling integration
- Query result caching integration
- Event listener system integration

## References

- [Doctrine ORM Documentation](https://www.doctrine-project.org/projects/doctrine-orm/en/latest/)
- [Doctrine DBAL Documentation](https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/)
- [Doctrine Migrations Documentation](https://www.doctrine-project.org/projects/doctrine-migrations/en/latest/)
- [Decode Labs Chorus](https://github.com/decodelabs/chorus)
- [Indoctrination Repository](https://github.com/decodelabs/indoctrination)

