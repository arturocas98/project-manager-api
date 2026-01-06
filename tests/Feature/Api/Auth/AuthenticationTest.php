<?php

use function Pest\Laravel\postJson;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertTrue;

test('users can authenticate api', function () {
    $user = user();

    $response = postJson('api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk();

    assertNotNull($response->json()['data']['access_token']);
});

test('users with invalid password', function () {
    $user = user();

    $response = postJson('api/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertJsonValidationErrorFor('email');
});

test('users can logout', function () {
    $user = user();

    $response = postJson('api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    assertNotNull($response->json()['data']['access_token']);

    $token = $response->json()['data']['access_token'];

    $response = postJson(uri: 'api/logout', headers: [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertNoContent();
});

it('returns 2FA status if enabled and build cache for 2FA challenge', function () {
    $user = user([
        'two_factor_secret' => fake()->text(10),
        'two_factor_recovery_codes' => fake()->text(10),
        'two_factor_confirmed_at' => fake()->dateTimeBetween('-6 months'),
    ]);

    // Send a POST request to the login endpoint
    $response = postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    // Assert that the response contains the two-factor authentication flag
    $response->assertJson([
        'data' => [
            'two_factor_authentication' => true,
        ],
    ]);

    assertTrue(cache()->has('login.'.$user->getEmailForVerification()));
});
