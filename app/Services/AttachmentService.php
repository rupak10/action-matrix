<?php

namespace App\Services;

use App\Models\ObservationAttachment;
use App\Models\ObservationCommentAttachment;
use App\Models\VisitRemarkAttachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AttachmentService
{
    const MAX_FILES      = 3;
    const MAX_SIZE_BYTES = 30 * 1024 * 1024; // 30 MB

    // ── Observation attachments ────────────────────────────────────────────

    public function storeObservationAttachments(int $observationId, array $files, User $user): array
    {
        $stored = [];
        foreach (array_slice($files, 0, self::MAX_FILES) as $file) {
            if (!$file instanceof UploadedFile) continue;
            $path = $this->store($file, "observation_attachments/{$observationId}");
            $stored[] = ObservationAttachment::create([
                'observation_id' => $observationId,
                'file_name'      => $file->getClientOriginalName(),
                'file_path'      => $path,
                'file_type'      => $file->getMimeType(),
                'file_size'      => $file->getSize(),
                'created_by'     => $user->emp_id,
            ]);
        }
        return $stored;
    }

    public function deleteObservationAttachment(int $attachmentId): void
    {
        $att = ObservationAttachment::findOrFail($attachmentId);
        Storage::disk('public')->delete($att->file_path);
        $att->delete();
    }

    public function deleteAllObservationAttachments(int $observationId): void
    {
        ObservationAttachment::where('observation_id', $observationId)->each(function ($att) {
            Storage::disk('public')->delete($att->file_path);
            $att->delete();
        });
    }

    // ── Comment attachments ────────────────────────────────────────────────

    public function storeCommentAttachments(int $commentId, array $files, User $user): array
    {
        $stored = [];
        foreach (array_slice($files, 0, self::MAX_FILES) as $file) {
            if (!$file instanceof UploadedFile) continue;
            $path = $this->store($file, "comment_attachments/{$commentId}");
            $stored[] = ObservationCommentAttachment::create([
                'comment_id' => $commentId,
                'file_name'  => $file->getClientOriginalName(),
                'file_path'  => $path,
                'file_type'  => $file->getMimeType(),
                'file_size'  => $file->getSize(),
                'created_by' => $user->emp_id,
            ]);
        }
        return $stored;
    }

    public function deleteCommentAttachment(int $attachmentId): void
    {
        $att = ObservationCommentAttachment::findOrFail($attachmentId);
        Storage::disk('public')->delete($att->file_path);
        $att->delete();
    }

    public function deleteAllCommentAttachments(int $commentId): void
    {
        ObservationCommentAttachment::where('comment_id', $commentId)->each(function ($att) {
            Storage::disk('public')->delete($att->file_path);
            $att->delete();
        });
    }

    public function deleteCommentAttachmentsByIds(array $ids): void
    {
        ObservationCommentAttachment::whereIn('id', $ids)->each(function ($att) {
            Storage::disk('public')->delete($att->file_path);
            $att->delete();
        });
    }

    // ── Visit remark attachments ───────────────────────────────────────────

    public function storeRemarkAttachments(int $remarkId, array $files, User $user): array
    {
        $stored = [];
        foreach (array_slice($files, 0, self::MAX_FILES) as $file) {
            if (!$file instanceof UploadedFile) continue;
            $path = $this->store($file, "remark_attachments/{$remarkId}");
            $stored[] = VisitRemarkAttachment::create([
                'remark_id'  => $remarkId,
                'file_name'  => $file->getClientOriginalName(),
                'file_path'  => $path,
                'file_type'  => $file->getMimeType(),
                'file_size'  => $file->getSize(),
                'created_by' => $user->emp_id,
            ]);
        }
        return $stored;
    }

    public function deleteRemarkAttachment(int $attachmentId): void
    {
        $att = VisitRemarkAttachment::findOrFail($attachmentId);
        Storage::disk('public')->delete($att->file_path);
        $att->delete();
    }

    // ── Private helpers ────────────────────────────────────────────────────

    private function store(UploadedFile $file, string $directory): string
    {
        $name = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs($directory, $name, 'public');
    }
}
