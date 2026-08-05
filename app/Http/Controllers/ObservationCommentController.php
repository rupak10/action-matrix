<?php

namespace App\Http\Controllers;

use App\Models\Observation;
use App\Models\ObservationComment;
use App\Models\Visit;
use App\Services\ObservationService;
use Illuminate\Http\Request;

class ObservationCommentController extends Controller
{
    public function __construct(private ObservationService $service) {}

    public function store(Request $request, int $observationId)
    {
        $observation = Observation::findOrFail($observationId);
        $visit       = $observation->visit;
        $user        = auth()->user();

        $this->authorizeComment($visit, $user);

        $validated = $request->validate([
            'comment_detail' => 'required|string|max:5000',
            'attachments'    => 'nullable|array|max:3',
            'attachments.*'  => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:30720',
        ]);

        try {
            $comment = $this->service->storeComment(
                $observationId,
                $validated,
                $request->file('attachments') ?? [],
                $user
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Comment saved.',
                    'comment' => $this->formatComment($comment),
                ]);
            }

            return back()->with('success', 'Comment saved.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, int $observationId, int $commentId)
    {
        $comment = ObservationComment::findOrFail($commentId);
        $user    = auth()->user();

        abort_unless($comment->observation_id === $observationId, 404);
        abort_unless($comment->created_by === $user->emp_id, 403, 'You can only edit your own comments.');
        abort_unless($comment->isEditable(), 403, 'This comment has been finalized and cannot be edited.');
        if ($user->isPo() && !$user->isSupervisor()) {
            abort_unless(!$comment->observation->isResolved(), 403, 'Comments on a resolved observation cannot be edited.');
        }

        $validated = $request->validate([
            'comment_detail'        => 'required|string|max:5000',
            'attachments'           => 'nullable|array',
            'attachments.*'         => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:30720',
            'remove_attachments'    => 'nullable|array',
            'remove_attachments.*'  => 'integer',
        ]);

        try {
            $updated = $this->service->updateComment(
                $commentId,
                $validated,
                $request->file('attachments') ?? [],
                $request->input('remove_attachments') ?? [],
                $user
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Comment updated.',
                    'comment' => $this->formatComment($updated),
                ]);
            }

            return back()->with('success', 'Comment updated.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Request $request, int $observationId, int $commentId)
    {
        $comment = ObservationComment::findOrFail($commentId);
        $user    = auth()->user();

        abort_unless($comment->observation_id === $observationId, 404);
        abort_unless($comment->created_by === $user->emp_id || $user->isSupervisor(), 403, 'Unauthorized.');
        abort_unless($comment->isEditable(), 403, 'This comment has been finalized and cannot be deleted.');
        if ($user->isPo() && !$user->isSupervisor()) {
            abort_unless(!$comment->observation->isResolved(), 403, 'Comments on a resolved observation cannot be deleted.');
        }

        try {
            $this->service->deleteComment($commentId, $user);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Comment deleted.']);
            }

            return back()->with('success', 'Comment deleted.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function getMyDraft(Request $request, int $observationId)
    {
        $user  = auth()->user();
        $draft = $this->service->getMyDraft($observationId, $user);

        return response()->json(['draft' => $draft]);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function authorizeComment(Visit $visit, $user): void
    {
        // Anyone can comment only when the visit is on their desk
        abort_unless($visit->current_desk_emp_id === $user->emp_id, 403, 'You cannot comment on this visit at this stage.');
    }

    private function formatComment(ObservationComment $comment): array
    {
        $author = \App\Models\User::where('emp_id', $comment->created_by)
            ->value('name');

        return [
            'id'             => $comment->id,
            'observation_id' => $comment->observation_id,
            'comment_detail' => $comment->comment_detail,
            'comment_source' => $comment->comment_source,
            'is_draft'       => $comment->is_draft,
            'is_editable'    => $comment->isEditable(),
            'created_by'     => $comment->created_by,
            'author_name'    => $author ?? $comment->created_by,
            'updated_at'     => $comment->updated_at?->format('d M Y, h:i A'),
            'attachments'    => ($comment->relationLoaded('attachments') ? $comment->attachments : collect())->map(fn($a) => [
                'id'        => $a->id,
                'file_name' => $a->file_name,
                'file_size' => $a->formatted_size,
                'url'       => $a->download_url,
            ])->values()->toArray(),
        ];
    }
}
