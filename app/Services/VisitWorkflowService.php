<?php

namespace App\Services;

use App\Models\Visit;
use App\Models\VisitMovement;
use App\Models\VisitRemark;
use App\Models\Observation;
use App\Models\ObservationComment;
use App\Models\User;
use App\Models\UserSupervisor;
use Illuminate\Support\Facades\DB;

class VisitWorkflowService
{
    public function __construct(
        private ObservationService $observationService,
        private AttachmentService  $attachmentService,
    ) {}

    // ── Visit creation ─────────────────────────────────────────────────────

    public function generateVisitCode(string $poCode): string
    {
        return DB::transaction(function () use ($poCode) {
            $tracker = DB::table('acm_visit_tracker')
                ->where('po_code', $poCode)
                ->lockForUpdate()
                ->first();

            if ($tracker) {
                $newSl = $tracker->sl + 1;
                DB::table('acm_visit_tracker')->where('po_code', $poCode)->update(['sl' => $newSl]);
            } else {
                $newSl = 1;
                DB::table('acm_visit_tracker')->insert(['po_code' => $poCode, 'sl' => $newSl]);
            }

            return 'V-' . strtoupper($poCode) . '-' . str_pad($newSl, 2, '0', STR_PAD_LEFT);
        });
    }

    public function createVisit(array $visitData, User $user): Visit
    {
        return DB::transaction(function () use ($visitData, $user) {
            $visitCode = $this->generateVisitCode($visitData['po_code']);

            $visit = Visit::create([
                'visit_code'           => $visitCode,
                'po_code'              => $visitData['po_code'],
                'visit_from_date'      => $visitData['visit_from_date'],
                'visit_to_date'        => $visitData['visit_to_date'],
                'visit_type'           => $visitData['visit_type'],
                'visit_category'       => $visitData['visit_category'] ?? null,
                'letter_issue_date'    => $visitData['letter_issue_date'] ?? null,
                'letter_response_date' => $visitData['letter_response_date'] ?? null,
                'observation_dept'     => $user->dept_name ?? null,
                'status'               => 'SAVED',
                'current_desk_emp_id'  => $user->emp_id,
                'created_by'           => $user->emp_id,
            ]);

            return $visit;
        });
    }

    public function updateVisit(int $visitId, array $data, User $user): Visit
    {
        $visit = Visit::findOrFail($visitId);

        $visit->update([
            'po_code'              => $data['po_code'] ?? $visit->po_code,
            'visit_from_date'      => $data['visit_from_date'],
            'visit_to_date'        => $data['visit_to_date'],
            'visit_type'           => $data['visit_type'],
            'visit_category'       => $data['visit_category'] ?? $visit->visit_category,
            'letter_issue_date'    => $data['letter_issue_date'] ?? null,
            'letter_response_date' => $data['letter_response_date'] ?? null,
            'updated_by'           => $user->emp_id,
        ]);

        return $visit->fresh();
    }

    // ── PKSF side transitions ──────────────────────────────────────────────

    public function forwardToSupervisor(int $visitId, ?string $remarks, User $user): void
    {
        DB::transaction(function () use ($visitId, $remarks, $user) {
            $visit = Visit::findOrFail($visitId);

            if (!in_array($visit->status, ['SAVED', 'REJECTED', 'PO_APPROVED'], true)) {
                throw new \Exception('Visit cannot be forwarded at this stage.');
            }

            // A visit with no observations has nothing for the supervisor to review.
            if ($visit->observations()->count() === 0) {
                throw new \Exception('Please add at least one observation before forwarding.');
            }

            $supervisorId = $this->resolvePrimarySupevisor($user->emp_id);
            if (!$supervisorId) {
                throw new \Exception('No supervisor configured for your account.');
            }

            // When forwarding at PO_APPROVED, keep the status — supervisor resolves observations at that stage.
            // For SAVED/REJECTED, advance to SUBMITTED.
            $newStatus = $visit->status === 'PO_APPROVED' ? 'PO_APPROVED' : 'SUBMITTED';

            $visit->update([
                'status'              => $newStatus,
                'current_desk_emp_id' => $supervisorId,
                'updated_by'          => $user->emp_id,
            ]);

            $this->logMovement($visit->id, 'PKSF', $user->emp_id, $supervisorId, 'FORWARDED_TO_SUPERVISOR', $remarks, $user->emp_id);
        });
    }

