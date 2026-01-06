<?php

namespace Database\Seeders\Auth;

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = $this->roles();

        foreach ($this->users() as $user) {
            if ($user->trashed()) {
                continue;
            }

            $user->markEmailAsVerified();
            $user->assignRole($roles);
        }
    }

    /**
     * @return User[]
     */
    protected function users(): array
    {
        return [
            User::withTrashed()->firstOrCreate(['email' => config('auth.admin.email')], [
                'name' => 'TeamQ',
                'password' => bcrypt(config('auth.admin.password')),
            ]),
        ];
    }

    /**
     * @return Role[]
     */
    protected function roles(): array
    {
        return [
            Role::findByName(RoleName::Admin->value),
            Role::findByName(RoleName::ItSupport->value),
        ];
    }
}
