<?php

namespace App\Http\Controllers;

use App\Models\PoInfo;
use Illuminate\Http\Request;

class PoInfoController extends Controller
{
    public function index()
    {
        $poList = PoInfo::orderBy('po_code')->get();
        return view('admin.po_info', compact('poList'));
    }

    public function create()
    {
        return view('admin.po_info.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'po_code'       => 'required|string|max:10|unique:po_info,po_code',
            'po_short_name' => 'nullable|string|max:50',
            'po_name'       => 'required|string|max:255',
        ]);

        PoInfo::create([
            'po_code'       => strtoupper(trim($request->po_code)),
            'po_short_name' => $request->po_short_name,
            'po_name'       => $request->po_name,
            'is_active'     => 'Y',
            'created_by'    => auth()->user()->emp_id,
        ]);

        return redirect()->route('admin.po-info.index')->with('success', 'PO added successfully.');
    }

    public function edit($id)
    {
        $po = PoInfo::findOrFail($id);
        return view('admin.po_info.edit', compact('po'));
    }

    public function update(Request $request, $id)
    {
        $po = PoInfo::findOrFail($id);

        $request->validate([
            'po_code'       => 'required|string|max:10|unique:po_info,po_code,' . $id,
            'po_short_name' => 'nullable|string|max:50',
            'po_name'       => 'required|string|max:255',
        ]);

        $po->update([
            'po_code'       => strtoupper(trim($request->po_code)),
            'po_short_name' => $request->po_short_name,
            'po_name'       => $request->po_name,
            'updated_by'    => auth()->user()->emp_id,
            'updated_at'    => now(),
        ]);

        return redirect()->route('admin.po-info.index')->with('success', 'PO updated successfully.');
    }

    public function destroy($id)
    {
        $po = PoInfo::findOrFail($id);

        $po->update([
            'is_active'  => 'N',
            'updated_by' => auth()->user()->emp_id,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'PO deactivated successfully.');
    }

    public function activate($id)
    {
        $po = PoInfo::findOrFail($id);

        $po->update([
            'is_active'  => 'Y',
            'updated_by' => auth()->user()->emp_id,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'PO activated successfully.');
    }
}
