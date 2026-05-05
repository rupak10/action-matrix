<?php

namespace App\Http\Controllers;

use App\Services\UserRoleService;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    protected $userRoleService;

    public function __construct(UserRoleService $userRoleService)
    {
        $this->userRoleService = $userRoleService;
    }

    public function index()
    {
        $users = $this->userRoleService->getAllUsersWithRoles();
        return view('user_roles.index', compact('users'));
    }

    public function edit($empId)
    {
        $user = $this->userRoleService->getUserByEmpId($empId);
        $roles = $this->userRoleService->getAllRoles();
        return view('user_roles.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $empId)
    {
        $request->validate([
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $this->userRoleService->assignRoles($empId, $request->input('roles', []));

        return redirect()->route('user-roles.index')->with('success', 'User roles updated successfully.');
    }
}
