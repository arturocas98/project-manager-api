<?php

namespace App\Actions\Auth;

use App\Models\Role;
use Illuminate\Support\Facades\DB;

class SaveRoleAction
{
    public function execute(array $input, ?Role $role = null): Role
    {
        return DB::transaction(function () use ($input, $role) {
            $role ??= new Role();
            $role->fill($input);
            $role->save();
            return $role;
        });
    }
}
