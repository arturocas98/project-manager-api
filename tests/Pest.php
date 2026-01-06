<?php

use App\Models\User;
use Database\Seeders\Testing\PassportClientSeeder;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticated;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(TestCase::class)->in('Feature');

uses()->group('features', 'api')
    ->beforeEach(function () {
        $this->seed(PassportClientSeeder::class);
    })
    ->in('Feature/Api');

uses()->group('features', 'web')
    ->in('Feature/Web', 'Feature/Jetstream');

uses()->group('unit')
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

// expect()->extend('toBeOne', function () {
//     return $this->toBe(1);
// });

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Helper to create users with their attributes, roles and permissions.
 */
function user(array $attributes = [], array $roles = [], array $permissions = [], ?callable $callback = null): User
{
    $factory = $callback !== null
        ? $callback(User::factory())
        : User::factory();

    $permissions = collect($permissions)
        ->map(fn ($name, $guard) => Permission::findByName($name, is_string($guard) ? $guard : 'api'))
        ->pluck('id')
        ->toArray();

    $roles = collect($roles)
        ->map(fn ($name, $guard) => Role::findByName($name, is_string($guard) ? $guard : 'api'))
        ->pluck('id')
        ->toArray();

    return $factory
        ->create($attributes)
        ->assignRole($roles)
        ->givePermissionTo($permissions);
}

/**
 * Authenticate a user using the web guard through sessions.
 */
function webActingAs(Authenticatable $user): Illuminate\Foundation\Testing\TestCase
{
    return actingAs($user, 'web');
}

/**
 * Assert that the user is authenticated.
 */
function assertWebAuthenticated(): Illuminate\Foundation\Testing\TestCase
{
    return assertAuthenticated('web');
}

/**
 * Authenticate a user using the guard api through Passport.
 *
 * @return mixed|\Illuminate\Foundation\Testing\TestCase
 */
function apiActingAs(Authenticatable $user, array $scopes = []): mixed
{
    Passport::actingAs($user, $scopes, 'api');

    return test();
}
