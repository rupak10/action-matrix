@php
    $user         = auth()->user();
    $resStatus    = $obs->resolution_status;
    $isSupervisor = $user->isSupervisor();
    $isPksfUser   = $user->isPksf();
    $isPoUser     = $user->isPo();
    $visitStatus  = $visit->status;
    $isMyDesk     = $visit->current_desk_emp_id === $user->emp_id;

    // All observation actions require the visit to be on your desk
    $canMarkPending    = $isPksfUser && !$isSupervisor && $isMyDesk && $obs->isOpen()
                         && in_array($visitStatus, ['PO_APPROVED', 'SUBMITTED']);
    // SO: directly resolve an OPEN observation (only OPEN — PENDING_RESOLVED uses Accept/Reject below)
    $canResolve        = $isPksfUser && $isSupervisor && $isMyDesk && $obs->isOpen() && $visitStatus === 'PO_APPROVED';
    // SO: accept or reject a CO-marked pending resolution
    $canApprovePending = $isPksfUser && $isSupervisor && $isMyDesk && $obs->isPendingResolved();
    $canRejectPending  = $isPksfUser && $isSupervisor && $isMyDesk && $obs->isPendingResolved();
    $canReopen         = $isPksfUser && $isSupervisor && $isMyDesk && $obs->isResolved();
    $canEditObs   = $canAddObs || ($isPksfUser && $isSupervisor && $isMyDesk);
    $canDeleteObs = $canAddObs || ($isPksfUser && $isSupervisor && $isMyDesk && $visitStatus === 'SUBMITTED');
@endphp

