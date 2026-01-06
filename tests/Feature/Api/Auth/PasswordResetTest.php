<?php

use App\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\{postJson};

test('reset password link can be requested', function () {
    Notification::fake();

    $user = user();

    postJson('/api/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = user();

    postJson('/api/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = postJson('/api/reset-password/', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasNoErrors();

        return true;
    });
});
