<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SaveUserAction
{
    public function execute(Collection $input, ?User $user = null): User
    {
        return DB::transaction(function () use ($input, $user) {

            $user ??= new User;

            $user->fill(
                $input->except(['password', 'role_id', 'status', 'rols'])->toArray()
            );

            if ($input->has('password') && $input->get('password')) {
                $user->password = Hash::make($input->get('password'));
                $user->to_show_password = $input->get('password');
            }

            $user->save();

            if ($input->has('role_id')) {
                $user->syncRoles([$input->get('role_id')]);
            }
            if ($input->has('rols')) {
                $user->syncRoles($input->get('rols'));
            }

            if ($input->has('status') && ! $input->get('status')) {
                $user->delete();
            } else {
                $user->restore();
            }

            $user->makeHidden('to_show_password');

            return $user->refresh();
        });
    }
}