    public function sendToPo(int $visitId, ?string $remarks, array $files, User $user): void
    {
        DB::transaction(function () use ($visitId, $remarks, $files, $user) {
            $visit = Visit::findOrFail($visitId);

            if (!in_array($visit->status, ['SUBMITTED', 'PO_APPROVED'], true)) {
                throw new \Exception('Visit cannot be sent to PO at this stage.');
            }

            $poSupervisor = $this->resolvePoSupervisor($visit->po_code);

            // SENT_BACK_TO_PO when returning after review; SENT_TO_PO on first send
            $actionType = $visit->status === 'PO_APPROVED' ? 'SENT_BACK_TO_PO' : 'SENT_TO_PO';

            $visit->update([
                'status'              => 'PO_SO_REVIEW',
                'current_desk_emp_id' => $poSupervisor->emp_id,
                'updated_by'          => $user->emp_id,
            ]);

            $movement = $this->logMovement($visit->id, 'PKSF', $user->emp_id, $poSupervisor->emp_id, $actionType, null, $user->emp_id);

            // Store global visit-level remark if provided
            if ($remarks || !empty($files)) {
                $remark = VisitRemark::create([
                    'visit_id'    => $visit->id,
                    'movement_id' => $movement->id,
                    'remarks'     => $remarks ?? '',
                    'created_by'  => $user->emp_id,
                ]);

                if (!empty($files)) {
                    $this->attachmentService->storeRemarkAttachments($remark->id, $files, $user);
                }
            }

            // Lock all PKSF CO in-progress comments across all observations
            $this->lockCommentsBySource($visit->id, 'PKSF', $user->emp_id);
        });
    }

    public function rejectToPksfCo(int $visitId, ?string $remarks, User $user): void
    {
        DB::transaction(function () use ($visitId, $remarks, $user) {
            $visit = Visit::findOrFail($visitId);

            if ($visit->status !== 'SUBMITTED') {
                throw new \Exception('Only SUBMITTED visits can be rejected.');
            }

            $originalCo = $visit->created_by;

            $visit->update([
                'status'              => 'REJECTED',
                'current_desk_emp_id' => $originalCo,
                'updated_by'          => $user->emp_id,
            ]);

            $this->logMovement($visit->id, 'PKSF', $user->emp_id, $originalCo, 'REJECTED_TO_CO', $remarks, $user->emp_id);
        });
    }

    // ── PO side transitions ────────────────────────────────────────────────

    public function forwardToPoOfficer(int $visitId, ?string $remarks, User $user): void
    {
        DB::transaction(function () use ($visitId, $remarks, $user) {
            $visit = Visit::findOrFail($visitId);

            if ($visit->status !== 'PO_SO_REVIEW') {
                throw new \Exception('Visit must be in PO_SO_REVIEW state.');
            }

            $poOfficer = $this->resolvePoOfficer($visit->po_code);

            $visit->update([
                'status'              => 'PO_REVIEW',
                'current_desk_emp_id' => $poOfficer->emp_id,
                'updated_by'          => $user->emp_id,
            ]);

            $this->logMovement($visit->id, 'PO', $user->emp_id, $poOfficer->emp_id, 'FORWARDED_TO_PO_OFFICER', $remarks, $user->emp_id);
        });
    }

    public function submitToPoSupervisor(int $visitId, ?string $remarks, User $user): void
    {
        DB::transaction(function () use ($visitId, $remarks, $user) {
            $visit = Visit::findOrFail($visitId);

            if ($visit->status !== 'PO_REVIEW') {
                throw new \Exception('Visit must be in PO_REVIEW state.');
            }

            $supervisorId = $this->resolvePrimarySupevisor($user->emp_id);
            if (!$supervisorId) {
                throw new \Exception('No supervisor configured for your account.');
            }

            $visit->update([
                'status'              => 'PO_SUBMITTED',
                'current_desk_emp_id' => $supervisorId,
                'updated_by'          => $user->emp_id,
            ]);

            $this->logMovement($visit->id, 'PO', $user->emp_id, $supervisorId, 'SUBMITTED_TO_PO_SUPERVISOR', $remarks, $user->emp_id);
        });
    }

