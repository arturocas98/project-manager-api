<?php

use App\Enums\HeaderName;
use Symfony\Component\HttpFoundation\Response;

test('enable endpoint should throw exception if you have not previously confirmed the password', function () {
    $user = user();

    apiActingAs($user)
        ->postJson(uri: 'api/user/two-factor/authentication')
        ->assertStatus(Response::HTTP_LOCKED);
});

test('enable endpoint should throw exception if 2FA is already enabled', function () {
    $user = user([
        'two_factor_secret' => fake()->text(10),
        'two_factor_recovery_codes' => fake()->text(10),
        'two_factor_confirmed_at' => fake()->dateTimeBetween('-6 months'),
    ]);

    apiActingAs($user)
        ->postJson(uri: 'api/user/two-factor/authentication', headers: [
            HeaderName::ConfirmedPasswordToken->value => Crypt::encryptString($user->getKey().'|'.time()),
        ])
        ->assertForbidden();
});

test('enable endpoint should enable 2FA for the user', function () {
    $user = user();

    apiActingAs($user)
        ->postJson(uri: 'api/user/two-factor/authentication', headers: [
            HeaderName::ConfirmedPasswordToken->value => Crypt::encryptString($user->getKey().'|'.time()),
        ]);

    expect($user->refresh())
        ->two_factor_secret->not->toBeNull()
        ->two_factor_recovery_codes->not->toBeNull();
});

test('disable endpoint should throw exception if you have not previously confirmed the password', function () {
    $user = user();

    apiActingAs($user)
        ->deleteJson(uri: 'api/user/two-factor/authentication')
        ->assertStatus(Response::HTTP_LOCKED);
});

test('disable endpoint should throw exception if 2FA is not been enabled', function () {
    $user = user();

    apiActingAs($user)
        ->deleteJson(uri: 'api/user/two-factor/authentication', headers: [
            HeaderName::ConfirmedPasswordToken->value => Crypt::encryptString($user->getKey().'|'.time()),
        ])
        ->assertForbidden();
});

test('disable endpoint should enable 2FA for the user', function () {
    $user = user([
        'two_factor_secret' => fake()->text(10),
        'two_factor_recovery_codes' => fake()->text(10),
        'two_factor_confirmed_at' => fake()->dateTimeBetween('-6 months'),
    ]);

    apiActingAs($user)
        ->deleteJson(uri: 'api/user/two-factor/authentication', headers: [
            HeaderName::ConfirmedPasswordToken->value => Crypt::encryptString($user->getKey().'|'.time()),
        ]);

    expect($user->refresh())
        ->two_factor_secret->toBeNull()
        ->two_factor_recovery_codes->toBeNull()
        ->two_factor_confirmed_at->toBeNull();
});
