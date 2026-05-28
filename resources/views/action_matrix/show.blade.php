@extends('layouts.app')

@section('content')
@php
    $isPoUser = auth()->user()->isPo();
    $statusClass = match($master->status) {
        'SUBMITTED', 'PO_REVIEW' => 'primary',
        'APPROVED', 'PO_APPROVED', 'CLOSED' => 'success',
        'REJECTED', 'PO_REJECTED', 'PKSF_REJECTED' => 'danger',
        'SAVED', 'DRAFT', 'PO_SUBMITTED', 'WAITING_FOR_CLOSURE', 'REVISION_REQUESTED' => 'warning',
        default => 'secondary',
    };

    $priorityClass = match($master->priority) {
        'HIGH' => 'danger',
        'MEDIUM' => 'warning',
        'LOW' => 'info',
        default => 'secondary',
    };

    $formatDate = fn ($date, $format = 'd M Y') => $date ? \Carbon\Carbon::parse($date)->format($format) : 'Not set';
    $formatDateTime = fn ($date) => $date ? \Carbon\Carbon::parse($date)->format('d M Y, h:i A') : 'Not set';
    $formatBytes = function ($bytes) {
        if (!$bytes) {
            return 'Size unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = min((int) floor(log(max($bytes, 1), 1024)), count($units) - 1);

        return round($bytes / (1024 ** $power), 2) . ' ' . $units[$power];
    };
    $userLabel = function ($empId) use ($usersByEmpId) {
        if (!$empId) {
            return 'Not assigned';
        }

        $user = $usersByEmpId->get($empId);

        return $user ? $user->name . ' (' . $empId . ')' : $empId;
    };
    $initials = function ($empId) use ($usersByEmpId) {
        $user = $usersByEmpId->get($empId);
        $name = $user->name ?? $empId ?? 'NA';
        $parts = preg_split('/\s+/', trim($name));

        return strtoupper(substr($parts[0] ?? 'N', 0, 1) . substr($parts[1] ?? '', 0, 1));
    };
@endphp

<div class="container-fluid action-detail-page py-2">
    <div class="detail-hero mb-4">
        <div class="d-flex flex-column flex-xl-row justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                    <span class="detail-kicker">Action Matrix Detail</span>
                    <span class="badge rounded-pill bg-{{ $statusClass }} px-3 py-2">{{ $master->status ?? 'UNKNOWN' }}</span>
                    <span class="badge rounded-pill bg-{{ $priorityClass }} px-3 py-2">{{ $master->priority ?? 'N/A' }} PRIORITY</span>
                </div>
                <h1 class="detail-title mb-2">{{ $master->acm_id }}</h1>
                <div class="detail-subtitle">
                    PO Code <strong>{{ $master->po_code }}</strong>
                    <span class="detail-dot"></span>
                    Visit Date <strong>{{ $formatDate($master->visiting_date) }}</strong>
                    <span class="detail-dot"></span>
                    Resolution <strong>{{ $master->resolution_status ?? 'N/A' }}</strong>
                </div>
            </div>

            <div class="detail-actions">
                <a href="{{ route('action-matrix.index') }}" class="btn btn-light border px-4 rounded-pill shadow-sm">
                    <i class="bi bi-arrow-left me-2"></i>Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <section class="detail-section h-100">
                <div class="section-heading">
                    <div>
                        <h2>Matrix Summary</h2>
                        <p>Core tracking information for this observation.</p>
                    </div>
                    <i class="bi bi-clipboard2-data"></i>
                </div>

                <div class="summary-grid">
                    <div class="summary-item">
                        <span>Observation Department</span>
                        <strong>{{ $master->observation_dept ?? 'N/A' }}</strong>
                    </div>
                    <div class="summary-item">
                        <span>Observation Category</span>
                        <strong>{{ $master->observation_category ?? 'N/A' }}</strong>
                    </div>
                    <div class="summary-item">
                        <span>Visit Type</span>
                        <strong>{{ $master->visit_type ?? 'N/A' }}</strong>
                    </div>
                    <div class="summary-item">
                        <span>Visit Category</span>
                        <strong>{{ $master->visit_category ?? 'N/A' }}</strong>
                    </div>
                    <div class="summary-item">
                        <span>Letter Issue Date</span>
                        <strong>{{ $formatDate($master->letter_issue_date) }}</strong>
                    </div>
                    <div class="summary-item">
                        <span>Letter Response Date</span>
                        <strong>{{ $formatDate($master->letter_response_date) }}</strong>
                    </div>
                    <div class="summary-item">
                        <span>Action Matrix Required</span>
                        <strong>{{ $master->action_matrix === 'Y' ? 'Yes' : 'No' }}</strong>
                    </div>
                    <div class="summary-item">
                        <span>PO Editable</span>
                        <strong>{{ $master->is_editable_by_po === 'Y' ? 'Yes' : 'No' }}</strong>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-lg-4">
            <section class="detail-section h-100">
                <div class="section-heading">
                    <div>
                        <h2>Desk Control</h2>
                        <p>Ownership and routing position.</p>
                    </div>
                    <i class="bi bi-person-lines-fill"></i>
                </div>

                @if($incomingAssignment)
                    <div class="assignment-card mb-3">
                        <div class="assignment-topline">
                            <span class="assignment-label">Current Assignment</span>
                            <span class="assignment-source assignment-source-{{ strtolower($incomingAssignment['source']) }}">{{ $incomingAssignment['source'] }}</span>
                        </div>

                        <div class="assignment-route">
                            <div class="assignment-person">
                                <span>Incoming From</span>
                                <strong>{{ $userLabel($incomingAssignment['from_emp_id']) }}</strong>
                            </div>
                            <div class="assignment-arrow">
                                <i class="bi bi-arrow-right"></i>
                            </div>
                            <div class="assignment-person">
                                <span>Current Desk</span>
                                <strong>{{ $userLabel($incomingAssignment['to_emp_id']) }}</strong>
                            </div>
                        </div>

                        <div class="assignment-meta">
                            @unless($isPoUser)
                                <span><i class="bi bi-send-check"></i>{{ \Illuminate\Support\Str::headline($incomingAssignment['action_type']) }}</span>
                            @endunless
                            <span><i class="bi bi-calendar-event"></i>{{ $formatDateTime($incomingAssignment['created_at']) }}</span>
                        </div>

                        @if($incomingAssignment['remarks'])
                            <div class="assignment-remarks">
                                <span>Remarks</span>
                                <p>{{ $incomingAssignment['remarks'] }}</p>
                            </div>
                        @endif
                    </div>
                @elseif($isPoUser)
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <span>No current assignment information is available.</span>
                    </div>
                @endif

                @unless($isPoUser)
                    <div class="desk-list">
                        <div class="desk-row">
                            <span>Created By</span>
                            <strong>{{ $userLabel($master->created_by) }}</strong>
                        </div>
                        <div class="desk-row">
                            <span>Current Desk</span>
                            <strong>{{ $userLabel($master->current_desk_emp_id) }}</strong>
                        </div>
                        <div class="desk-row">
                            <span>Created At</span>
                            <strong>{{ $formatDateTime($master->created_at) }}</strong>
                        </div>
                        <div class="desk-row">
                            <span>Last Updated</span>
                            <strong>{{ $formatDateTime($master->updated_at) }}</strong>
                        </div>
                    </div>
                @endunless
            </section>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <section class="detail-section h-100">
                <div class="section-heading">
                    <div>
                        <h2>Observation Details</h2>
                        <p>Original PKSF observation and instruction to PO.</p>
                    </div>
                    <i class="bi bi-journal-text"></i>
                </div>

                <div class="text-block mb-3">
                    <span>PKSF Observation</span>
                    <p>{{ $master->pksf_observation }}</p>
                </div>

                <div class="text-block">
                    <span>Direction to PO</span>
                    <p>{{ $master->direction_to_po }}</p>
                </div>
            </section>
        </div>

        <div class="col-xl-5">
            <section class="detail-section h-100">
                <div class="section-heading">
                    <div>
                        <h2>Original Attachments</h2>
                        <p>Files uploaded with the initial matrix.</p>
                    </div>
                    <i class="bi bi-paperclip"></i>
                </div>

                <div class="file-list">
                    @forelse($master->attachments as $attachment)
                        <a class="file-row" href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" rel="noopener">
                            <span class="file-icon"><i class="bi bi-file-earmark-arrow-down"></i></span>
                            <span class="file-meta">
                                <strong>{{ $attachment->file_name ?? 'Attachment ' . $attachment->sl }}</strong>
                                <small>{{ $attachment->file_type ?? 'Unknown type' }} · {{ $formatBytes($attachment->file_size) }}</small>
                            </span>
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                    @empty
                        <div class="empty-state">
                            <i class="bi bi-folder2-open"></i>
                            <span>No original attachments uploaded.</span>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <section class="detail-section">
                <div class="section-heading">
                    <div>
                        <h2>Comments & Responses</h2>
                        <p>Chronological discussion from PKSF and PO users.</p>
                    </div>
                    <span class="count-pill">{{ $master->comments->count() }}</span>
                </div>

                <div class="comment-timeline">
                    @forelse($master->comments as $comment)
                        @php
                            $attachments = $commentAttachments->get($comment->sl, collect());
                            $sourceClass = $comment->comment_source === 'PO' ? 'warning' : 'primary';
                        @endphp
                        <article class="comment-item">
                            <div class="avatar avatar-{{ $sourceClass }}">{{ $initials($comment->created_by) }}</div>
                            <div class="comment-body">
                                <div class="comment-topline">
                                    <div>
                                        <strong>{{ $userLabel($comment->created_by) }}</strong>
                                        <span class="badge rounded-pill bg-{{ $sourceClass }} ms-2">{{ $comment->comment_source }}</span>
                                        @if($comment->comment_short)
                                            <span class="badge rounded-pill bg-light text-dark border ms-1">{{ $comment->comment_short }}</span>
                                        @endif
                                    </div>
                                    <time>{{ $formatDateTime($comment->created_at) }}</time>
                                </div>
                                <p>{{ $comment->comment_detail }}</p>

                                @if($attachments->isNotEmpty())
                                    <div class="comment-files">
                                        @foreach($attachments as $attachment)
                                            <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" rel="noopener">
                                                <i class="bi bi-paperclip"></i>{{ $attachment->file_name }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="empty-state">
                            <i class="bi bi-chat-left-text"></i>
                            <span>No comments or responses have been added yet.</span>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="col-xl-5">
            <section class="detail-section">
                <div class="section-heading">
                    <div>
                        <h2>Movement History</h2>
                        <p>{{ $isPoUser ? 'PO-side audit trail of approvals, forwards, and rejections.' : 'Audit trail of approvals, forwards, and rejections.' }}</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="count-pill">{{ $movementHistory->count() }}</span>
                        <button class="collapse-toggle"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#movementHistoryCollapse"
                                aria-expanded="true"
                                aria-controls="movementHistoryCollapse"
                                title="Toggle movement history">
                            <i class="bi bi-chevron-up"></i>
                        </button>
                    </div>
                </div>

                <div class="collapse show" id="movementHistoryCollapse">
                    <div class="movement-list">
                        @forelse($movementHistory as $movement)
                            <article class="movement-item">
                                <div class="movement-marker {{ $movement['source'] === 'PO' ? 'movement-po' : 'movement-pksf' }}"></div>
                                <div class="movement-content">
                                    <div class="movement-head">
                                        <strong>{{ \Illuminate\Support\Str::headline($movement['action_type']) }}</strong>
                                        <span>{{ $movement['source'] }}</span>
                                    </div>
                                    <div class="movement-route">
                                        <span>{{ $userLabel($movement['from_emp_id']) }}</span>
                                        <i class="bi bi-arrow-right"></i>
                                        <span>{{ $userLabel($movement['to_emp_id']) }}</span>
                                    </div>
                                    <time>{{ $formatDateTime($movement['created_at']) }}</time>
                                    @if($movement['remarks'])
                                        <p>{{ $movement['remarks'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="empty-state">
                                <i class="bi bi-clock-history"></i>
                                <span>No movement history has been recorded yet.</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const movementCollapse = document.getElementById('movementHistoryCollapse');
        const movementToggleIcon = document.querySelector('[data-bs-target="#movementHistoryCollapse"] i');

        movementCollapse?.addEventListener('show.bs.collapse', () => {
            movementToggleIcon?.classList.remove('bi-chevron-down');
            movementToggleIcon?.classList.add('bi-chevron-up');
        });

        movementCollapse?.addEventListener('hide.bs.collapse', () => {
            movementToggleIcon?.classList.remove('bi-chevron-up');
            movementToggleIcon?.classList.add('bi-chevron-down');
        });
    });
</script>
@endpush

<style>
    .action-detail-page {
        color: #1c2929;
    }

    .detail-hero,
    .detail-section {
        background: #ffffff;
        border: 1px solid #e4ecec;
        box-shadow: 0 14px 36px rgba(27, 58, 58, 0.07);
    }

    .detail-hero {
        border-radius: 16px;
        padding: 28px;
    }

    .detail-kicker {
        color: #607070;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .detail-title {
        color: #173434;
        font-size: clamp(2rem, 4vw, 3.35rem);
        font-weight: 800;
        letter-spacing: 0;
        line-height: 1;
    }

    .detail-subtitle {
        color: #667676;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }

    .detail-subtitle strong {
        color: #173434;
    }

    .detail-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: #9aa8a8;
        display: inline-block;
    }

    .detail-actions {
        display: flex;
        align-items: flex-start;
        justify-content: flex-end;
    }

    .detail-section {
        border-radius: 14px;
        padding: 24px;
    }

    .section-heading {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        border-bottom: 1px solid #eef3f3;
        padding-bottom: 18px;
        margin-bottom: 20px;
    }

    .section-heading h2 {
        color: #173434;
        font-size: 1rem;
        font-weight: 800;
        margin: 0 0 4px;
        letter-spacing: 0;
    }

    .section-heading p {
        color: #738080;
        font-size: 0.84rem;
        margin: 0;
    }

    .section-heading > i {
        color: #1b3a3a;
        background: rgba(27, 58, 58, 0.08);
        border-radius: 10px;
        font-size: 1.2rem;
        padding: 10px 12px;
    }

    .assignment-card {
        background: linear-gradient(135deg, #f4fbf8 0%, #eef7fb 100%);
        border: 1px solid #cfe2dc;
        border-radius: 14px;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.75);
        padding: 16px;
    }

    .assignment-topline,
    .assignment-meta,
    .assignment-route {
        display: flex;
        gap: 10px;
    }

    .assignment-topline {
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
    }

    .assignment-label {
        color: #1b3a3a;
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .assignment-source {
        border-radius: 999px;
        color: #ffffff;
        font-size: 0.7rem;
        font-weight: 900;
        padding: 5px 9px;
    }

    .assignment-source-pksf {
        background: #1b3a3a;
    }

    .assignment-source-po {
        background: #b7791f;
    }

    .assignment-route {
        align-items: stretch;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
        margin-bottom: 12px;
    }

    .assignment-person {
        background: rgba(255, 255, 255, 0.74);
        border: 1px solid rgba(207, 226, 220, 0.9);
        border-radius: 10px;
        min-width: 0;
        padding: 11px;
    }

    .assignment-person span,
    .assignment-remarks span {
        color: #667676;
        display: block;
        font-size: 0.68rem;
        font-weight: 900;
        letter-spacing: 0.04em;
        margin-bottom: 5px;
        text-transform: uppercase;
    }

    .assignment-person strong {
        color: #172f2f;
        display: block;
        font-size: 0.83rem;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .assignment-arrow {
        align-items: center;
        color: #1b3a3a;
        display: inline-flex;
        justify-content: center;
    }

    .assignment-meta {
        flex-wrap: wrap;
        margin-bottom: 12px;
    }

    .assignment-meta span {
        align-items: center;
        background: #ffffff;
        border: 1px solid #d9e7e4;
        border-radius: 999px;
        color: #4f6262;
        display: inline-flex;
        font-size: 0.76rem;
        font-weight: 700;
        gap: 6px;
        padding: 7px 10px;
    }

    .assignment-remarks {
        background: rgba(255, 255, 255, 0.78);
        border-left: 3px solid #1b3a3a;
        border-radius: 0 10px 10px 0;
        padding: 11px 12px;
    }

    .assignment-remarks p {
        color: #304141;
        font-size: 0.84rem;
        line-height: 1.5;
        margin: 0;
        white-space: pre-line;
    }

    .collapse-toggle {
        align-items: center;
        background: #ffffff;
        border: 1px solid #dfe8e8;
        border-radius: 50%;
        color: #1b3a3a;
        display: inline-flex;
        height: 36px;
        justify-content: center;
        padding: 0;
        transition: background 0.15s ease, border-color 0.15s ease;
        width: 36px;
    }

    .collapse-toggle:hover {
        background: #edf4f4;
        border-color: #b9cccc;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .summary-item,
    .desk-row {
        background: #f7fafa;
        border: 1px solid #edf2f2;
        border-radius: 10px;
        padding: 14px;
    }

    .summary-item span,
    .desk-row span,
    .text-block span {
        color: #718080;
        display: block;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        margin-bottom: 7px;
        text-transform: uppercase;
    }

    .summary-item strong,
    .desk-row strong {
        color: #1c2929;
        display: block;
        font-size: 0.94rem;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .desk-list {
        display: grid;
        gap: 12px;
    }

    .text-block {
        border-left: 4px solid #1b3a3a;
        background: #f8fbfb;
        border-radius: 0 10px 10px 0;
        padding: 16px 18px;
    }

    .text-block p {
        color: #243232;
        font-size: 0.96rem;
        line-height: 1.65;
        margin: 0;
        white-space: pre-line;
    }

    .file-list {
        display: grid;
        gap: 12px;
    }

    .file-row {
        align-items: center;
        background: #f8fbfb;
        border: 1px solid #e9f0f0;
        border-radius: 10px;
        color: #1d3030;
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 12px;
        padding: 14px;
        text-decoration: none;
        transition: transform 0.15s ease, border-color 0.15s ease, background 0.15s ease;
    }

    .file-row:hover {
        background: #ffffff;
        border-color: #b9cccc;
        color: #1b3a3a;
        transform: translateY(-1px);
    }

    .file-icon {
        align-items: center;
        background: #e8f1f1;
        border-radius: 10px;
        color: #1b3a3a;
        display: inline-flex;
        height: 42px;
        justify-content: center;
        width: 42px;
    }

    .file-meta {
        min-width: 0;
    }

    .file-meta strong,
    .file-meta small {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .file-meta small {
        color: #708080;
        margin-top: 3px;
    }

    .empty-state {
        align-items: center;
        background: #f8fbfb;
        border: 1px dashed #cfdcdd;
        border-radius: 12px;
        color: #6d7b7b;
        display: flex;
        gap: 10px;
        justify-content: center;
        min-height: 96px;
        padding: 18px;
        text-align: center;
    }

    .empty-state i {
        color: #1b3a3a;
        font-size: 1.35rem;
    }

    .count-pill {
        background: #edf4f4;
        border-radius: 999px;
        color: #1b3a3a;
        font-size: 0.82rem;
        font-weight: 800;
        min-width: 38px;
        padding: 8px 12px;
        text-align: center;
    }

    .comment-timeline,
    .movement-list {
        display: grid;
        gap: 16px;
    }

    .comment-item {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 14px;
    }

    .avatar {
        align-items: center;
        border-radius: 50%;
        color: #fff;
        display: inline-flex;
        flex: 0 0 auto;
        font-size: 0.78rem;
        font-weight: 800;
        height: 42px;
        justify-content: center;
        margin-top: 2px;
        width: 42px;
    }

    .avatar-primary {
        background: #1b3a3a;
    }

    .avatar-warning {
        background: #b7791f;
    }

    .comment-body {
        background: #f8fbfb;
        border: 1px solid #e7eeee;
        border-radius: 12px;
        padding: 16px;
        min-width: 0;
    }

    .comment-topline {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
    }

    .comment-topline strong {
        color: #1d3030;
        overflow-wrap: anywhere;
    }

    .comment-topline time,
    .movement-content time {
        color: #758383;
        font-size: 0.78rem;
        white-space: nowrap;
    }

    .comment-body p {
        color: #263737;
        line-height: 1.6;
        margin: 0;
        white-space: pre-line;
    }

    .comment-files {
        border-top: 1px solid #e7eeee;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
        padding-top: 12px;
    }

    .comment-files a {
        background: #ffffff;
        border: 1px solid #dfe8e8;
        border-radius: 999px;
        color: #1b3a3a;
        font-size: 0.8rem;
        padding: 7px 11px;
        text-decoration: none;
    }

    .comment-files i {
        margin-right: 5px;
    }

    .movement-item {
        display: grid;
        grid-template-columns: 14px 1fr;
        gap: 12px;
    }

    .movement-marker {
        border-radius: 999px;
        margin-top: 7px;
        min-height: 100%;
        width: 6px;
    }

    .movement-pksf {
        background: #1b3a3a;
    }

    .movement-po {
        background: #b7791f;
    }

    .movement-content {
        background: #f8fbfb;
        border: 1px solid #e7eeee;
        border-radius: 12px;
        padding: 14px 16px;
        min-width: 0;
    }

    .movement-head,
    .movement-route {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .movement-head {
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .movement-head strong {
        color: #173434;
        font-size: 0.92rem;
    }

    .movement-head span {
        background: #ffffff;
        border: 1px solid #dfe8e8;
        border-radius: 999px;
        color: #526464;
        font-size: 0.72rem;
        font-weight: 800;
        padding: 4px 9px;
    }

    .movement-route {
        color: #344747;
        font-size: 0.84rem;
        margin-bottom: 6px;
    }

    .movement-route span {
        overflow-wrap: anywhere;
    }

    .movement-content p {
        background: #ffffff;
        border-left: 3px solid #d6e2e2;
        border-radius: 0 8px 8px 0;
        color: #536464;
        font-size: 0.86rem;
        line-height: 1.5;
        margin: 10px 0 0;
        padding: 10px 12px;
        white-space: pre-line;
    }

    @media (max-width: 991.98px) {
        .summary-grid {
            grid-template-columns: 1fr;
        }

        .detail-hero,
        .detail-section {
            padding: 20px;
        }
    }

    @media (max-width: 575.98px) {
        .detail-actions {
            justify-content: stretch;
        }

        .detail-actions .btn {
            width: 100%;
        }

        .comment-item {
            grid-template-columns: 1fr;
        }

        .comment-topline {
            flex-direction: column;
        }

        .assignment-route {
            grid-template-columns: 1fr;
        }

        .assignment-arrow {
            justify-content: flex-start;
        }

        .assignment-arrow i {
            transform: rotate(90deg);
        }
    }
</style>
@endsection
