<?php

use App\Enums\HeaderName;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Symfony\Component\HttpFoundation\Response;

test('endpoint should throw exception if you have not previously confirmed the password', function () {
    $user = user();

    apiActingAs($user)
        ->getJson(uri: 'api/user/two-factor/qr-code')
        ->assertStatus(Response::HTTP_LOCKED);
});

test('endpoint should throw exception if 2FA is already confirmed', function () {
    $user = user([
        'two_factor_secret' => fake()->text(10),
        'two_factor_recovery_codes' => fake()->text(10),
        'two_factor_confirmed_at' => fake()->dateTimeBetween('-6 months'),
    ]);

    apiActingAs($user)
        ->getJson(uri: 'api/user/two-factor/qr-code', headers: [
            HeaderName::ConfirmedPasswordToken->value => Crypt::encryptString($user->getKey().'|'.time()),
        ])
        ->assertForbidden();
});

test('endpoint should throw exception if 2FA has not been enabled', function () {
    $user = user();

    apiActingAs($user)
        ->getJson(uri: 'api/user/two-factor/qr-code', headers: [
            HeaderName::ConfirmedPasswordToken->value => Crypt::encryptString($user->getKey().'|'.time()),
        ])
        ->assertForbidden();
});

test('endpoint should show qr code as svg and url', function () {
    $user = user();

    $action = app(EnableTwoFactorAuthentication::class);

    $action($user);

    $response = apiActingAs($user)
        ->getJson(uri: 'api/user/two-factor/qr-code', headers: [
            HeaderName::ConfirmedPasswordToken->value => Crypt::encryptString($user->getKey().'|'.time()),
        ])
        ->assertOk();

    expect(json_decode($response->getContent()))
        ->toHaveProperty('svg', $user->twoFactorQrCodeSvg())
        ->toHaveProperty('url', $user->twoFactorQrCodeUrl());
});
