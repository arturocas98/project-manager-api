<?php

namespace App\Support\Scribe\Strategies\Middlewares;

use Illuminate\Support\Collection;
use Knuckles\Camel\Extraction\ExtractedEndpointData;
use Knuckles\Scribe\Extracting\ParamHelpers;
use Knuckles\Scribe\Extracting\Strategies\Strategy;

/**
 * Adds permission metadata to the endpoint, making use of Authorize middleware.
 *
 * @link https://scribe.knuckles.wtf/laravel
 *
 * @author Luis Arce
 */
class GetFromAuthorizeMiddlewareStrategy extends Strategy
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
        $action = $endpointData->route->getAction();

        if (! is_array($action) || ! isset($action['middleware'])) {
            return [];
        }

        $permissions = $this->getPermissionsFromAuthorizeMiddleware($action['middleware']);

        if ($permissions->isEmpty()) {
            return [];
        }

        $endpointData->metadata->description .= "<aside class='notice'>This endpoint requires the user to be assigned the following permissions: {$permissions->join(', ')}</aside>";

        return $endpointData->metadata->toArray();
    }

    /**
     * Filters and extracts permissions from Authorize middleware.
     */
    protected function getPermissionsFromAuthorizeMiddleware(array|string $middlewares): Collection
    {
        if (! is_array($middlewares)) {
            return new Collection();
        }

        return collect($middlewares)
            ->filter(fn ($middleware) => str($middleware)->startsWith('can'))
            ->map(function ($permission) {
                $permission = str($permission)
                    ->replace('can:', '')
                    ->replace(',', ':')
                    ->toString();

                return "<span style='font-weight: bolder; font-style: italic'>$permission</span>";
            });
    }
}
