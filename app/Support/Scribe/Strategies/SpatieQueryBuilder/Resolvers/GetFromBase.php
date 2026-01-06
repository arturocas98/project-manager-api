<?php

namespace App\Support\Scribe\Strategies\SpatieQueryBuilder\Resolvers;

use Illuminate\Support\Collection;
use Knuckles\Scribe\Attributes\QueryParam;
use ReflectionClass;
use ReflectionException;
use Spatie\QueryBuilder\Filters\Filter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\Sorts\Sort;

/**
 * Add `knuckleswtf/scribe` discovery strategy for HTTP parameters of `spatie/laravel-query-builder` package.kage.
 *
 * @link https://spatie.be/docs/laravel-query-builder
 * @link https://scribe.knuckles.wtf/laravel
 *
 * @author Luis Arce
 */
abstract class GetFromBase
{
    /**
     * Get documentation for spatie/laravel-query-builder parameters.
     *
     * @throws ReflectionException
     */
    abstract public function __invoke(Collection $properties, QueryBuilder $queryBuilder): Collection;

    /**
     * Represents the name of the field with which it travels in an HTTP request.
     */
    abstract protected function getParameterName(): string;

    /**
     * Gets the documentation for the query params of the Spatie QueryBuilder package.
     *
     * @return array|string[]
     *
     * @throws ReflectionException
     */
    abstract protected function getDocumentation(Filter|Sort|Collection|string $param, mixed $value = null): array;

    /**
     * Gets the documentation of the custom classes. If the attribute, QueryParam exists,
     * the documentation for that attribute is extracted, otherwise the getDocumentation
     * method is fetched.
     *
     * @throws ReflectionException
     */
    protected function getDefaultDocumentation(Filter|Sort|string $param): array
    {
        $reflectionClass = new ReflectionClass($param);

        $documentation = $reflectionClass->getAttributes(QueryParam::class);

        if (! empty($documentation)) {
            $documentation = $documentation[0]->newInstance();

            return $documentation->toArray();
        }

        if (method_exists($param, 'docs')) {
            return $param::docs()->toArray();
        }

        return [];
    }
}
