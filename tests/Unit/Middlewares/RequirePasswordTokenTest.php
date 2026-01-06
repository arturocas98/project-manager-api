<?php

use App\Enums\HeaderName;
use App\Http\Middleware\RequirePasswordToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->middleware = new RequirePasswordToken();
    $this->request = new Request();
    $this->next = fn ($request) => true;

    $user = user();
    $this->request->setUserResolver(fn () => $user);
});

test('valid password confirmation token should pass', function () {
    $token = Crypt::encryptString($this->request->user()->getKey().'|'.time()); // Valid token

    $this->request->headers->set(HeaderName::ConfirmedPasswordToken->value, $token);

    $response = $this->middleware->handle($this->request, $this->next);

    expect($response)->toBeTrue();
});

test('missing or invalid password confirmation token should throw HttpException', function () {
    // Missing token
    expect(function () {
        $this->middleware->handle($this->request, $this->next);
    })->toThrow(HttpException::class, __('passwords.invalid_confirmation_token'));

    // Invalid token with user not authenticated
    $this->request->headers->set(HeaderName::ConfirmedPasswordToken->value, 123);

    expect(function () {
        $this->middleware->handle($this->request, $this->next);
    })->toThrow(HttpException::class, __('passwords.invalid_confirmation_token'));
});

test('valid token does not match authenticated user should throw HttpException', function () {
    $user = user();

    $token = Crypt::encryptString($user->getKey().'|'.time()); // Valid token

    // Valid token with user not authenticated
    $this->request->headers->set(HeaderName::ConfirmedPasswordToken->value, $token);

    expect(function () {
        $this->middleware->handle($this->request, $this->next);
    })->toThrow(HttpException::class, __('passwords.invalid_confirmation_token'));
});

test('valid token expired should throw HttpException', function () {
    $token = Crypt::encryptString(
        $this->request->user()->getKey().'|'.(time() - config('auth.password_timeout'))
    ); // Valid token

    // Valid token with expired time
    $this->request->headers->set(HeaderName::ConfirmedPasswordToken->value, $token);

    expect(function () {
        $this->middleware->handle($this->request, $this->next);
    })->toThrow(HttpException::class, __('passwords.invalid_confirmation_token'));
});
