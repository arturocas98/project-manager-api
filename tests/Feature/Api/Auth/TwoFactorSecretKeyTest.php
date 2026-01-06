<?php

use App\Enums\HeaderName;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Symfony\Component\HttpFoundation\Response;

test('get secret key should throw exception if you have not previously confirmed the password', function () {
    $user = user();

    apiActingAs($user)
        ->getJson(uri: 'api/user/two-factor/secret-key')
        ->assertStatus(Response::HTTP_LOCKED);
});

test('get secret key should throw forbidden exception if 2FA has not been enabled', function () {
    $user = user();

    apiActingAs($user)
        ->getJson(uri: 'api/user/two-factor/secret-key', headers: [
            HeaderName::ConfirmedPasswordToken->value => Crypt::encryptString($user->getKey().'|'.time()),
        ])
        ->assertForbidden();
});

test('get secret key should throw forbidden exception if 2FA is already confirmed', function () {
    $user = user([
        'two_factor_secret' => fake()->text(10),
        'two_factor_recovery_codes' => fake()->text(10),
        'two_factor_confirmed_at' => fake()->dateTimeBetween('-6 months'),
    ]);

    apiActingAs($user)
        ->getJson(uri: 'api/user/two-factor/secret-key', headers: [
            HeaderName::ConfirmedPasswordToken->value => Crypt::encryptString($user->getKey().'|'.time()),
        ])
        ->assertForbidden();
});

test('get secret key codes should show the code', function () {
    $user = user();

    $action = app(EnableTwoFactorAuthentication::class);

    $action($user);

    $response = apiActingAs($user)
        ->getJson(uri: 'api/user/two-factor/secret-key', headers: [
            HeaderName::ConfirmedPasswordToken->value => Crypt::encryptString($user->getKey().'|'.time()),
        ])
        ->assertOk();

    expect(json_decode($response->getContent()))
        ->toHaveProperty('secret_key', decrypt($user->two_factor_secret));
});
