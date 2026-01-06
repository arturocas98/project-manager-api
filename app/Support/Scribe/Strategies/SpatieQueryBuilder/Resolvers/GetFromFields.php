<?php

namespace App\Support\Scribe\Strategies\SpatieQueryBuilder\Resolvers;

use Illuminate\Support\Collection;
use Spatie\QueryBuilder\Filters\Filter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\Sorts\Sort;
use Str;

/**
 * Adds discovery resolver for the "fields" parameter of the query builder.
 *
 * @link https://spatie.be/docs/laravel-query-builder
 * @link https://scribe.knuckles.wtf/laravel
 *
 * @author Luis Arce
 */
class GetFromFields extends GetFromBase
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(Collection $properties, QueryBuilder $queryBuilder): Collection
    {
        $parameterName = $this->getParameterName();

        return $properties->groupBy(fn ($field) => Str::of($field)->beforeLast('.')->toString())
            ->reduce(function (Collection $queryParams, Collection $fields, string $key) use ($parameterName) {
                $name = sprintf('%s[%s]', $parameterName, $key);

                $param = $fields
                    ->map(fn ($field) => Str::of($field)->afterLast('.')->toString());

                return $queryParams->put(
                    $name,
                    array_merge([
                        'type' => 'string',
                        'required' => false,
                    ], $this->getDocumentation($param), [
                        'name' => $name,
                    ])
                );
            }, new Collection());
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
        return config('query-builder.parameters.fields');
    }
}
