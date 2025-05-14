<?php

namespace App\Services\V1\API\Role;

use Spatie\Permission\Models\Role;

class RoleService
{

    public function getAllRoles()
    {
        return Role::all();
    }

    public function createRole(array $data)
    {
        return Role::create($data);
    }

    public function updateRole(Role $role, array $data)
    {
        $role->update($data);
        return $role;
    }

    public function deleteRole(Role $role)
    {
        $role->delete();
        return $role;
    }

    public function getRoleById(int $id)
    {
        return Role::find($id);
    }

    public function getRoleByField(string $field, string $value)
    {
        return Role::where($field, $value)->first();
    }
}