<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSupervisor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getAllUsers()
    {
        return User::with('supervisors.supervisor')->latest()->get();
    }

    public function createUser(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name'        => $data['name'],
                'email'       => $data['email'],
                'password'    => Hash::make($data['password']),
                'emp_id'      => $data['emp_id'],
                'emp_type'    => $data['emp_type'] ?? null,
                'po_code'     => $data['po_code'] ?? null,
                'designation' => $data['designation'],
                'dept_id'     => $data['dept_id'],
                'dept_name'   => $data['dept_name'] ?? null,
                'unit_id'     => $data['unit_id'],
                'unit_name'   => $data['unit_name'] ?? null,
            ]);

            $this->syncSupervisors(
                $user->emp_id,
                $data['supervisor_emp_ids'] ?? [],
                $data['primary_supervisor_emp_id'] ?? null
            );

            return $user;
        });
    }

    public function getUserById($id)
    {
        return User::findOrFail($id);
    }

    public function updateUser($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $user = $this->getUserById($id);

            $updateData = [
                'name'        => $data['name'],
                'email'       => $data['email'],
                'emp_id'      => $data['emp_id'],
                'emp_type'    => $data['emp_type'] ?? $user->emp_type,
                'po_code'     => $data['po_code'] ?? null,
                'designation' => $data['designation'],
                'dept_id'     => $data['dept_id'],
                'dept_name'   => $data['dept_name'] ?? null,
                'unit_id'     => $data['unit_id'],
                'unit_name'   => $data['unit_name'] ?? null,
            ];

            if (!empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            $user->update($updateData);

            $this->syncSupervisors(
                $user->emp_id,
                $data['supervisor_emp_ids'] ?? [],
                $data['primary_supervisor_emp_id'] ?? null
            );

            return $user;
        });
    }

    public function deleteUser($id)
    {
        $user = $this->getUserById($id);
        return $user->delete();
    }

    private function syncSupervisors(string $empId, array $supervisorEmpIds, ?string $primaryEmpId): void
    {
        UserSupervisor::where('emp_id', $empId)->delete();

        foreach (array_filter($supervisorEmpIds) as $supEmpId) {
            UserSupervisor::create([
                'emp_id'            => $empId,
                'supervisor_emp_id' => $supEmpId,
                'is_primary'        => ($supEmpId === $primaryEmpId),
            ]);
        }
    }
}