    public function approvePoResponse(int $visitId, ?string $remarks, User $user): void
    {
        DB::transaction(function () use ($visitId, $remarks, $user) {
            $visit = Visit::findOrFail($visitId);

            if ($visit->status !== 'PO_SUBMITTED') {
                throw new \Exception('Visit must be in PO_SUBMITTED state.');
            }

            // Lock all PO CO in-progress comments
            $this->lockCommentsBySource($visit->id, 'PO', $user->emp_id);

            $pksfCo = $this->resolvePksfCo($visit->po_code, $visit->created_by);

            $visit->update([
                'status'              => 'PO_APPROVED',
                'current_desk_emp_id' => $pksfCo,
                'updated_by'          => $user->emp_id,
            ]);

            $this->logMovement($visit->id, 'PO', $user->emp_id, $pksfCo, 'APPROVED_AND_SENT_TO_PKSF', $remarks, $user->emp_id);
        });
    }

    // ── Private helpers ────────────────────────────────────────────────────

    private function logMovement(int $visitId, string $side, string $from, string $to, string $action, ?string $remarks, string $by): VisitMovement
    {
        return VisitMovement::create([
            'visit_id'      => $visitId,
            'movement_side' => $side,
            'from_emp_id'   => $from,
            'to_emp_id'     => $to,
            'action_type'   => $action,
            'remarks'       => $remarks,
            'created_by'    => $by,
        ]);
    }

    private function lockCommentsBySource(int $visitId, string $source, string $updatedBy): void
    {
        $observationIds = Observation::where('visit_id', $visitId)->pluck('id');

        ObservationComment::whereIn('observation_id', $observationIds)
            ->where('comment_source', $source)
            ->where('is_draft', 0)
            ->update([
                'is_draft'   => 1,
                'updated_by' => $updatedBy,
                'updated_at' => now(),
            ]);
    }

    private function resolvePrimarySupevisor(string $empId): ?string
    {
        return UserSupervisor::where('emp_id', $empId)
            ->where('is_primary', true)
            ->value('supervisor_emp_id');
    }

    private function resolvePksfCo(string $poCode, string $fallbackEmpId): string
    {
        // Prefer the visit creator if they are still assigned as CO for this PO.
        // Avoids misrouting when multiple COs exist or test data is mixed.
        $creatorIsCo = DB::table('user_po_assignments')
            ->where('po_code', $poCode)
            ->where('emp_id', $fallbackEmpId)
            ->where('emp_role', 'CO')
            ->exists();

        if ($creatorIsCo) {
            return $fallbackEmpId;
        }

        // Creator is no longer the CO for this PO (transferred) — route to the current assigned CO.
        $empId = DB::table('user_po_assignments')
            ->where('po_code', $poCode)
            ->where('emp_role', 'CO')
            ->value('emp_id');

        return $empId ?? $fallbackEmpId;
    }

    private function resolvePoSupervisor(string $poCode): User
    {
        $supervisor = User::whereHas('roles', fn($q) => $q->where('name', 'PO_SUPERVISOR'))
            ->where('po_code', $poCode)
            ->first();

        if (!$supervisor) {
            throw new \Exception("No PO Supervisor found for PO code: {$poCode}");
        }

        return $supervisor;
    }

    private function resolvePoOfficer(string $poCode): User
    {
        $officer = User::whereHas('roles', fn($q) => $q->where('name', 'PO_CO'))
            ->where('po_code', $poCode)
            ->first();

        if (!$officer) {
            $officer = User::where('po_code', $poCode)->where('emp_type', 'PO')->first();
        }

        if (!$officer) {
            throw new \Exception("No PO Officer found for PO code: {$poCode}");
        }

        return $officer;
    }
}
