<?php

namespace App\Support\Scribe\Strategies\SpatieQueryBuilder\Resolvers;

use Illuminate\Support\Collection;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\Filters\Filter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\Sorts\Sort;

/**
 * Adds discovery resolver for the "sort" parameter of the query builder.
 *
 * @link https://spatie.be/docs/laravel-query-builder
 * @link https://scribe.knuckles.wtf/laravel
 *
 * @author Luis Arce
 */
class GetFromSorts extends GetFromBase
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(Collection $properties, QueryBuilder $queryBuilder): Collection
    {
        $allowedSorts = $properties->map(fn (AllowedSort $allowedSort) => $allowedSort->getName());

        $parameterName = $this->getParameterName();

        return collect()->put(
            $parameterName,
            array_merge([
                'type' => 'string',
                'required' => false,
            ], $this->getDocumentation($allowedSorts), [
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
            'description' => "Each value separated by a comma. To indicate descending order, add \"-\" before the field to sort. <div>Allowed values: {$values}</div>",
            'example' => $param->random(3)
                ->map(fn ($value, $key) => $key % 2 == 0 ? '-'.$value : $value)
                ->join(','),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getParameterName(): string
    {
        return config('query-builder.parameters.sort');
    }
}
