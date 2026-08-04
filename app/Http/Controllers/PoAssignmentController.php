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

        $smUsers = User::where('emp_type', 'PKSF')
            ->with('roles', 'poAssignments')
            ->orderBy('name')
            ->get();

        $poList = PoInfo::where('is_active', 'Y')->orderBy('po_code')->get();

        return view('admin.po_assignments', compact('smUsers', 'poList'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasAnyRole(['Admin', 'Super_Admin']), 403);

        $request->validate([
            'emp_id'  => 'required|string|exists:users,emp_id',
            'po_code' => 'required|string',
        ]);

        // MD doesn't need PO assignments (full access by role)
        $user = User::where('emp_id', $request->emp_id)->firstOrFail();
        abort_if($user->isSmMd(), 422, 'MD has full access to all POs — no assignment needed.');

        UserPoAssignment::firstOrCreate([
            'emp_id'  => $request->emp_id,
            'po_code' => $request->po_code,
        ]);

        return back()->with('success', 'PO assigned successfully.');
    }

    public function destroy(int $id)
    {
        abort_unless(auth()->user()->hasAnyRole(['Admin', 'Super_Admin']), 403);

        UserPoAssignment::findOrFail($id)->delete();

        return back()->with('success', 'PO assignment removed.');
    }

}
