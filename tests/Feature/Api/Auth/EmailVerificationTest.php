<?php

use Database\Factories\UserFactory;
use Illuminate\Auth\Events\Verified;

use function Pest\Laravel\get;

test('email verification link can be resent', function () {
    $user = user(callback: fn (UserFactory $factory) => $factory->unverified());

    $response = apiActingAs($user)->post('api/email/verification-notification');

    $response->assertOk();
});

test('email verification link cannot be sent if user is already verified', function () {
    $user = user();

    $response = apiActingAs($user)->post('api/email/verification-notification');

    $response->assertInvalid();
});

test('email can be verified', function () {
    $user = user(callback: static fn (UserFactory $factory) => $factory->unverified());

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'api.email-verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = get($verificationUrl);

    Event::assertDispatched(Verified::class);

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(config('app.frontend_url').'/dashboard?verified=1');
});

test('email is not verified with invalid hash', function () {
    $user = user(callback: fn (UserFactory $factory) => $factory->unverified());

    $verificationUrl = URL::temporarySignedRoute(
        'api.email-verification.verify',
        now()->addMinutes(60),
        [
            'id' => $user->id,
            'hash' => sha1('wrong-email'),
        ]
    );

    get($verificationUrl);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});
