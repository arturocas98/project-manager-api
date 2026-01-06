<?php

use App\Enums\HeaderName;
use PragmaRX\Google2FA\Google2FA;
use Symfony\Component\HttpFoundation\Response;

test('confirmed endpoint should throw exception if you have not previously confirmed the password', function () {
    $user = user();

    apiActingAs($user)
        ->postJson(uri: 'api/user/two-factor/confirmed')
        ->assertStatus(Response::HTTP_LOCKED);
});

test('confirmed endpoint should throw exception if 2FA is already enabled', function () {
    $user = user([
        'two_factor_secret' => fake()->text(10),
        'two_factor_recovery_codes' => fake()->text(10),
        'two_factor_confirmed_at' => fake()->dateTimeBetween('-6 months'),
    ]);

    apiActingAs($user)
        ->postJson(uri: 'api/user/two-factor/confirmed', headers: [
            HeaderName::ConfirmedPasswordToken->value => Crypt::encryptString($user->getKey().'|'.time()),
        ])
        ->assertForbidden();
});

test('confirmed endpoint should confirm 2FA for the user', function () {
    $tfaEngine = app(Google2FA::class);
    $userSecret = $tfaEngine->generateSecretKey();
    $validOtp = $tfaEngine->getCurrentOtp($userSecret);

    $user = user([
        'two_factor_secret' => encrypt($userSecret),
    ]);

    apiActingAs($user)
        ->postJson(
            uri: 'api/user/two-factor/confirmed',
            data: [
                'code' => $validOtp,
            ],
            headers: [
                HeaderName::ConfirmedPasswordToken->value => Crypt::encryptString($user->getKey().'|'.time()),
            ]
        );

    expect($user->refresh())
        ->hasEnabledTwoFactorAuthentication()->toBeTrue();
});
