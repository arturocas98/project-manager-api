<?php

use Illuminate\Support\Facades\Crypt;

use function Pest\Laravel\postJson;

test('confirm-password endpoint should return a valid JSON response with confirmed_password_token', function () {
    $user = user();

    $encryptedToken = Crypt::encryptString($user->getKey().'|'.time());

    Crypt::shouldReceive('encryptString')->andReturn($encryptedToken);

    apiActingAs($user)
        ->postJson('api/user/confirm-password', [
            'current_password' => 'password',
        ])
        ->assertJson([
            'confirmed_password_token' => $encryptedToken,
        ])
        ->assertOk();
});

test('confirm-password endpoint should be throttled to 3 requests per minute', function () {
    $user = user();

    apiActingAs($user);

    // Make 4 requests within a minute
    for ($count = 1; $count <= 4; $count++) {
        $response = postJson('api/user/confirm-password', [
            'current_password' => 'password',
        ]);

        if ($count < 4) {
            $response->assertOk();
        } else {
            $response->assertTooManyRequests();
        }
    }
});
