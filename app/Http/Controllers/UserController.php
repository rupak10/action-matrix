<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Demo data for dropdowns
     */
    protected function getDemoData()
    {
        return [
            'departments' => [
                'IT-01' => 'Information Technology',
                'HR-01' => 'Human Resources',
                'FIN-01' => 'Finance',
                'OPS-01' => 'Operations',
                'AUD-01' => 'Internal Audit'
            ],
            'units' => [
                'UNIT-A' => 'Core Development',
                'UNIT-B' => 'Quality Assurance',
                'UNIT-C' => 'Talent Acquisition',
                'UNIT-D' => 'Risk Management',
                'UNIT-E' => 'Compliance'
            ],
            'pos' => [
                'PO-001' => 'PO One',
                'PO-007' => 'PO Seven',
                'PO-012' => 'PO Twelve',
                'PO-045' => 'PO Forty-Five'
            ]
        ];
    }

    public function index()
    {
        $users = $this->userService->getAllUsers();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $data = $this->getDemoData();
        $supervisors = \App\Models\User::select('emp_id', 'name')->orderBy('name')->get();
        return view('users.create', [
            'departments' => $data['departments'],
            'units' => $data['units'],
            'pos' => $data['pos'],
            'supervisors' => $supervisors
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'emp_id' => 'required|string|max:20|unique:users',
            'emp_type' => 'required|in:PKSF,PO',
            'po_code' => 'required_if:emp_type,PO|nullable|string',
            'designation' => 'required|string|max:100',
            'dept_id' => 'required|string',
            'unit_id' => 'required|string',
            'supervisor_emp_id' => 'nullable|string|exists:users,emp_id',
        ]);

        // Map IDs back to names for demo purposes
        $demo = $this->getDemoData();
        $data = $request->all();
        $data['dept_name'] = $demo['departments'][$request->dept_id] ?? 'Other';
        $data['unit_name'] = $demo['units'][$request->unit_id] ?? 'Other';

        $this->userService->createUser($data);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit($id)
    {
        $user = $this->userService->getUserById($id);
        $data = $this->getDemoData();
        $supervisors = \App\Models\User::where('id', '!=', $id)->select('emp_id', 'name')->orderBy('name')->get();
        
        return view('users.edit', [
            'user' => $user,
            'departments' => $data['departments'],
            'units' => $data['units'],
            'pos' => $data['pos'],
            'supervisors' => $supervisors
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$id,
            'password' => 'nullable|string|min:8|confirmed',
            'emp_id' => 'required|string|max:20|unique:users,emp_id,'.$id,
            'emp_type' => 'required|in:PKSF,PO',
            'po_code' => 'required_if:emp_type,PO|nullable|string',
            'designation' => 'required|string|max:100',
            'dept_id' => 'required|string',
            'unit_id' => 'required|string',
            'supervisor_emp_id' => 'nullable|string|exists:users,emp_id',
        ]);

        $demo = $this->getDemoData();
        $data = $request->all();
        $data['dept_name'] = $demo['departments'][$request->dept_id] ?? 'Other';
        $data['unit_name'] = $demo['units'][$request->unit_id] ?? 'Other';

        $this->userService->updateUser($id, $data);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        $this->userService->deleteUser($id);
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
