<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Support\Facades\Auth;

class RoleService
{
    public function getAllRoles()
    {
        return Role::latest()->get();
    }

    public function createRole(array $data)
    {
        return Role::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'role_group' => $data['role_group'],
            'is_active' => isset($data['is_active']) ? (bool)$data['is_active'] : true,
            'created_by' => Auth::user()->emp_id ?? 'System',
        ]);
    }

    public function getRoleById($id)
    {
        return Role::findOrFail($id);
    }

    public function updateRole($id, array $data)
    {
        $role = $this->getRoleById($id);
        
        $role->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'role_group' => $data['role_group'],
            'is_active' => isset($data['is_active']) ? (bool)$data['is_active'] : true,
            'updated_by' => Auth::user()->emp_id ?? 'System',
        ]);

        return $role;
    }

    public function deleteRole($id)
    {
        $role = $this->getRoleById($id);
        return $role->delete();
    }
}
