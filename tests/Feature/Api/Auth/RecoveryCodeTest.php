<?php

use App\Enums\HeaderName;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Symfony\Component\HttpFoundation\Response;

test('get recovery codes should throw exception if you have not previously confirmed the password', function () {
    $user = user();

    apiActingAs($user)
        ->getJson(uri: 'api/user/two-factor/recovery-codes')
        ->assertStatus(Response::HTTP_LOCKED);
});

test('get recovery codes should throw exception if 2FA has not been enabled', function () {
    $user = user();

    apiActingAs($user)
        ->getJson(uri: 'api/user/two-factor/recovery-codes', headers: [
            HeaderName::ConfirmedPasswordToken->value => Crypt::encryptString($user->getKey().'|'.time()),
        ])
        ->assertForbidden();
});

test('get recovery codes should show the codes', function () {
    $user = user();

    $action = app(EnableTwoFactorAuthentication::class);

    $action($user);

    $response = apiActingAs($user)
        ->getJson(uri: 'api/user/two-factor/recovery-codes', headers: [
            HeaderName::ConfirmedPasswordToken->value => Crypt::encryptString($user->getKey().'|'.time()),
        ])
        ->assertOk();

    expect(json_decode($response->getContent()))
        ->toBe(json_decode(decrypt($user->two_factor_recovery_codes), true));
});

test('regenerate recovery codes should throw exception if you have not previously confirmed the password', function () {
    $user = user();

    apiActingAs($user)
        ->postJson(uri: 'api/user/two-factor/recovery-codes')
        ->assertStatus(Response::HTTP_LOCKED);
});

test('regenerate recovery codes should throw exception if 2FA has not been enabled', function () {
    $user = user();

    apiActingAs($user)
        ->getJson(uri: 'api/user/two-factor/recovery-codes', headers: [
            HeaderName::ConfirmedPasswordToken->value => Crypt::encryptString($user->getKey().'|'.time()),
        ])
        ->assertForbidden();
});

test('regenerate recovery codes should return not content', function () {
    $user = user();

    $action = app(EnableTwoFactorAuthentication::class);

    $action($user);

    $oldCodes = $user->two_factor_recovery_codes;

    $response = apiActingAs($user)
        ->postJson(uri: 'api/user/two-factor/recovery-codes', headers: [
            HeaderName::ConfirmedPasswordToken->value => Crypt::encryptString($user->getKey().'|'.time()),
        ])
        ->assertNoContent();

    expect(json_decode($response->getContent()))
        ->not->toBe(json_decode(decrypt($oldCodes), true));
});
