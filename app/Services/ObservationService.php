<?php

namespace App\Services;

use App\Models\Observation;
use App\Models\ObservationComment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ObservationService
{
    public function __construct(private AttachmentService $attachmentService) {}

    // ── Observation CRUD ───────────────────────────────────────────────────

    public function addObservation(int $visitId, array $data, array $files, User $user): Observation
    {
        return DB::transaction(function () use ($visitId, $data, $files, $user) {
            $observation = Observation::create([
                'visit_id'             => $visitId,
                'observation_category' => $data['observation_category'],
                'pksf_observation'     => $data['pksf_observation'],
                'direction_to_po'      => $data['direction_to_po'],
                'priority'          => $data['priority'] ?? 'MEDIUM',
                'action_matrix'     => $data['action_matrix'] ?? 'N',
                'resolution_status' => 'OPEN',
                'created_by'           => $user->emp_id,
            ]);

            if (!empty($files)) {
                $this->attachmentService->storeObservationAttachments($observation->id, $files, $user);
            }

            return $observation->load('attachments');
        });
    }

    public function updateObservation(int $observationId, array $data, array $newFiles, array $removeAttachmentIds, User $user): Observation
    {
        return DB::transaction(function () use ($observationId, $data, $newFiles, $removeAttachmentIds, $user) {
            $observation = Observation::findOrFail($observationId);

            $observation->update([
                'observation_category' => $data['observation_category'],
                'pksf_observation'     => $data['pksf_observation'],
                'direction_to_po'      => $data['direction_to_po'],
                'priority'      => $data['priority'] ?? $observation->priority,
                'action_matrix' => $data['action_matrix'] ?? $observation->action_matrix,
                'updated_by'    => $user->emp_id,
            ]);

            if (!empty($removeAttachmentIds)) {
                foreach ($removeAttachmentIds as $attId) {
                    $this->attachmentService->deleteObservationAttachment((int) $attId);
                }
            }

            $existingCount = $observation->attachments()->count();
            $allowedNew    = max(0, AttachmentService::MAX_FILES - $existingCount);
            if (!empty($newFiles) && $allowedNew > 0) {
                $this->attachmentService->storeObservationAttachments(
                    $observation->id,
                    array_slice($newFiles, 0, $allowedNew),
                    $user
                );
            }

            return $observation->fresh('attachments');
        });
    }

    public function deleteObservation(int $observationId, User $user): void
    {
        DB::transaction(function () use ($observationId, $user) {
            $observation = Observation::with('comments.attachments')->findOrFail($observationId);

            // Delete all comment attachments
            foreach ($observation->comments as $comment) {
                $this->attachmentService->deleteAllCommentAttachments($comment->id);
            }

            // Delete observation attachments
            $this->attachmentService->deleteAllObservationAttachments($observation->id);

            // Cascade deletes comments via FK; delete the observation
            $observation->delete();
        });
    }

    // ── Resolution state machine ───────────────────────────────────────────

    public function markPendingResolved(int $observationId, User $user): void
    {
        $observation = Observation::findOrFail($observationId);

        if (!$observation->isOpen()) {
            throw new \Exception('Only OPEN observations can be marked as pending resolution.');
        }

        $observation->update([
            'resolution_status' => 'PENDING_RESOLVED',
            'updated_by'        => $user->emp_id,
        ]);
    }

    public function resolveObservation(int $observationId, User $user): void
    {
        $observation = Observation::findOrFail($observationId);

        $observation->update([
            'resolution_status' => 'RESOLVED',
            'updated_by'        => $user->emp_id,
        ]);
    }

    public function approvePendingResolved(int $observationId, User $user): void
    {
        $observation = Observation::findOrFail($observationId);

        if (!$observation->isPendingResolved()) {
            throw new \Exception('Observation is not in PENDING_RESOLVED state.');
        }

        $observation->update([
            'resolution_status' => 'RESOLVED',
            'updated_by'        => $user->emp_id,
        ]);
    }

    public function reopenObservation(int $observationId, User $user): void
    {
        $observation = Observation::findOrFail($observationId);

        $observation->update([
            'resolution_status' => 'OPEN',
            'updated_by'        => $user->emp_id,
        ]);
    }

    // ── Comment management ─────────────────────────────────────────────────

    public function storeComment(int $observationId, array $data, array $files, User $user): ObservationComment
    {
        return DB::transaction(function () use ($observationId, $data, $files, $user) {
            $comment = ObservationComment::create([
                'observation_id' => $observationId,
                'comment_source' => strtoupper($user->emp_type ?? 'PKSF'),
                'comment_detail' => $data['comment_detail'],
                'is_draft'       => 0,
                'created_by'     => $user->emp_id,
            ]);

            if (!empty($files)) {
                $this->attachmentService->storeCommentAttachments($comment->id, $files, $user);
            }

            return $comment->load('attachments');
        });
    }

    public function updateComment(int $commentId, array $data, array $newFiles, array $removeAttachmentIds, User $user): ObservationComment
    {
        return DB::transaction(function () use ($commentId, $data, $newFiles, $removeAttachmentIds, $user) {
            $comment = ObservationComment::findOrFail($commentId);

            $comment->update([
                'comment_detail' => $data['comment_detail'],
                'updated_by'     => $user->emp_id,
            ]);

            if (!empty($removeAttachmentIds)) {
                $this->attachmentService->deleteCommentAttachmentsByIds($removeAttachmentIds);
            }

            $existingCount = $comment->attachments()->count();
            $allowedNew    = max(0, AttachmentService::MAX_FILES - $existingCount);
            if (!empty($newFiles) && $allowedNew > 0) {
                $this->attachmentService->storeCommentAttachments(
                    $comment->id,
                    array_slice($newFiles, 0, $allowedNew),
                    $user
                );
            }

            return $comment->fresh('attachments');
        });
    }

    public function deleteComment(int $commentId, User $user): void
    {
        DB::transaction(function () use ($commentId, $user) {
            $comment = ObservationComment::findOrFail($commentId);
            $this->attachmentService->deleteAllCommentAttachments($comment->id);
            $comment->delete();
        });
    }

    public function getMyDraft(int $observationId, User $user): ?array
    {
        $comment = ObservationComment::with('attachments')
            ->where('observation_id', $observationId)
            ->where('created_by', $user->emp_id)
            ->where('is_draft', 0)
            ->orderByDesc('updated_at')
            ->first();

        if (!$comment) return null;

        return [
            'id'             => $comment->id,
            'comment_detail' => $comment->comment_detail,
            'attachments'    => $comment->attachments->map(fn($a) => [
                'id'        => $a->id,
                'file_name' => $a->file_name,
                'file_size' => $a->formatted_size,
                'url'       => $a->download_url,
            ])->values()->toArray(),
        ];
    }

    public function getVisibleComments($comments, User $user, string $visitStatus)
    {
        if ($user->hasAnyRole(['Super_Admin', 'Super Admin', 'Admin'])) {
            return $comments;
        }

        $isSupervisor = $user->isSupervisor();

        return $comments->filter(function ($comment) use ($user, $visitStatus, $isSupervisor) {
            // Finalized (locked when PO SO approves) — visible to all sides
            if ((int) $comment->is_draft === 1) return true;

            // Author always sees their own draft
            if ($comment->created_by === $user->emp_id) return true;

            // PO Supervisor sees PO CO draft when the visit is submitted to them
            if ($isSupervisor && $user->isPo() && $comment->comment_source === 'PO' && $visitStatus === 'PO_SUBMITTED') {
                return true;
            }

            // PKSF Supervisor sees PKSF CO draft when on PKSF side
            if ($isSupervisor && $user->isPksf() && $comment->comment_source === 'PKSF') {
                return true;
            }

            return false;
        })->values();
    }
}
