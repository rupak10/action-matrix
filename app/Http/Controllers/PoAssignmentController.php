<?php

namespace App\Http\Controllers;

use App\Models\PoInfo;
use App\Models\User;
use App\Models\UserPoAssignment;
use Illuminate\Http\Request;

class PoAssignmentController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->hasAnyRole(['Admin', 'Super_Admin']), 403);

        // Form dropdown: PKSF users excluding MD (full access)
        $smUsers = User::where('emp_type', 'PKSF')
            ->with('roles')
            ->orderBy('name')
            ->get()
            ->filter(fn($u) => !$u->isSmMd());

        $poList = PoInfo::where('is_active', 'Y')->orderBy('po_code')->get();

        // Flat assignments for the table
        $assignments = UserPoAssignment::with(['user.roles', 'po'])
            ->orderBy('emp_id')
            ->get();

        return view('admin.po_assignments', compact('smUsers', 'poList', 'assignments'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasAnyRole(['Admin', 'Super_Admin']), 403);

        $request->validate([
            'emp_id'   => 'required|string|exists:users,emp_id',
            'po_code'  => 'required|string',
            'emp_role' => 'required|in:CO,SO,MGT',
        ]);

        $user = User::where('emp_id', $request->emp_id)->firstOrFail();
        abort_if($user->isSmMd(), 422, 'MD has full access to all POs — no assignment needed.');

        // Update role if assignment already exists, otherwise create
        UserPoAssignment::updateOrCreate(
            ['emp_id' => $request->emp_id, 'po_code' => $request->po_code],
            ['emp_role' => $request->emp_role]
        );

        return back()->with('success', 'PO assigned successfully.');
    }

    public function destroy(int $id)
    {
        abort_unless(auth()->user()->hasAnyRole(['Admin', 'Super_Admin']), 403);

        UserPoAssignment::findOrFail($id)->delete();

        return back()->with('success', 'PO assignment removed.');
    }

}
