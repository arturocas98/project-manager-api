<?php

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $this->url = url(config('pulse.path'));
});

test('pulse is not accessible if not authenticated', function () {
    $this->get($this->url)
        ->assertRedirect(route('login'));
});

test('pulse is not accessible if not authorized', function () {
    webActingAs(User::factory()->create())
        ->get($this->url)
        ->assertForbidden();
});

test('pulse is accessible with "It Support" role', function () {
    $user = User::factory()->create();
    $role = Role::findOrCreate(RoleName::ItSupport->value);

    $user->assignRole($role);

    webActingAs($user)
        ->get($this->url)
        ->assertSuccessful();
});
