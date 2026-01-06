<?php

use App\Models\Permission;
use App\Models\Role;

use function Pest\Laravel\assertCredentials;

test('profile information is displayed', function () {
    $response = apiActingAs(user())
        ->getJson('/api/user/profile');

    $response->assertOk();
});

test('profile information is displayed with permissions assigned', function () {
    Permission::findOrCreate('view', 'api');
    Permission::findOrCreate('create', 'api');
    Permission::findOrCreate('edit', 'api');
    Permission::findOrCreate('delete', 'api');
    Permission::findOrCreate('restore', 'api');
    Permission::findOrCreate('export', 'api');

    Role::findOrCreate('Eliminator Role', 'api')
        ->givePermissionTo(['delete', 'restore']);

    Role::findOrCreate('Creator Role', 'api')
        ->givePermissionTo(['edit', 'create']);

    $user = user(['name' => 'Luis Arce', 'email' => 'laa@teamq.biz', 'email_verified_at' => '2023-10-10T10:10:00'])
        ->givePermissionTo(['view'])
        ->assignRole(['Eliminator Role'])
        ->assignRole(['Creator Role']);

    apiActingAs($user)
        ->getJson('/api/user/profile')
        ->assertOk()
        ->assertExactJson([
            'data' => [
                'id' => $user->getKey(),
                'name' => 'Luis Arce',
                'email' => 'laa@teamq.biz',
                'email_verified_at' => '2023-10-10T10:10:00',
                'two_factor_confirmed_at' => null,
                'profile_photo_url' => 'https://ui-avatars.com/api/?name=L+A&color=7F9CF5&background=EBF4FF',
                'permissions' => [
                    'view',
                ],
                'roles' => [
                    'Eliminator Role',
                    'Creator Role',
                ],
                'access_permissions' => [
                    'create',
                    'delete',
                    'edit',
                    'restore',
                    'view',
                ],
            ],
        ]);
});

test('profile is updated', function () {
    $user = user([
        'email' => 'laa@teamq.biz',
        'password' => bcrypt('Laravel1234**'),
    ]);

    apiActingAs($user)
        ->patchJson('/api/user/profile', [
            'current_password' => 'Laravel1234**',
            'password' => 'NewPass12**',
            'password_confirmation' => 'NewPass12**',
        ])
        ->assertOk();

    assertCredentials([
        'email' => 'laa@teamq.biz',
        'password' => 'NewPass12**',
    ]);
});
