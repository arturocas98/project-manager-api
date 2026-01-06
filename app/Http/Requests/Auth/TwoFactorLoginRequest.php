<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

class TwoFactorLoginRequest extends FormRequest
{
    /**
     * The user attempting the two factor challenge.
     *
     * @var mixed
     */
    protected ?User $challengedUser = null;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'code' => 'nullable|string',
            'recovery_code' => 'nullable|string',
            'email' => 'nullable|email',
        ];
    }

    /**
     * Determine if the request has a valid two-factor code.
     */
    public function hasValidCode(): bool
    {
        if (! $this->code) {
            return false;
        }

        return tap(
            app(TwoFactorAuthenticationProvider::class)->verify(
                decrypt($this->challengedUser()->two_factor_secret),
                $this->code
            ),
            function ($result) {
                if ($result) {
                    cache()->forget('login.'.$this->email);
                }
            }
        );
    }

    /**
     * Get the valid recovery code if one exists on the request.
     */
    public function validRecoveryCode(): ?string
    {
        if (! $this->recovery_code) {
            return null;
        }

        return tap(
            collect($this->challengedUser()->recoveryCodes())->first(function ($code) {
                return hash_equals($code, $this->recovery_code) ? $code : null;
            }),
            function ($code) {
                if ($code) {
                    cache()->forget('login.'.$this->email);
                }
            }
        );
    }

    /**
     * Get the user that is attempting the two factor challenge.
     */
    public function challengedUser(): mixed
    {
        if ($this->challengedUser) {
            return $this->challengedUser;
        }

        try {
            if (! cache()->has('login.'.$this->email) ||
                ! $user = User::find(decrypt(cache()->get('login.'.$this->email)))) {
                throw new HttpResponseException(
                    app(FailedTwoFactorLoginResponse::class)->toResponse($this)
                );
            }
        } catch (Exception) {
            throw new HttpResponseException(
                app(FailedTwoFactorLoginResponse::class)->toResponse($this)
            );
        }

        return $this->challengedUser = $user;
    }
}
