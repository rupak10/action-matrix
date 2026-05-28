<?php

namespace App\Http\Controllers;

use App\Services\ActionMatrixService;
use Illuminate\Http\Request;

class ActionMatrixController extends Controller
{
    protected $acmService;

    public function __construct(ActionMatrixService $acmService)
    {
        $this->acmService = $acmService;
    }

    /**
     * Display a listing of the resource.
     * Data for the table is fetched server-side via getData() (AJAX).
     */
    public function index()
    {
        $user = auth()->user();

        // Stat cards — lightweight counts only, no full collection load
        $stats = $this->acmService->getStatCounts($user);

        // Fetch all PO Concern Officers (role 'PO_CO') for modal assignment
        $poOfficers = \App\Models\User::whereHas('roles', function ($q) {
            $q->where('name', 'PO_CO');
        })->get();

        // Filter options for the dropdowns
        $formOptions = $this->getFormOptions();

        // Empty collections — table data now comes via AJAX (getData)
        $matrices            = collect();
        $incomingAssignments = collect();
        $usersByEmpId        = collect();

        return view('action_matrix.index', compact(
            'stats', 'poOfficers', 'formOptions',
            'matrices', 'incomingAssignments', 'usersByEmpId'
        ));
    }

    /**
     * Server-side DataTables AJAX endpoint.
     */
    public function getData(Request $request)
    {
        $user = auth()->user();

        $dtParams = [
            'draw'   => $request->input('draw', 1),
            'start'  => $request->input('start', 0),
            'length' => $request->input('length', 25),
            'search' => $request->input('search', ['value' => '']),
            'order'  => $request->input('order', [['column' => 0, 'dir' => 'desc']]),
        ];

        $filters = [
            'view'     => $request->input('view', 'all'),
            'po_code'  => $request->input('po_code', ''),
            'priority' => $request->input('priority', ''),
        ];

        $result = $this->acmService->getMatricesTableData($dtParams, $filters, $user);

        return response()->json($result);
    }

    /**
     * Show the form for creating a new Action Matrix.
     */
    public function create()
    {
        if (!auth()->user()->isPksf()) {
            abort(403, 'Only PKSF users can create an action matrix.');
        }

        $formOptions = $this->getFormOptions();

        return view('action_matrix.create', $formOptions);
    }

    /**
     * Store a newly created Action Matrix in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->isPksf()) {
            abort(403, 'Only PKSF users can create an action matrix.');
        }

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
            'attachments.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,txt|max:30720',
        ], [
            'attachments.max' => 'You can upload a maximum of 3 files.',
            'attachments.*.mimes' => 'Only PDF, Word, Excel, Text, and Image files are allowed.',
            'attachments.*.max' => 'Each file must not exceed 30MB.',
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
        $user = auth()->user();

        $master = \App\Models\AcmMaster::with([
            'attachments',
            'comments',
            'pksfMovements',
            'poMovements',
        ])->where('acm_id', $id)->firstOrFail();

        $isAdmin = $user->hasAnyRole(['Super_Admin', 'Super Admin', 'Admin']);
        $canView = $isAdmin
            || $master->created_by === $user->emp_id
            || $master->current_desk_emp_id === $user->emp_id;

        abort_unless($canView, 403, 'Unauthorized access to this Action Matrix.');

        $commentAttachments = \App\Models\AcmCommentAttachment::where('acm_id', $master->acm_id)
            ->orderBy('sl')
            ->orderBy('file_id')
            ->get()
            ->groupBy('sl');

        $movements = collect($master->pksfMovements)
            ->map(fn ($movement) => [
                'source' => 'PKSF',
                'sl' => $movement->sl,
                'from_emp_id' => $movement->from_emp_id,
                'to_emp_id' => $movement->to_emp_id,
                'action_type' => $movement->action_type,
                'remarks' => $movement->remarks,
                'created_at' => $movement->created_at,
            ])
            ->concat(collect($master->poMovements)->map(fn ($movement) => [
                'source' => 'PO',
                'sl' => $movement->sl,
                'from_emp_id' => $movement->from_emp_id,
                'to_emp_id' => $movement->to_emp_id,
                'action_type' => $movement->action_type,
                'remarks' => $movement->remarks,
                'created_at' => $movement->created_at,
            ]))
            ->sortBy('created_at')
            ->values();

        $employeeIds = collect([
                $master->created_by,
                $master->updated_by,
                $master->current_desk_emp_id,
            ])
            ->concat($master->comments->pluck('created_by'))
            ->concat($movements->pluck('from_emp_id'))
            ->concat($movements->pluck('to_emp_id'))
            ->filter()
            ->unique()
            ->values();

        $usersByEmpId = \App\Models\User::whereIn('emp_id', $employeeIds)
            ->get()
            ->keyBy('emp_id');

        $incomingAssignment = $this->latestIncomingMovement($master);
        $movementHistory = $user->isPo()
            ? $movements->where('source', 'PO')->values()
            : $movements;

        return view('action_matrix.show', compact(
            'master',
            'commentAttachments',
            'movements',
            'movementHistory',
            'usersByEmpId',
            'incomingAssignment'
        ));
    }

    /**
     * Show the form for editing an Action Matrix before supervisor review.
     */
    public function edit($id)
    {
        $master = \App\Models\AcmMaster::with('attachments')
            ->where('acm_id', $id)
            ->firstOrFail();

        abort_unless($this->canEditMatrix($master), 403, 'This Action Matrix cannot be edited at this stage.');

        return view('action_matrix.edit', array_merge(
            ['master' => $master],
            $this->getFormOptions()
        ));
    }

