<?php

namespace App\Http\Controllers;

use App\Services\ActionMatrixService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActionMatrixController extends Controller
{
    protected $acmService;

    public function __construct(ActionMatrixService $acmService)
    {
        $this->acmService = $acmService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        
        // Filter: Show if current user is the creator OR if the item is currently at their desk
        $matrices = \App\Models\AcmMaster::where('created_by', $user->emp_id)
            ->orWhere('current_desk_emp_id', $user->emp_id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Fetch all PO Concern Officers (role 'PO_CO') for automatic assignment
        $poOfficers = \App\Models\User::whereHas('roles', function($q) {
            $q->where('name', 'PO_CO');
        })->get();
            
        return view('action_matrix.index', compact('matrices', 'poOfficers'));
    }

    /**
     * Show the form for creating a new Action Matrix.
     */
    public function create()
    {
        // Dummy data for dropdowns
        $poList = [
            ['code' => '001', 'name' => 'PO One'],
            ['code' => '007', 'name' => 'PO Seven'],
            ['code' => '010', 'name' => 'PO Ten'],
        ];

        $categories = ['FINANCIAL', 'OPERATIONAL', 'COMPLIANCE', 'GOVERNANCE'];
        $visitTypes = ['ONSITE', 'OFFSITE'];
        $visitCategories = ['REGULAR VISIT', 'MANAGEMENT AUDIT', 'SPECIAL AUDIT'];
        $priorities = ['LOW', 'MEDIUM', 'HIGH'];

        return view('action_matrix.create', compact('poList', 'categories', 'visitTypes', 'visitCategories', 'priorities'));
    }

    /**
     * Store a newly created Action Matrix in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'po_code' => 'required|string|max:5',
            'visiting_date' => 'required|date',
            'observation_category' => 'required|string',
            'visit_type' => 'required|string',
            'visit_category' => 'required|string',
            'letter_issue_date' => 'nullable|date',
            'letter_response_date' => 'nullable|date',
            'pksf_observation' => 'required|string',
            'direction_to_po' => 'required|string',
            'action_matrix' => 'required|in:Y,N',
            'priority' => 'required|string',
            'attachments' => 'nullable|array|max:3',
            'attachments.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,txt|max:5120',
        ], [
            'attachments.max' => 'You can upload a maximum of 3 files.',
            'attachments.*.mimes' => 'Only PDF, Word, Excel, Text, and Image files are allowed.',
            'attachments.*.max' => 'Each file must not exceed 5MB.',
        ]);

        try {
            $status = 'SAVED'; // Always SAVED on initial creation
            
            // Pass the files to the service if any exist
            $files = $request->file('attachments') ?? [];
            
            $master = $this->acmService->createMaster($validated, $status, $files);
            
            return redirect()->route('action-matrix.index')
                ->with('success', 'Action Matrix ' . $master->acm_id . ' has been ' . $status . ' successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create Action Matrix: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified Action Matrix.
     */
    public function show($id)
    {
        // Placeholder for show view
        return "Action Matrix Created Successfully! ACM ID: " . $id;
    }

    /**
     * Forward the Action Matrix to a supervisor.
     */
    public function forward(Request $request)
    {
        $request->validate([
            'acm_id' => 'required|string',
            'remarks' => 'nullable|string|max:1000'
        ]);

        try {
            $this->acmService->forwardMatrix($request->acm_id, $request->remarks, auth()->user());
            return redirect()->route('action-matrix.index')
                ->with('success', 'Action Matrix ' . $request->acm_id . ' has been forwarded to your supervisor.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Approve the Action Matrix.
     */
    public function approve(Request $request)
    {
        $request->validate([
            'acm_id' => 'required|string',
            'to_emp_id' => 'required|string|exists:users,emp_id',
            'remarks' => 'nullable|string|max:1000'
        ]);

        try {
            $this->acmService->approveMatrix($request->acm_id, $request->remarks, $request->to_emp_id, auth()->user());
            return redirect()->route('action-matrix.index')
                ->with('success', 'Action Matrix ' . $request->acm_id . ' has been approved and forwarded to PO.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject (Send Back) the Action Matrix.
     */
    public function reject(Request $request)
    {
        $request->validate([
            'acm_id' => 'required|string',
            'remarks' => 'nullable|string|max:1000'
        ]);

        try {
            $this->acmService->rejectMatrix($request->acm_id, $request->remarks, auth()->user());
            return redirect()->route('action-matrix.index')
                ->with('success', 'Action Matrix ' . $request->acm_id . ' has been sent back to the officer.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Store a comment (PO or PKSF).
     */
    public function storeComment(Request $request)
    {
        $request->validate([
            'acm_id' => 'required|string',
            'comment_detail' => 'required|string',
            'attachments.*' => 'nullable|file|max:5120' // 5MB max
        ]);

        try {
            $this->acmService->storeComment($request->all(), auth()->user());
            
            // If the user checked "forward to supervisor"
            if ($request->has('forward_to_supervisor') && $request->forward_to_supervisor == 1) {
                $this->acmService->forwardToPoSupervisor($request->acm_id, 'Auto-forwarded after commenting.', auth()->user());
                return redirect()->route('action-matrix.index')
                    ->with('success', 'Comment saved and forwarded to supervisor.');
            }

            return back()->with('success', 'Comment saved successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * PO Officer forwards to PO Supervisor.
     */
    public function forwardToPoSupervisor(Request $request)
    {
        $request->validate([
            'acm_id' => 'required|string',
            'remarks' => 'nullable|string'
        ]);

        try {
            $this->acmService->forwardToPoSupervisor($request->acm_id, $request->remarks, auth()->user());
            return redirect()->route('action-matrix.index')
                ->with('success', 'Forwarded to PO Supervisor successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * PO Supervisor approves and sends to PKSF.
     */
    public function approvePoResponse(Request $request)
    {
        $request->validate([
            'acm_id' => 'required|string',
            'remarks' => 'nullable|string'
        ]);

        try {
            $this->acmService->approvePoResponse($request->acm_id, $request->remarks, auth()->user());
            return redirect()->route('action-matrix.index')
                ->with('success', 'PO Response approved and sent to PKSF.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * PO Supervisor rejects and sends back to officer.
     */
    public function rejectPoResponse(Request $request)
    {
        $request->validate([
            'acm_id' => 'required|string',
            'remarks' => 'nullable|string'
        ]);

        try {
            $this->acmService->rejectPoResponse($request->acm_id, $request->remarks, auth()->user());
            return redirect()->route('action-matrix.index')
                ->with('success', 'Response sent back to officer for correction.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Fetch full history for a matrix.
     */
    public function getHistory($acm_id)
    {
        $master = \App\Models\AcmMaster::with(['comments.attachments', 'pksfMovements', 'poMovements'])
            ->where('acm_id', $acm_id)
            ->firstOrFail();

        // Build a unified history view
        return view('action_matrix.partials.history', compact('master'))->render();
    }
}
