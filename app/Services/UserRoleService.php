<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;

class UserRoleService
{
    public function getAllUsersWithRoles()
    {
        return User::with('roles')->latest()->get();
    }

    public function getAllRoles()
    {
        return Role::where('is_active', true)->orderBy('name')->get();
    }

    public function getUserByEmpId($empId)
    {
        return User::where('emp_id', $empId)->with('roles')->firstOrFail();
    }

    public function assignRoles($empId, array $roleIds)
    {
        $user = User::where('emp_id', $empId)->firstOrFail();
        
        // Sync roles (this handles adding new ones and removing ones not in the array)
        $user->roles()->syncWithPivotValues($roleIds, [
            'created_by' => Auth::user()->emp_id ?? 'System',
            'updated_by' => Auth::user()->emp_id ?? 'System',
        ]);

        return $user;
    }
}
