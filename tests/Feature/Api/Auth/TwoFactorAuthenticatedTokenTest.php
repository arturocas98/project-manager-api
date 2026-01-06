<?php

use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use PragmaRX\Google2FA\Google2FA;

use function Pest\Laravel\postJson;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNotNull;

test('two-factor-challenge endpoint should be throttled to 3 requests per minute', function () {
    $user = user([
        'email' => 'laa@teamq.biz',
        'two_factor_confirmed_at' => fake()->dateTimeBetween('-6 months'),
    ]);

    $action = app(EnableTwoFactorAuthentication::class);
    $action($user);

    cache()->add('login.'.$user->getEmailForVerification(), encrypt($user->getKey()), now()->addMinutes(3));

    for ($count = 1; $count <= 4; $count++) {
        $response = postJson('api/two-factor-challenge', [
            'code' => '123456',
            'email' => 'laa@teamq.biz',
        ]);

        if ($count < 4) {
            $response->assertUnprocessable();
        } else {
            $response->assertTooManyRequests();
        }
    }
});

test('two-factor-challenge endpoint should be throw 422 exception if code is invalid', function () {
    $user = user([
        'email' => 'laa@teamq.biz',
        'two_factor_confirmed_at' => fake()->dateTimeBetween('-6 months'),
    ]);

    $action = app(EnableTwoFactorAuthentication::class);
    $action($user);

    cache()->add('login.'.$user->getEmailForVerification(), encrypt($user->getKey()), now()->addMinutes(3));

    $response = postJson('api/two-factor-challenge', [
        'code' => '123456',
        'email' => 'laa@teamq.biz',
    ]);
    $response->assertUnprocessable();
});

test('two-factor-challenge endpoint should be throw 422 exception if recovery_code is invalid', function () {
    $user = user([
        'email' => 'laa@teamq.biz',
        'two_factor_secret' => encrypt('secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['invalid-code', 'valid-code'])),
        'two_factor_confirmed_at' => fake()->dateTimeBetween('-6 months'),
    ]);

    cache()->add('login.'.$user->getEmailForVerification(), encrypt($user->getKey()), now()->addMinutes(3));

    $response = postJson('api/two-factor-challenge', [
        'recovery_code' => 'missing-code',
        'email' => 'laa@teamq.biz',
    ]);
    $response->assertUnprocessable();
});

test('two-factor-challenge endpoint should be throw 422 exception if email not match', function () {
    $tfaEngine = app(Google2FA::class);
    $userSecret = $tfaEngine->generateSecretKey();
    $validOtp = $tfaEngine->getCurrentOtp($userSecret);

    $user = user([
        'email' => 'laa@teamq.biz',
        'two_factor_secret' => encrypt($userSecret),
        'two_factor_confirmed_at' => fake()->dateTimeBetween('-6 months'),
    ]);

    cache()->add('login.'.$user->getEmailForVerification(), encrypt($user->getKey()), now()->addMinutes(3));

    $response = postJson('api/two-factor-challenge', [
        'code' => $validOtp,
        'email' => 'gbp@teamq.biz',
    ]);
    $response->assertUnprocessable();
});

test('two-factor-challenge endpoint should be throw 422 exception and flush cache after has been passed', function () {
    $tfaEngine = app(Google2FA::class);
    $userSecret = $tfaEngine->generateSecretKey();
    $validOtp = $tfaEngine->getCurrentOtp($userSecret);

    $user = user([
        'email' => 'laa@teamq.biz',
        'two_factor_secret' => encrypt($userSecret),
        'two_factor_confirmed_at' => fake()->dateTimeBetween('-6 months'),
    ]);

    cache()->add('login.'.$user->getEmailForVerification(), encrypt($user->getKey()), now()->addMinutes(3));

    // Passe validation
    $response = postJson('api/two-factor-challenge', [
        'code' => $validOtp,
        'email' => 'laa@teamq.biz',
    ]);
    $response->assertOk();

    // Assert flush cache
    assertFalse(cache()->has('login.'.$user->getEmailForVerification()));

    // Assert throw 422 exception
    $response = postJson('api/two-factor-challenge', [
        'code' => $validOtp,
        'email' => 'laa@teamq.biz',
    ]);
    $response->assertUnprocessable();
});

test('two-factor-challenge endpoint should be return access_token if passes with code', function () {
    $tfaEngine = app(Google2FA::class);
    $userSecret = $tfaEngine->generateSecretKey();
    $validOtp = $tfaEngine->getCurrentOtp($userSecret);

    $user = user([
        'email' => 'laa@teamq.biz',
        'two_factor_secret' => encrypt($userSecret),
        'two_factor_confirmed_at' => fake()->dateTimeBetween('-6 months'),
    ]);

    cache()->add('login.'.$user->getEmailForVerification(), encrypt($user->getKey()), now()->addMinutes(3));

    $response = postJson('api/two-factor-challenge', [
        'code' => $validOtp,
        'email' => 'laa@teamq.biz',
    ]);

    $response->assertOk();

    assertNotNull($response->json()['data']['access_token']);
});

test('two-factor-challenge endpoint should be return access_token if passes with recovery code', function () {
    $user = user([
        'email' => 'laa@teamq.biz',
        'two_factor_secret' => encrypt('secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['invalid-code', 'valid-code'])),
        'two_factor_confirmed_at' => fake()->dateTimeBetween('-6 months'),
    ]);

    cache()->add('login.'.$user->getEmailForVerification(), encrypt($user->getKey()), now()->addMinutes(3));

    $response = postJson('api/two-factor-challenge', [
        'recovery_code' => 'valid-code',
        'email' => 'laa@teamq.biz',
    ]);

    $response->assertOk();

    assertNotNull($response->json()['data']['access_token']);
});
