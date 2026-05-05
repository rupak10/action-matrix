<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getAllUsers()
    {
        return User::with('supervisor')->latest()->get();
    }

    public function createUser(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'emp_id' => $data['emp_id'],
            'designation' => $data['designation'],
            'dept_id' => $data['dept_id'],
            'dept_name' => $data['dept_name'] ?? null,
            'unit_id' => $data['unit_id'],
            'unit_name' => $data['unit_name'] ?? null,
            'supervisor_emp_id' => $data['supervisor_emp_id'] ?? null,
        ]);
    }

    public function getUserById($id)
    {
        return User::findOrFail($id);
    }

    public function updateUser($id, array $data)
    {
        $user = $this->getUserById($id);
        
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'emp_id' => $data['emp_id'],
            'designation' => $data['designation'],
            'dept_id' => $data['dept_id'],
            'dept_name' => $data['dept_name'] ?? null,
            'unit_id' => $data['unit_id'],
            'unit_name' => $data['unit_name'] ?? null,
            'supervisor_emp_id' => $data['supervisor_emp_id'] ?? null,
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        return $user;
    }

    public function deleteUser($id)
    {
        $user = $this->getUserById($id);
        return $user->delete();
    }
}
