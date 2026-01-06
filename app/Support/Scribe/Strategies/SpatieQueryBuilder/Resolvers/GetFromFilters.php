<?php

namespace App\Support\Scribe\Strategies\SpatieQueryBuilder\Resolvers;

use Error;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionException;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Filters\Filter;
use Spatie\QueryBuilder\Filters\FiltersBeginsWithStrict;
use Spatie\QueryBuilder\Filters\FiltersExact;
use Spatie\QueryBuilder\Filters\FiltersPartial;
use Spatie\QueryBuilder\Filters\FiltersTrashed;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\Sorts\Sort;

/**
 * Adds discovery resolver for the "filter" parameter of the query builder.
 *
 * @link https://spatie.be/docs/laravel-query-builder
 * @link https://scribe.knuckles.wtf/laravel
 *
 * @author Luis Arce
 */
class GetFromFilters extends GetFromBase
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(Collection $properties, QueryBuilder $queryBuilder): Collection
    {
        $parameterName = $this->getParameterName();

        $model = $this->getFactoryModelFromQueryBuilder($queryBuilder);

        return $properties
            ->reduce(function (Collection $queryParams, AllowedFilter $allowedFilter) use ($parameterName, $model) {
                $name = sprintf('%s[%s]', $parameterName, $allowedFilter->getName());

                $allowedFilterReflectionClass = new ReflectionClass($allowedFilter);

                $filterClass = $allowedFilterReflectionClass
                    ->getProperty('filterClass')
                    ->getValue($allowedFilter);

                $value = $this->getExampleValue($model, $allowedFilter->getInternalName());

                return $queryParams->put(
                    $name,
                    array_merge([
                        'type' => 'string',
                        'required' => false,
                    ], $this->getDocumentation($filterClass, $value), [
                        'name' => $name,
                    ])
                );
            }, new Collection());
    }

    /**
     * {@inheritDoc}
     *
     * @throws ReflectionException
     */
    protected function getDocumentation(Filter|Sort|Collection|string $param, mixed $value = null): array
    {
        return match (is_string($param) ? $param : $param::class) {
            FiltersPartial::class => [
                'description' => 'Partial Filter. It is evaluated as: The field contains this value.',
                'example' => str($value)->substr(2, 5) ?: str()->words(2)->lower(),
            ],
            FiltersBeginsWithStrict::class => [
                'description' => 'Begin Partial Filter. It is evaluated as: The field starts with this value.',
                'example' => str($value)->substr(0, 3) ?: 'https://',
            ],
            FiltersExact::class => [
                'description' => 'Exact Filter. It is evaluated as: The field is exactly equal to this value.',
                'example' => $value ?: 'laa@teamq.biz',
            ],
            FiltersTrashed::class => [
                'description' => 'Trashed Filter. For soft deleted records. The accepted values are:
                    <ul>
                        <li><b>with:</b> With deleted records.</li>
                        <li><b>only:</b> Only deleted records.</li>
                        <li><b>without:</b> Without deleted records.</li>
                    </ul>',
                'example' => 'only',
            ],
            default => $this->getDefaultDocumentation($param),
        };
    }

    /**
     * {@inheritDoc}
     */
    protected function getParameterName(): string
    {
        return config('query-builder.parameters.filter');
    }

    /**
     * Gets the data factory related to the model that is associated to the query builder class.
     */
    protected function getFactoryModelFromQueryBuilder(QueryBuilder $queryBuilder): Model
    {
        $model = $queryBuilder->getSubject()->getModel();

        if (method_exists($model, 'factory')) {
            return $model::factory()->make();
        }

        return $model;
    }

    /**
     * It allows to generate a value according to the model being queried.
     */
    protected function getExampleValue(Model $model, string $property): mixed
    {
        try {
            $value = $model->getAttribute($property);

            if (is_null($value) && isset($model->getCasts()[$property])) {
                $type = $model->getCasts()[$property];

                $value = match ($type) {
                    'int' => fake()->randomDigit(),
                    'string' => fake()->words(2),
                    'datetime' => fake()->dateTimeBetween('-5 years'),
                };
            }
        } catch (Exception|Error) {
            $value = null;
        }

        return $value;
    }
}
