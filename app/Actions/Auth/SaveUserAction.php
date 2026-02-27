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
            $user->fill($input->except(['password', 'rols', 'status'])->toArray());
            if ($input->has('password')) {
                $user->password = Hash::make($input->get('password'));
                $user->to_show_password = $input->get('password');
            }
            if ($input->has('rols')) {
                $user->syncRoles([$input->get('rols')['id']]);
            }
            $user->makeHidden('to_show_password');
            if ($input->has('status') && ! $input->get('status')) {
                $user->delete();
            } else {
                $user->deleted_at = null;
            }
            $user->save();

            return $user;
        });
    }
}
