<?php

namespace App\Support\Scribe\Strategies\SpatieQueryBuilder\Resolvers;

use Illuminate\Support\Collection;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\Filters\Filter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\Sorts\Sort;

/**
 * Adds discovery resolver for the "include" parameter of the query builder.
 *
 * @link https://spatie.be/docs/laravel-query-builder
 * @link https://scribe.knuckles.wtf/laravel
 *
 * @author Luis Arce
 */
class GetFromIncludes extends GetFromBase
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(Collection $properties, QueryBuilder $queryBuilder): Collection
    {
        $allowedIncludes = $properties->map(fn (AllowedInclude $allowedInclude) => $allowedInclude->getName());

        $parameterName = $this->getParameterName();

        return collect()->put(
            $parameterName,
            array_merge([
                'type' => 'string',
                'required' => false,
            ], $this->getDocumentation($allowedIncludes), [
                'name' => $parameterName,
            ])
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getDocumentation(Filter|Sort|Collection|string $param, mixed $value = null): array
    {
        $values = $param
            ->map(fn ($value) => "<span style='font-weight: bolder; font-style: italic'>$value</span>")
            ->join(', ');

        return [
            'description' => "Each value separated by a comma.<div>Allowed values: {$values}</div>",
            'example' => $param->shuffle()->take(2)->join(','),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getParameterName(): string
    {
        return config('query-builder.parameters.include');
    }
}
