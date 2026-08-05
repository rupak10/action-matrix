<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Models\User;
use App\Services\VisitWorkflowService;
use App\Services\VisitQueryService;
use App\Services\ObservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitController extends Controller
{
    public function __construct(
        private VisitWorkflowService $workflowService,
        private VisitQueryService    $queryService,
        private ObservationService   $observationService,
    ) {}

    // ── List & DataTables ──────────────────────────────────────────────────

    public function index()
    {
        $user      = auth()->user();
        $stats     = $this->queryService->getStatCounts($user);
        $formOptions = $this->getFormOptions();
        $poList    = $this->getPoList();

        return view('visits.index', compact('stats', 'formOptions', 'poList'));
    }

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
            'view'       => $request->input('view', 'all'),
            'po_code'    => $request->input('po_code', ''),
            'visit_type' => $request->input('visit_type', ''),
            'status'     => $request->input('status', ''),
        ];

        return response()->json($this->queryService->getVisitsTableData($dtParams, $filters, $user));
    }

    // ── Create ─────────────────────────────────────────────────────────────

    public function create()
    {
        $user = auth()->user();
        abort_unless($user->isPksf(), 403, 'Only PKSF users can create visits.');

        return view('visits.create', [
            'formOptions' => $this->getFormOptions(),
            'poList'      => $this->getPoList(),
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        abort_unless($user->isPksf(), 403, 'Only PKSF users can create visits.');

        $validated = $request->validate([
            'po_code'              => 'required|string|max:5',
            'visit_from_date'      => 'required|date',
            'visit_to_date'        => 'required|date|after_or_equal:visit_from_date',
            'visit_type'           => 'required|in:ONSITE,OFFSITE',
            'visit_category'       => 'required|string|max:100',
            'letter_issue_date'    => 'nullable|date',
            'letter_response_date' => 'nullable|date',
        ]);

        try {
            $visit = $this->workflowService->createVisit($validated, $user);

            return redirect()->route('visits.show', $visit->id)
                ->with('success', "Visit {$visit->visit_code} created. Now add your observations.");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ── Show ───────────────────────────────────────────────────────────────

    public function show(int $id)
    {
        $user  = auth()->user();
        $visit = Visit::with([
            'observations.attachments',
            'observations.comments.attachments',
            'movements.fromUser',
            'movements.toUser',
            'remarks.attachments',
            'remarks.author',
            'smComments.commenter',
            'poInfo',
        ])->findOrFail($id);

        abort_unless($this->canView($visit, $user), 403, 'You do not have access to this visit.');

        // Filter comment visibility per observation
        foreach ($visit->observations as $obs) {
            $filtered = $this->observationService->getVisibleComments($obs->comments, $user, $visit->status);
            $obs->setRelation('comments', $filtered);
        }

        $resolutionSummary = $visit->getResolutionSummary();

        // Resolve employee name map
        $empIds = collect([$visit->created_by, $visit->current_desk_emp_id]);
        foreach ($visit->movements as $m) {
            $empIds->push($m->from_emp_id)->push($m->to_emp_id);
        }
        foreach ($visit->observations as $obs) {
            $empIds->push($obs->created_by);
            foreach ($obs->comments as $c) $empIds->push($c->created_by);
        }
        $usersByEmpId = User::whereIn('emp_id', $empIds->filter()->unique()->values())
            ->get()->keyBy('emp_id');

        $formOptions = $this->getFormOptions();
        $poList      = $this->getPoList();

        return view('visits.show', compact(
            'visit', 'resolutionSummary', 'usersByEmpId', 'formOptions', 'poList'
        ));
    }

    // ── Edit ───────────────────────────────────────────────────────────────

    public function edit(int $id)
    {
        $user  = auth()->user();
        $visit = Visit::findOrFail($id);

        abort_unless(
            $visit->isEditableByPksfCo() && $visit->created_by === $user->emp_id,
            403, 'This visit cannot be edited at this stage.'
        );

        return view('visits.edit', [
            'visit'       => $visit,
            'formOptions' => $this->getFormOptions(),
            'poList'      => $this->getPoList(),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $user  = auth()->user();
        $visit = Visit::findOrFail($id);

        abort_unless(
            $visit->isEditableByPksfCo() && $visit->created_by === $user->emp_id,
            403, 'This visit cannot be edited at this stage.'
        );

        $validated = $request->validate([
            'visit_from_date'      => 'required|date',
            'visit_to_date'        => 'required|date|after_or_equal:visit_from_date',
            'visit_type'           => 'required|in:ONSITE,OFFSITE',
            'visit_category'       => 'required|string|max:100',
            'letter_issue_date'    => 'nullable|date',
            'letter_response_date' => 'nullable|date',
        ]);

        try {
            $this->workflowService->updateVisit($id, $validated, $user);
            return redirect()->route('visits.show', $id)->with('success', 'Visit updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ── Workflow transitions ───────────────────────────────────────────────

    public function forward(Request $request, int $id)
    {
        $request->validate(['remarks' => 'nullable|string|max:1000']);

        try {
            $this->workflowService->forwardToSupervisor($id, $request->remarks, auth()->user());
            return redirect()->route('visits.index')->with('success', 'Visit forwarded to supervisor.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function sendToPo(Request $request, int $id)
    {
        $request->validate([
            'remarks'      => 'nullable|string|max:2000',
            'attachments'  => 'nullable|array|max:3',
            'attachments.*'=> 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:30720',
        ]);

        try {
            $this->workflowService->sendToPo(
                $id,
                $request->remarks,
                $request->file('attachments') ?? [],
                auth()->user()
            );
            return redirect()->route('visits.index')->with('success', 'Visit sent to PO Supervisor.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, int $id)
    {
        $request->validate(['remarks' => 'nullable|string|max:1000']);

        try {
            $this->workflowService->rejectToPksfCo($id, $request->remarks, auth()->user());
            return redirect()->route('visits.index')->with('success', 'Visit rejected and sent back to officer.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function forwardToPoOfficer(Request $request, int $id)
    {
        $request->validate(['remarks' => 'nullable|string|max:1000']);

        try {
            $this->workflowService->forwardToPoOfficer($id, $request->remarks, auth()->user());
            return redirect()->route('visits.index')->with('success', 'Visit forwarded to PO Officer.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function submitToPoSupervisor(Request $request, int $id)
    {
        $request->validate(['remarks' => 'nullable|string|max:1000']);

        try {
            $this->workflowService->submitToPoSupervisor($id, $request->remarks, auth()->user());
            return redirect()->route('visits.index')->with('success', 'Response submitted to PO Supervisor.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approvePoResponse(Request $request, int $id)
    {
        $request->validate(['remarks' => 'nullable|string|max:1000']);

        try {
            $this->workflowService->approvePoResponse($id, $request->remarks, auth()->user());
            return redirect()->route('visits.index')->with('success', 'PO response approved and sent to PKSF.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Request $request, int $id)
    {
        $visit = Visit::with([
            'observations.attachments',
            'observations.comments.attachments',
        ])->findOrFail($id);

        $user = auth()->user();

        abort_unless($visit->status === 'SAVED', 403, 'Only unsent visits can be deleted.');

        $hasPo = DB::table('user_po_assignments')
            ->where('emp_id', $user->emp_id)
            ->where('po_code', $visit->po_code)
            ->exists();

        abort_unless($user->isPksf() && $hasPo, 403, 'You are not assigned to this PO.');

        DB::transaction(function () use ($visit) {
            foreach ($visit->observations as $obs) {
                foreach ($obs->comments as $comment) {
                    $this->observationService->deleteComment($comment->id, auth()->user());
                }
                $this->observationService->deleteObservation($obs->id, auth()->user());
            }
            $visit->delete();
        });

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'redirect' => route('visits.index')]);
        }

        return redirect()->route('visits.index')->with('success', "Visit {$visit->visit_code} deleted.");
    }

    public function getHistory(int $id)
    {
        $visit = Visit::with([
            'movements.fromUser',
            'movements.toUser',
            'remarks.attachments',
            'remarks.author',
        ])->findOrFail($id);

        return view('visits.partials.visit_timeline', compact('visit'))->render();
    }

    // ── Authorization ──────────────────────────────────────────────────────

    private function canView(Visit $visit, User $user): bool
    {
        if ($user->hasAnyRole(['Super_Admin', 'Super Admin', 'Admin'])) return true;
        if ($visit->created_by === $user->emp_id)           return true;
        if ($visit->current_desk_emp_id === $user->emp_id)  return true;

        $isSupervisor = DB::table('user_supervisors')
            ->where('supervisor_emp_id', $user->emp_id)->exists();

        if ($user->isPksf() && $isSupervisor) return true;

        if ($user->isPo() && $user->po_code && $visit->po_code === $user->po_code
            && !in_array($visit->status, ['SAVED'], true)) {
            return true;
        }

        if ($user->isSeniorManagement()) {
            if ($user->isSmMd()) return !in_array($visit->status, ['SAVED']);
            return in_array($visit->po_code, $user->assignedPoCodes())
                && !in_array($visit->status, ['SAVED']);
        }

        return false;
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function getFormOptions(): array
    {
        return [
            'visitTypes'  => ['ONSITE', 'OFFSITE'],
            'categories'  => ['FINANCIAL', 'OPERATIONAL', 'COMPLIANCE', 'GOVERNANCE'],
            'priorities'  => ['LOW', 'MEDIUM', 'HIGH'],
            'statuses'    => ['SAVED', 'SUBMITTED', 'REJECTED', 'PO_SO_REVIEW', 'PO_REVIEW', 'PO_SUBMITTED', 'PO_APPROVED'],
        ];
    }

    private function getPoList(): array
    {
        $empId = auth()->user()->emp_id;

        return DB::table('po_info')
            ->join('user_po_assignments', 'po_info.po_code', '=', 'user_po_assignments.po_code')
            ->where('user_po_assignments.emp_id', $empId)
            ->where('po_info.is_active', 'Y')
            ->orderBy('po_info.po_name')
            ->get(['po_info.po_code', 'po_info.po_name', 'po_info.po_short_name'])
            ->toArray();
    }
}
