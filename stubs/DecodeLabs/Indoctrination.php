<?php
/**
 * This is a stub file for IDE compatibility only.
 * It should not be included in your projects.
 */
namespace DecodeLabs;

use DecodeLabs\Veneer\Proxy as Proxy;
use DecodeLabs\Veneer\ProxyTrait as ProxyTrait;
use DecodeLabs\Indoctrination\Context as Inst;
use Doctrine\ORM\EntityManager as Ref0;
use DecodeLabs\Cipher\Payload as Ref1;
use Closure as Ref2;

class Indoctrination implements Proxy
{
    use ProxyTrait;

    public const Veneer = 'DecodeLabs\\Indoctrination';
    public const VeneerTarget = Inst::class;

    protected static Inst $_veneerInstance;

    public static function init(): void {}
    public static function getEntityManager(?string $name = NULL): Ref0 {
        return static::$_veneerInstance->getEntityManager(...func_get_args());
    }
    public static function clearCache(): void {}
    public static function withJwt(Ref1 $payload, Ref2 $callback, Ref0|string|null $entityManager = NULL): mixed {
        return static::$_veneerInstance->withJwt(...func_get_args());
    }
    public static function bypassJwt(Ref2 $callback, Ref0|string|null $entityManager = NULL): mixed {
        return static::$_veneerInstance->bypassJwt(...func_get_args());
    }
};
