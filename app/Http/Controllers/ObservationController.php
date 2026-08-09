<?php

namespace App\Http\Controllers;

use App\Models\Observation;
use App\Models\Visit;
use App\Services\ObservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ObservationController extends Controller
{
    public function __construct(private ObservationService $service) {}

    public function store(Request $request, int $visitId)
    {
        $visit = Visit::findOrFail($visitId);
        $user  = auth()->user();

        abort_unless($visit->isEditableByPksfCo(), 403, 'Visit is locked and cannot accept new observations.');
        abort_unless($user->isPksf(), 403, 'Only PKSF users can add observations.');

        $validated = $request->validate([
            'observation_category' => 'required|string|max:255',
            'pksf_observation'     => 'required|string',
            'direction_to_po'      => 'required|string',
            'priority'      => 'nullable|in:LOW,MEDIUM,HIGH',
            'action_matrix' => 'nullable|in:Y,N',
            'attachments'   => 'nullable|array|max:3',
            'attachments.*'        => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:30720',
        ]);

        try {
            $observation = $this->service->addObservation(
                $visitId,
                $validated,
                $request->file('attachments') ?? [],
                $user
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success'     => true,
                    'message'     => 'Observation added.',
                    'observation' => $this->formatObservation($observation),
                ]);
            }

            return back()->with('success', 'Observation added successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function editData(Request $request, int $visitId, int $observationId)
    {
        $observation = Observation::with('attachments')->findOrFail($observationId);

        abort_unless($observation->visit_id === $visitId, 404);

        return response()->json([
            'observation' => $observation,
            'attachments' => $observation->attachments->map(fn($a) => [
                'id'        => $a->id,
                'file_name' => $a->file_name,
                'file_size' => $a->formatted_size,
                'url'       => $a->download_url,
            ]),
        ]);
    }

    public function update(Request $request, int $visitId, int $observationId)
    {
        $visit       = Visit::findOrFail($visitId);
        $observation = Observation::findOrFail($observationId);
        $user        = auth()->user();

        abort_unless($observation->visit_id === $visitId, 404);
        abort_unless($visit->isEditableByPksfCo() || $user->isSupervisor(), 403, 'Visit is locked.');
        abort_unless($user->isPksf(), 403, 'Only PKSF users can edit observations.');

        $validated = $request->validate([
            'observation_category'  => 'required|string|max:255',
            'pksf_observation'      => 'required|string',
            'direction_to_po'       => 'required|string',
            'priority'      => 'nullable|in:LOW,MEDIUM,HIGH',
            'action_matrix' => 'nullable|in:Y,N',
            'attachments'   => 'nullable|array',
            'attachments.*'         => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:30720',
            'remove_attachments'    => 'nullable|array',
            'remove_attachments.*'  => 'integer',
        ]);

        try {
            $updated = $this->service->updateObservation(
                $observationId,
                $validated,
                $request->file('attachments') ?? [],
                $request->input('remove_attachments') ?? [],
                $user
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success'     => true,
                    'message'     => 'Observation updated.',
                    'observation' => $this->formatObservation($updated),
                ]);
            }

            return back()->with('success', 'Observation updated.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Request $request, int $visitId, int $observationId)
    {
        $visit       = Visit::findOrFail($visitId);
        $observation = Observation::findOrFail($observationId);
        $user        = auth()->user();

        abort_unless($observation->visit_id === $visitId, 404);
        abort_unless($user->isPksf(), 403, 'Only PKSF users can delete observations.');

        // CO can delete when visit is SAVED/REJECTED; supervisor can also delete during SUBMITTED review
        $canDelete = $visit->isEditableByPksfCo()
            || ($user->isSupervisor() && $visit->status === 'SUBMITTED');
        abort_unless($canDelete, 403, 'You cannot delete observations at this stage.');

        try {
            $this->service->deleteObservation($observationId, $user);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Observation deleted.']);
            }

            return back()->with('success', 'Observation deleted.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function markPendingResolved(Request $request, int $observationId)
    {
        $observation = Observation::findOrFail($observationId);
        $visit       = $observation->visit;
        $user        = auth()->user();

        abort_unless($user->isPksf(), 403);
        abort_unless(in_array($visit->status, ['PO_APPROVED', 'SUBMITTED'], true), 403, 'Visit is not at a stage where observations can be marked pending resolved.');

        try {
            $this->service->markPendingResolved($observationId, $user);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'resolution_status' => 'PENDING_RESOLVED']);
            }
            return back()->with('success', 'Observation marked as pending resolution.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function resolve(Request $request, int $observationId)
    {
        $observation = Observation::findOrFail($observationId);
        $user        = auth()->user();

        abort_unless($user->isPksf() && $user->isSupervisor(), 403, 'Only PKSF supervisors can directly resolve observations.');

        try {
            $this->service->resolveObservation($observationId, $user);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'resolution_status' => 'RESOLVED']);
            }
            return back()->with('success', 'Observation resolved.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function approvePending(Request $request, int $observationId)
    {
        $observation = Observation::findOrFail($observationId);
        $user        = auth()->user();

        abort_unless($user->isPksf() && $user->isSupervisor(), 403, 'Only PKSF supervisors can approve pending resolutions.');

        try {
            $this->service->approvePendingResolved($observationId, $user);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'resolution_status' => 'RESOLVED']);
            }
            return back()->with('success', 'Observation approved and resolved.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function reopen(Request $request, int $observationId)
    {
        $observation = Observation::findOrFail($observationId);
        $user        = auth()->user();

        abort_unless($user->isPksf() && $user->isSupervisor(), 403, 'Only PKSF supervisors can reopen observations.');

        try {
            $this->service->reopenObservation($observationId, $user);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'resolution_status' => 'OPEN']);
            }
            return back()->with('success', 'Observation reopened.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    private function formatObservation(Observation $obs): array
    {
        return [
            'id'                   => $obs->id,
            'observation_category' => $obs->observation_category,
            'pksf_observation'     => $obs->pksf_observation,
            'direction_to_po'      => $obs->direction_to_po,
            'priority'          => $obs->priority,
            'action_matrix'     => $obs->action_matrix,
            'resolution_status' => $obs->resolution_status,
            'created_by'           => $obs->created_by,
            'attachments'          => ($obs->relationLoaded('attachments') ? $obs->attachments : collect())->map(fn($a) => [
                'id'        => $a->id,
                'file_name' => $a->file_name,
                'file_size' => $a->formatted_size,
                'url'       => $a->download_url,
            ])->values()->toArray(),
        ];
    }
}
