<?php

namespace App\Support\Scribe\Strategies\SpatieQueryBuilder;

use Knuckles\Camel\Extraction\ExtractedEndpointData;
use Knuckles\Scribe\Extracting\ParamHelpers;
use ReflectionAttribute;

/**
 * Adds the possibility of documenting the API from a class that extends `Spatie\QueryBuilder\QueryBuilder`
 * and has `Knuckles\Scribe\Attributes\QueryParam` attributes defined.
 *
 * @link https://spatie.be/docs/laravel-query-builder
 * @link https://scribe.knuckles.wtf/laravel
 *
 * @author Luis Arce
 */
class GetFromSpatieQueryBuilderParamAttribute extends GetFromSpatieQueryBuilder
{
    /**
     * Trait containing some helper methods for dealing with "parameters",
     * such as generating examples and casting values to types.
     * Useful if your strategy extracts information about parameters or generates examples.
     */
    use ParamHelpers;

    /**
     * @link https://scribe.knuckles.wtf/laravel/advanced/plugins
     *
     * @param  ExtractedEndpointData  $endpointData  The endpoint we are currently processing.
     *                                               Contains details about httpMethods, controller, method, route, url, etc, as well as already extracted data.
     * @param  array  $routeRules  Array of rules for the ruleset which this route belongs to.
     *
     * See the documentation linked above for more details about writing custom strategies.
     */
    public function __invoke(ExtractedEndpointData $endpointData, array $routeRules = []): ?array
    {
        if (! $queryBuilderReflectionClass = $this->getQueryBuilderReflectionClass($endpointData->method)) {
            return [];
        }

        return collect($queryBuilderReflectionClass->getAttributes())
            ->mapWithKeys(function (ReflectionAttribute $attribute) {
                $arguments = $attribute->getArguments();

                return [
                    $arguments['name'] => [
                        'type' => $arguments['type'] ?? 'string',
                        'description' => $arguments['description'] ?? null,
                        'required' => $arguments['required'] ?? false,
                        'example' => $arguments['example'] ?? null,
                    ],
                ];
            })
            ->toArray();
    }
}
