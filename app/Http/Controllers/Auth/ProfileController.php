<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ProfileRequest;
use App\Http\Resources\Auth\ProfileResource;
use App\Models\User;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Auth')]
#[Subgroup('Profile')]
#[Authenticated]
#[ResponseFromApiResource(ProfileResource::class, User::class)]
class ProfileController extends Controller
{
    protected ?User $user;

    public function __construct(Request $request)
    {
        $this->user = $request->user('api');

        $this->user->load(['permissions', 'roles.permissions']);
    }

    /**
     * Show profile
     *
     * Displays the profile of the authenticated user.
     */
    public function show(): ProfileResource
    {
        return ProfileResource::make($this->user);
    }

    /**
     * Update profile
     *
     * Updates the authenticated user's profile.
     */
    public function update(ProfileRequest $request): ProfileResource
    {
        if ($request->has('password')) {
            $this->user->password = $request->input('password');
        }

        if ($request->input('logout_on_all_devices')) {
            $this->user->tokens()->delete();
        }

        $this->user->save();

        return ProfileResource::make($this->user);
    }
}