<div class="obs-card" id="obs-card-{{ $obs->id }}">

    {{-- ── Card Header (always visible) ───────────────────────────────── --}}
    <div class="obs-card-head" id="obs-head-{{ $obs->id }}">
        @php
            $seqBg = match($resStatus) {
                'PENDING_RESOLVED' => '#d97706',
                'RESOLVED'         => '#16a34a',
                default            => 'var(--sl-primary)',
            };
        @endphp
        <div class="obs-seq" style="background:{{ $seqBg }}" title="{{ match($resStatus) {
            'PENDING_RESOLVED' => 'Pending Resolution',
            'RESOLVED'         => 'Resolved',
            default            => 'Open',
        } }}">{{ $idx }}</div>

        <div class="flex-grow-1 min-w-0">
            {{-- Status + meta badges --}}
            <div class="d-flex align-items-center flex-wrap gap-1">
                <span class="priority-pill priority-{{ $obs->priority }}">{{ $obs->priority }}</span>
                <span class="badge rounded-pill" style="background:#f1f5f9;color:#475569;font-size:.68rem">
                    {{ $obs->observation_category }}
                </span>
                @if($obs->action_matrix === 'Y')
                <span class="badge rounded-pill" style="background:#fef3c7;color:#92400e;font-size:.68rem">Action Matrix Needed</span>
                @endif
                @php $commentCount = $obs->comments->count(); @endphp
                @if($commentCount > 0)
                <span class="badge rounded-pill obs-comment-count" id="obs-comment-count-{{ $obs->id }}"
                      style="background:#eff6ff;color:#2563eb;font-size:.68rem;display:inline-flex;align-items:center;gap:.25rem">
                    <i class="bi bi-chat-text" style="font-size:.65rem"></i>{{ $commentCount }}
                </span>
                @else
                <span class="badge rounded-pill obs-comment-count" id="obs-comment-count-{{ $obs->id }}"
                      style="background:#f1f5f9;color:#94a3b8;font-size:.68rem;display:none;align-items:center;gap:.25rem">
                    <i class="bi bi-chat-text" style="font-size:.65rem"></i>0
                </span>
                @endif
            </div>
        </div>

        {{-- ── Action buttons (View / Mark Pending / Mark Resolve / Edit / Delete) ── --}}
        <div class="d-flex align-items-center gap-1 flex-shrink-0">
            <button class="btn btn-sm btn-view-obs" data-obs-id="{{ $obs->id }}" data-idx="{{ $idx }}"
                    title="View"
                    style="background:#eff6ff;color:#1d4ed8;border:1.5px solid #bfdbfe;border-radius:7px;font-size:.75rem;font-weight:600;padding:.3rem .65rem">
                <i class="bi bi-eye me-1"></i>View
            </button>
            @if($canMarkPending)
            <button class="btn btn-sm btn-co-resolve-obs" data-obs-id="{{ $obs->id }}" data-idx="{{ $idx }}"
                    title="Resolve"
                    style="background:#dcfce7;color:#15803d;border:1.5px solid #86efac;border-radius:7px;font-size:.75rem;font-weight:600;padding:.3rem .65rem">
                <i class="bi bi-check-circle me-1"></i>Resolve
            </button>
            @endif
            @if($canResolve)
            <button class="btn btn-sm btn-resolve-obs" data-obs-id="{{ $obs->id }}" data-idx="{{ $idx }}"
                    title="Mark Resolve"
                    style="background:#dcfce7;color:#15803d;border:1.5px solid #86efac;border-radius:7px;font-size:.75rem;font-weight:600;padding:.3rem .65rem">
                <i class="bi bi-check2-circle me-1"></i>Mark Resolve
            </button>
            @endif
            @if($canApprovePending)
            <button class="btn btn-sm btn-approve-pending" data-obs-id="{{ $obs->id }}"
                    title="Accept Resolution"
                    style="background:#dcfce7;color:#15803d;border:1.5px solid #86efac;border-radius:7px;font-size:.75rem;font-weight:600;padding:.3rem .65rem">
                <i class="bi bi-check-all me-1"></i>Accept
            </button>
            @endif
            @if($canRejectPending)
            <button class="btn btn-sm btn-reject-pending" data-obs-id="{{ $obs->id }}"
                    title="Reject Resolution"
                    style="background:#fff1f2;color:#e11d48;border:1.5px solid #fda4af;border-radius:7px;font-size:.75rem;font-weight:600;padding:.3rem .65rem">
                <i class="bi bi-x-circle me-1"></i>Reject
            </button>
            @endif
            @if($canReopen)
            <button class="btn btn-sm btn-reopen-obs" data-obs-id="{{ $obs->id }}"
                    title="Reopen Observation"
                    style="background:#f1f5f9;color:#475569;border:1.5px solid #cbd5e1;border-radius:7px;font-size:.75rem;font-weight:600;padding:.3rem .65rem">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Reopen
            </button>
            @endif
            @if($canEditObs)
            <button class="btn btn-sm btn-edit-obs" data-obs-id="{{ $obs->id }}"
                    title="Edit"
                    style="background:#f1f5f9;color:#475569;border:1.5px solid #cbd5e1;border-radius:7px;font-size:.75rem;font-weight:600;padding:.3rem .65rem">
                <i class="bi bi-pencil me-1"></i>Edit
            </button>
            @endif
            @if($canDeleteObs)
            <button class="btn btn-sm btn-delete-obs" data-obs-id="{{ $obs->id }}" data-idx="{{ $idx }}"
                    title="Delete"
                    style="background:#fff1f2;color:#e11d48;border:1.5px solid #fda4af;border-radius:7px;font-size:.75rem;font-weight:600;padding:.3rem .55rem">
                <i class="bi bi-trash3"></i>
            </button>
            @endif
        </div>

        {{-- Chevron for comment toggle --}}
        <i class="bi bi-chevron-down text-muted obs-toggle-icon" style="font-size:.85rem;flex-shrink:0;margin-left:.25rem;transition:transform .2s"></i>
    </div>

    {{-- ── Collapsible body: comments only ─────────────────────────────── --}}
    <div class="obs-body" style="display:none">
        <div class="comment-section">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="info-label">Comments <span class="text-muted fw-normal">({{ $obs->comments->count() }})</span></div>
                @if($canComment && !$obs->isResolved())
                <button class="btn btn-sm btn-add-comment" data-obs-id="{{ $obs->id }}"
                        style="background:var(--sl-primary-soft);color:var(--sl-primary);border-radius:7px;font-size:.75rem;font-weight:600;border:none;padding:.3rem .65rem">
                    <i class="bi bi-plus-lg me-1"></i>Add Comment
                </button>
                @endif
            </div>

            @if($obs->comments->isEmpty())
            <div class="text-muted text-center py-2" style="font-size:.8rem;border:1px dashed var(--sl-border);border-radius:8px">
                No comments yet
            </div>
            @else
            <div class="comment-thread">
                @foreach($obs->comments as $comment)
                @php
                    $author      = $usersByEmpId->get($comment->created_by);
                    $isMyComment = $comment->created_by === $user->emp_id;
                    $obsLocked   = $isPoUser && !$isSupervisor && $obs->isResolved();
                    $isEditable  = $canComment && $comment->isEditable() && $isMyComment && !$obsLocked;
                    $isDeletable = $canComment && ($isMyComment || $isSupervisor) && $comment->isEditable() && !$obsLocked;
                @endphp
                <div class="comment-bubble comment-{{ $comment->comment_source }}" id="comment-bubble-{{ $comment->id }}">
                    <div style="white-space:pre-wrap">{{ $comment->comment_detail }}</div>
                    @if($comment->attachments->count())
                    <div class="mt-2 d-flex flex-wrap gap-1">
                        @foreach($comment->attachments as $att)
                        <a href="{{ $att->download_url }}" target="_blank" class="att-chip">
                            <i class="bi bi-paperclip"></i>{{ $att->file_name }}
                        </a>
                        @endforeach
                    </div>
                    @endif
                    <div class="comment-meta">
                        <span>{{ $author?->name ?? $comment->created_by }}</span>
                        <span>&bull;</span>
                        <span>{{ $comment->comment_source }}</span>
                        <span>{{ \Carbon\Carbon::parse($comment->updated_at)->format('d M Y, h:i A') }}</span>
                        @if($isEditable || $isDeletable)
                        <span class="ms-auto d-flex gap-1">
                            @if($isEditable)
                            <button class="btn btn-sm btn-edit-comment"
                                    style="padding:.1rem .4rem;font-size:.7rem;border-radius:5px;background:#f1f5f9;border:none"
                                    data-comment-id="{{ $comment->id }}"
                                    data-obs-id="{{ $obs->id }}"
                                    data-comment-detail="{{ e($comment->comment_detail) }}"
                                    data-attachments="{{ json_encode($comment->attachments->map(fn($a) => ['id'=>$a->id,'file_name'=>$a->file_name,'file_size'=>$a->formatted_size,'url'=>$a->download_url])->values()) }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            @endif
                            @if($isDeletable)
                            <button class="btn btn-sm btn-delete-comment"
                                    style="padding:.1rem .4rem;font-size:.7rem;border-radius:5px;background:#fff1f2;color:#e11d48;border:none"
                                    data-comment-id="{{ $comment->id }}"
                                    data-obs-id="{{ $obs->id }}">
                                <i class="bi bi-trash3"></i>
                            </button>
                            @endif
                        </span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

</div>
