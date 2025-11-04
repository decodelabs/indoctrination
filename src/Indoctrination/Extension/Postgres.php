<?php

/**
 * Indoctrination
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Indoctrination\Extension;

use DecodeLabs\Exceptional;
use DecodeLabs\Indoctrination\Extension;
use DecodeLabs\Indoctrination\ExtensionTrait;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration as OrmConfig;
use Doctrine\ORM\EntityManager;
use MartinGeorgiev\Doctrine\DBAL\Types\BigIntArray;
use MartinGeorgiev\Doctrine\DBAL\Types\BooleanArray;
use MartinGeorgiev\Doctrine\DBAL\Types\IntegerArray;
use MartinGeorgiev\Doctrine\DBAL\Types\Jsonb;
use MartinGeorgiev\Doctrine\DBAL\Types\JsonbArray;
use MartinGeorgiev\Doctrine\DBAL\Types\SmallIntArray;
use MartinGeorgiev\Doctrine\DBAL\Types\TextArray;
use MartinGeorgiev\Doctrine\ORM\Query\AST\Functions;

class Postgres implements Extension
{
    use ExtensionTrait;

    /**
     * Set global type mappings
     */
    public function loadGlobal(): void
    {
        if (!class_exists(BooleanArray::class)) {
            throw Exceptional::ComponentUnavailable(
                message: 'Postgres extension requires the martin-georgiev/postgresql-for-doctrine package'
            );
        }

        Type::addType('bool[]', BooleanArray::class);
        Type::addType('smallint[]', SmallIntArray::class);
        Type::addType('integer[]', IntegerArray::class);
        Type::addType('bigint[]', BigIntArray::class);
        Type::addType('text[]', TextArray::class);
        Type::addType('jsonb', Jsonb::class);
        Type::addType('jsonb[]', JsonbArray::class);
    }


    public function loadForOrmConfig(
        OrmConfig $ormConfig
    ): void {
        $ormConfig->addCustomStringFunction('CONTAINS', Functions\Contains::class);
        $ormConfig->addCustomStringFunction('IS_CONTAINED_BY', Functions\IsContainedBy::class);
        $ormConfig->addCustomStringFunction('OVERLAPS', Functions\Overlaps::class);
        $ormConfig->addCustomStringFunction('JSON_GET_FIELD', Functions\JsonGetField::class);
        $ormConfig->addCustomStringFunction('JSON_GET_FIELD_AS_TEXT', Functions\JsonGetFieldAsText::class);
        $ormConfig->addCustomStringFunction('JSON_GET_OBJECT', Functions\JsonGetObject::class);
        $ormConfig->addCustomStringFunction('JSON_GET_OBJECT_AS_TEXT', Functions\JsonGetObjectAsText::class);
        $ormConfig->addCustomStringFunction('ILIKE', Functions\Ilike::class);
        $ormConfig->addCustomStringFunction('SIMILAR_TO', Functions\SimilarTo::class);
        $ormConfig->addCustomStringFunction('NOT_SIMILAR_TO', Functions\NotSimilarTo::class);
        $ormConfig->addCustomStringFunction('REGEXP', Functions\Regexp::class);
        $ormConfig->addCustomStringFunction('IREGEXP', Functions\IRegexp::class);
        $ormConfig->addCustomStringFunction('NOT_REGEXP', Functions\NotRegexp::class);
        $ormConfig->addCustomStringFunction('NOT_IREGEXP', Functions\NotIRegexp::class);
        $ormConfig->addCustomStringFunction('TSMATCH', Functions\Tsmatch::class);

        $ormConfig->addCustomStringFunction('ALL_OF', Functions\All::class);
        $ormConfig->addCustomStringFunction('ANY_OF', Functions\Any::class);
        $ormConfig->addCustomStringFunction('ARRAY_AGG', Functions\ArrayAgg::class);
        $ormConfig->addCustomStringFunction('ARRAY_APPEND', Functions\ArrayAppend::class);
        $ormConfig->addCustomStringFunction('ARRAY_CAT', Functions\ArrayCat::class);
        $ormConfig->addCustomStringFunction('ARRAY_DIMENSIONS', Functions\ArrayDimensions::class);
        $ormConfig->addCustomStringFunction('ARRAY_LENGTH', Functions\ArrayLength::class);
        $ormConfig->addCustomStringFunction('ARRAY_NUMBER_OF_DIMENSIONS', Functions\ArrayNumberOfDimensions::class);
        $ormConfig->addCustomStringFunction('ARRAY_PREPEND', Functions\ArrayPrepend::class);
        $ormConfig->addCustomStringFunction('ARRAY_REMOVE', Functions\ArrayRemove::class);
        $ormConfig->addCustomStringFunction('ARRAY_REPLACE', Functions\ArrayReplace::class);
        $ormConfig->addCustomStringFunction('ARRAY_TO_JSON', Functions\ArrayToJson::class);
        $ormConfig->addCustomStringFunction('ARRAY_TO_STRING', Functions\ArrayToString::class);
        $ormConfig->addCustomStringFunction('ARRAY_CARDINALITY', Functions\ArrayCardinality::class);
        $ormConfig->addCustomStringFunction('CAST', Functions\Cast::class);
        $ormConfig->addCustomStringFunction('DATE_EXTRACT', Functions\DateExtract::class);
        $ormConfig->addCustomStringFunction('GREATEST', Functions\Greatest::class);
        $ormConfig->addCustomStringFunction('JSON_AGG', Functions\JsonAgg::class);
        $ormConfig->addCustomStringFunction('JSON_ARRAY_LENGTH', Functions\JsonArrayLength::class);
        $ormConfig->addCustomStringFunction('JSON_EACH', Functions\JsonEach::class);
        $ormConfig->addCustomStringFunction('JSON_EACH_TEXT', Functions\JsonEachText::class);
        $ormConfig->addCustomStringFunction('JSON_OBJECT_AGG', Functions\JsonObjectAgg::class);
        $ormConfig->addCustomStringFunction('JSON_OBJECT_KEYS', Functions\JsonObjectKeys::class);
        $ormConfig->addCustomStringFunction('JSON_STRIP_NULLS', Functions\JsonStripNulls::class);
        $ormConfig->addCustomStringFunction('JSON_TYPEOF', Functions\JsonTypeof::class);
        $ormConfig->addCustomStringFunction('JSONB_ARRAY_ELEMENTS', Functions\JsonbArrayElements::class);
        $ormConfig->addCustomStringFunction('JSONB_AGG', Functions\JsonbAgg::class);
        $ormConfig->addCustomStringFunction('JSONB_ARRAY_ELEMENTS_TEXT', Functions\JsonbArrayElementsText::class);
        $ormConfig->addCustomStringFunction('JSONB_ARRAY_LENGTH', Functions\JsonbArrayLength::class);
        $ormConfig->addCustomStringFunction('JSONB_EACH', Functions\JsonbEach::class);
        $ormConfig->addCustomStringFunction('JSONB_EACH_TEXT', Functions\JsonbEachText::class);
        $ormConfig->addCustomStringFunction('JSONB_EXISTS', Functions\JsonbExists::class);
        $ormConfig->addCustomStringFunction('JSONB_INSERT', Functions\JsonbInsert::class);
        $ormConfig->addCustomStringFunction('JSONB_OBJECT_AGG', Functions\JsonbObjectAgg::class);
        $ormConfig->addCustomStringFunction('JSONB_OBJECT_KEYS', Functions\JsonbObjectKeys::class);
        $ormConfig->addCustomStringFunction('JSONB_PRETTY', Functions\JsonbPretty::class);
        $ormConfig->addCustomStringFunction('JSONB_SET', Functions\JsonbSet::class);
        $ormConfig->addCustomStringFunction('JSONB_SET_LAX', Functions\JsonbSetLax::class);
        $ormConfig->addCustomStringFunction('JSONB_STRIP_NULLS', Functions\JsonbStripNulls::class);
        $ormConfig->addCustomStringFunction('LEAST', Functions\Least::class);
        $ormConfig->addCustomStringFunction('DATE_OVERLAPS', Functions\DateOverlaps::class);
        $ormConfig->addCustomStringFunction('FLAGGED_REGEXP_LIKE', Functions\FlaggedRegexpLike::class);
        $ormConfig->addCustomStringFunction('REGEXP_LIKE', Functions\RegexpLike::class);
        $ormConfig->addCustomStringFunction('FLAGGED_REGEXP_MATCH', Functions\FlaggedRegexpMatch::class);
        $ormConfig->addCustomStringFunction('REGEXP_MATCH', Functions\RegexpMatch::class);
        $ormConfig->addCustomStringFunction('ROW_TO_JSON', Functions\RowToJson::class);
        $ormConfig->addCustomStringFunction('STRING_AGG', Functions\StringAgg::class);
        $ormConfig->addCustomStringFunction('STRING_TO_ARRAY', Functions\StringToArray::class);
        $ormConfig->addCustomStringFunction('TO_JSON', Functions\ToJson::class);
        $ormConfig->addCustomStringFunction('TO_JSONB', Functions\ToJsonb::class);
        $ormConfig->addCustomStringFunction('TO_TSQUERY', Functions\ToTsquery::class);
        $ormConfig->addCustomStringFunction('TO_TSVECTOR', Functions\ToTsvector::class);
        $ormConfig->addCustomStringFunction('UNACCENT', Functions\Unaccent::class);
        $ormConfig->addCustomStringFunction('UNNEST', Functions\Unnest::class);

        $ormConfig->addCustomStringFunction('ARRAY', Functions\Arr::class);
        $ormConfig->addCustomStringFunction('IN_ARRAY', Functions\InArray::class);
        $ormConfig->addCustomStringFunction('JSON_GET_FIELD_AS_INTEGER', Functions\JsonGetFieldAsInteger::class);
    }


    /**
     * Set type mappings for ORM
     */
    public function loadForEntityManager(
        EntityManager $entityManager
    ): void {
        $platform = $entityManager->getConnection()->getDatabasePlatform();

        if (!$platform instanceof PostgreSQLPlatform) {
            return;
        }

        $platform->registerDoctrineTypeMapping('bool[]', 'bool[]');
        $platform->registerDoctrineTypeMapping('_bool', 'bool[]');
        $platform->registerDoctrineTypeMapping('smallint[]', 'smallint[]');
        $platform->registerDoctrineTypeMapping('_int2', 'smallint[]');
        $platform->registerDoctrineTypeMapping('integer[]', 'integer[]');
        $platform->registerDoctrineTypeMapping('_int4', 'integer[]');
        $platform->registerDoctrineTypeMapping('bigint[]', 'bigint[]');
        $platform->registerDoctrineTypeMapping('_int8', 'bigint[]');
        $platform->registerDoctrineTypeMapping('text[]', 'text[]');
        $platform->registerDoctrineTypeMapping('_text', 'text[]');
        $platform->registerDoctrineTypeMapping('jsonb', 'jsonb');
        $platform->registerDoctrineTypeMapping('jsonb[]', 'jsonb[]');
        $platform->registerDoctrineTypeMapping('_jsonb', 'jsonb[]');
    }
}