    /**
     * Update an editable Action Matrix.
     */
    public function update(Request $request, $id)
    {
        $master = \App\Models\AcmMaster::with('attachments')
            ->where('acm_id', $id)
            ->firstOrFail();

        abort_unless($this->canEditMatrix($master), 403, 'This Action Matrix cannot be edited at this stage.');

        $validated = $request->validate([
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
            'remove_attachments' => 'nullable|array',
            'remove_attachments.*' => 'integer',
            'attachments' => 'nullable|array|max:3',
            'attachments.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,txt|max:30720',
        ], [
            'attachments.max' => 'You can upload a maximum of 3 files.',
            'attachments.*.mimes' => 'Only PDF, Word, Excel, Text, and Image files are allowed.',
            'attachments.*.max' => 'Each file must not exceed 30MB.',
        ]);

        try {
            $this->acmService->updateMaster(
                $master->acm_id,
                $validated,
                $request->file('attachments') ?? [],
                $request->input('remove_attachments', []),
                auth()->user()
            );

            return redirect()->route('action-matrix.index')
                ->with('success', 'Action Matrix ' . $master->acm_id . ' has been updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to update Action Matrix: ' . $e->getMessage());
        }
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
     * Store or update a PO comment draft.
     * If comment_sl is present → update existing draft row.
     * If comment_sl is absent  → insert new comment for this round.
     */
    public function storeComment(Request $request)
    {
        $request->validate([
            'acm_id'          => 'required|string',
            'comment_detail'  => 'required|string',
            'attachments.*'   => 'nullable|file|max:30720', // 30 MB per file
            'remove_file_ids' => 'nullable|array',
            'remove_file_ids.*' => 'integer',
        ]);

        try {
            $data = $request->all();
            $data['attachments']     = $request->file('attachments') ?? [];
            $data['remove_file_ids'] = $request->input('remove_file_ids', []);

            if ($request->filled('comment_sl')) {
                // Editing an existing draft for the current round
                $this->acmService->updateComment($data, auth()->user());
            } else {
                // First save for this round — insert new row
                $this->acmService->storeComment($data, auth()->user());
            }

            if ($request->has('forward_to_supervisor') && $request->forward_to_supervisor == 1) {
                $this->acmService->forwardToPoSupervisor(
                    $request->acm_id,
                    'Submitted to supervisor.',
                    auth()->user()
                );
                return redirect()->route('action-matrix.index')
                    ->with('success', 'Response submitted and forwarded to supervisor.');
            }

            return back()->with('success', 'Draft saved successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Return the current user's is_draft=1 comment for a matrix (for pre-populating the modal).
     * Also self-heals any legacy comment (is_draft=0) that belongs to the current round —
     * i.e. the matrix is still sitting at this user's desk in an editable status.
     */
    public function getMyDraft(string $acmId)
    {
        $user  = auth()->user();
        $acmId = trim($acmId);

        // Self-heal: if a comment by this user exists with is_draft=0 but the matrix is
        // still at their desk (saved before the is_draft column was introduced), mark it.
        \Illuminate\Support\Facades\DB::table('acm_comments')
            ->join('acm_master', 'acm_comments.acm_id', '=', 'acm_master.acm_id')
            ->where('acm_comments.acm_id', $acmId)
            ->where('acm_comments.created_by', $user->emp_id)
            ->where('acm_comments.is_draft', 0)
            ->whereIn('acm_master.status', ['PO_REVIEW', 'PO_REJECTED'])
            ->whereColumn('acm_master.current_desk_emp_id', 'acm_comments.created_by')
            ->update(['acm_comments.is_draft' => 1]);

        $draft = \Illuminate\Support\Facades\DB::table('acm_comments')
            ->where('acm_id', $acmId)
            ->where('created_by', $user->emp_id)
            ->where('is_draft', 1)
            ->first();

        if (!$draft) {
            return response()->json(null);
        }

        $attachments = \Illuminate\Support\Facades\DB::table('acm_comments_file_attachment')
            ->where('acm_id', trim($acmId))
            ->where('sl', $draft->sl)
            ->get()
            ->map(fn($a) => [
                'file_id'   => $a->file_id,
                'file_name' => $a->file_name,
                'file_size' => $a->file_size,
                'file_type' => $a->file_type,
            ])
            ->values()
            ->toArray();

        return response()->json([
            'sl'             => $draft->sl,
            'comment_detail' => $draft->comment_detail,
            'attachments'    => $attachments,
        ]);
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

    /**
     * PKSF Officer requests closure.
     */
    public function requestClosure(Request $request)
    {
        $request->validate([
            'acm_id' => 'required|string',
            'remarks' => 'nullable|string|max:1000'
        ]);

        try {
            $this->acmService->requestClosure($request->acm_id, $request->remarks, auth()->user());
            return redirect()->route('action-matrix.index')
                ->with('success', 'Closure request submitted to your supervisor.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * PKSF Officer requests revision from PO (sends to PKSF Supervisor).
     */
    public function requestRevision(Request $request)
    {
        $request->validate([
            'acm_id' => 'required|string',
            'comment_detail' => 'required|string',
            'attachments.*' => 'nullable|file|max:30720'
        ]);

        try {
            $this->acmService->requestRevision($request->all(), auth()->user());
            return redirect()->route('action-matrix.index')
                ->with('success', 'Revision request submitted to your supervisor.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * PKSF Supervisor approves closure.
     */
    public function approveClosure(Request $request)
    {
        $request->validate([
            'acm_id' => 'required|string',
            'remarks' => 'nullable|string|max:1000'
        ]);

        try {
            $this->acmService->approveClosure($request->acm_id, $request->remarks, auth()->user());
            return redirect()->route('action-matrix.index')
                ->with('success', 'Action Matrix closed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * PKSF Supervisor rejects closure.
     */
    public function rejectClosure(Request $request)
    {
        $request->validate([
            'acm_id' => 'required|string',
            'remarks' => 'nullable|string|max:1000'
        ]);

        try {
            $this->acmService->rejectClosure($request->acm_id, $request->remarks, auth()->user());
            return redirect()->route('action-matrix.index')
                ->with('success', 'Closure request rejected and sent back to officer.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * PKSF Supervisor approves revision.
     */
    public function approveRevision(Request $request)
    {
        $request->validate([
            'acm_id' => 'required|string',
            'remarks' => 'nullable|string|max:1000'
        ]);

        try {
            $this->acmService->approveRevision($request->acm_id, $request->remarks, auth()->user());
            return redirect()->route('action-matrix.index')
                ->with('success', 'Revision request approved and forwarded to PO.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * PKSF Supervisor rejects revision request.
     */
    public function rejectRevision(Request $request)
    {
        $request->validate([
            'acm_id' => 'required|string',
            'remarks' => 'nullable|string|max:1000'
        ]);

        try {
            $this->acmService->rejectRevision($request->acm_id, $request->remarks, auth()->user());
            return redirect()->route('action-matrix.index')
                ->with('success', 'Revision request rejected and sent back to officer.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    protected function canEditMatrix(\App\Models\AcmMaster $master): bool
    {
        $user = auth()->user();

        return $user
            && $user->isPksf()
            && $master->created_by === $user->emp_id
            && $master->current_desk_emp_id === $user->emp_id
            && in_array($master->status, ['SAVED', 'REJECTED'], true);
    }

    protected function getFormOptions(): array
    {
        return [
            'poList' => [
                ['code' => '001', 'name' => 'PO One'],
                ['code' => '007', 'name' => 'PO Seven'],
                ['code' => '010', 'name' => 'PO Ten'],
            ],
            'categories' => ['FINANCIAL', 'OPERATIONAL', 'COMPLIANCE', 'GOVERNANCE'],
            'visitTypes' => ['ONSITE', 'OFFSITE'],
            'visitCategories' => ['REGULAR VISIT', 'MANAGEMENT AUDIT', 'SPECIAL AUDIT'],
            'priorities' => ['LOW', 'MEDIUM', 'HIGH'],
        ];
    }

    protected function latestIncomingMovement(\App\Models\AcmMaster $master): ?array
    {
        if (!$master->current_desk_emp_id) {
            return null;
        }

        return collect($master->pksfMovements)
            ->map(fn ($movement) => $this->movementToArray($movement, 'PKSF'))
            ->concat(collect($master->poMovements)->map(fn ($movement) => $this->movementToArray($movement, 'PO')))
            ->filter(fn ($movement) => $movement['to_emp_id'] === $master->current_desk_emp_id)
            ->sortByDesc('created_at')
            ->first();
    }

    protected function movementToArray($movement, string $source): array
    {
        return [
            'source' => $source,
            'sl' => $movement->sl,
            'from_emp_id' => $movement->from_emp_id,
            'to_emp_id' => $movement->to_emp_id,
            'action_type' => $movement->action_type,
            'remarks' => $movement->remarks,
            'created_at' => $movement->created_at,
        ];
    }
}
