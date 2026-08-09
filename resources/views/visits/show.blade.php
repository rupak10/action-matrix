@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
/* ── Layout ─────────────────────────────────────── */
.visit-layout { display: grid; grid-template-columns: 1fr 340px; gap: 1.25rem; align-items: start; }
@media(max-width:1100px) { .visit-layout { grid-template-columns: 1fr; } }

/* ── Card base ──────────────────────────────────── */
.v-card { background: var(--sl-surface); border: 1px solid var(--sl-border); border-radius: var(--sl-radius); }
.v-card-header { padding: .9rem 1.25rem; border-bottom: 1px solid var(--sl-border); display: flex; align-items: center; justify-content: space-between; gap: .5rem; }
.v-card-header .v-card-title { font-weight: 700; font-size: .9rem; color: var(--sl-text); }
.v-card-body { padding: 1.25rem; }

/* ── Visit header info ──────────────────────────── */
.info-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: .75rem 1.25rem; }
.info-item .info-label { font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; color: var(--sl-muted); font-weight: 600; }
.info-item .info-value { font-size: .875rem; font-weight: 600; color: var(--sl-text); margin-top: .1rem; }

/* ── Status badge ───────────────────────────────── */
.badge-status {
    display: inline-flex; align-items: center;
    font-size: .7rem; font-weight: 700;
    padding: .3em .75em; border-radius: 20px;
    letter-spacing: .04em; white-space: nowrap;
    text-transform: uppercase;
    vertical-align: middle;
}
/* Solid filled — clearly visible against any background */
.badge-SAVED        { background: #64748b; color: #fff; }
.badge-SUBMITTED    { background: #2563eb; color: #fff; }
.badge-REJECTED     { background: #dc2626; color: #fff; }
.badge-PO_SO_REVIEW { background: #ea580c; color: #fff; }
.badge-PO_REVIEW    { background: #d97706; color: #fff; }
.badge-PO_SUBMITTED { background: #16a34a; color: #fff; }
.badge-PO_APPROVED  { background: #059669; color: #fff; }

/* ── Observation card ───────────────────────────── */
.obs-card { border: 1px solid var(--sl-border); border-radius: 10px; margin-bottom: .75rem; overflow: hidden; transition: box-shadow .15s; }
.obs-card:hover { box-shadow: 0 4px 16px -4px rgba(27,58,58,.08); }
.obs-card-head { padding: .75rem 1rem; background: #fafbfc; display: flex; align-items: flex-start; gap: .75rem; cursor: pointer; border-bottom: 1px solid transparent; }
.obs-card-head.expanded { border-bottom-color: var(--sl-border); }
.obs-seq { width: 28px; height: 28px; border-radius: 8px; background: var(--sl-primary); color: #fff; font-size: .75rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: .1rem; }
.obs-body { padding: 1rem; }

/* ── Resolution badge ───────────────────────────── */
.res-badge { font-size: .68rem; font-weight: 700; padding: .25em .6em; border-radius: 5px; white-space: nowrap; }
.res-OPEN             { background: #f1f5f9; color: #475569; }
.res-PENDING_RESOLVED { background: #fef3c7; color: #92400e; }
.res-RESOLVED         { background: #dcfce7; color: #15803d; }

/* ── Priority ───────────────────────────────────── */
.priority-pill { font-size: .68rem; font-weight: 700; padding: .25em .6em; border-radius: 5px; }
.priority-HIGH   { background: #fee2e2; color: #991b1b; }
.priority-MEDIUM { background: #fef3c7; color: #92400e; }
.priority-LOW    { background: #f0fdf4; color: #166534; }

/* ── Comment thread ─────────────────────────────── */
.comment-thread { display: flex; flex-direction: column; gap: .5rem; max-height: 360px; overflow-y: auto; padding-right: .25rem; }
.comment-bubble { border-radius: 10px; padding: .75rem 1rem; font-size: .84rem; line-height: 1.5; }
.comment-PKSF { background: #eff6ff; border-left: 3px solid #3b82f6; }
.comment-PO   { background: #f0fdf4; border-left: 3px solid #22c55e; }
.comment-meta { font-size: .72rem; color: var(--sl-muted); margin-top: .3rem; display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; }
.comment-draft-badge { font-size: .65rem; font-weight: 700; padding: .15em .45em; border-radius: 4px; background: #fef3c7; color: #92400e; }

/* ── Comment form ───────────────────────────────── */
.comment-form-wrap { background: #f8fafc; border: 1px solid var(--sl-border); border-radius: 10px; padding: .85rem 1rem; }
.comment-form-wrap textarea { border-radius: 8px; font-size: .85rem; resize: vertical; min-height: 80px; }

/* ── Sidebar panels ─────────────────────────────── */
.timeline-item { display: flex; gap: .75rem; padding-bottom: 1.1rem; position: relative; }
.timeline-item:not(:last-child)::before { content: ''; position: absolute; left: 14px; top: 30px; bottom: 0; width: 1px; background: var(--sl-border); }
.timeline-dot { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .8rem; flex-shrink: 0; }
.timeline-content { flex: 1; min-width: 0; }

/* ── Resolution progress ────────────────────────── */
.res-progress { height: 8px; border-radius: 20px; overflow: hidden; background: #e2e8f0; }
.res-progress-bar { height: 100%; border-radius: 20px; background: linear-gradient(90deg, #22c55e, #16a34a); transition: width .4s; }

/* ── Action buttons ─────────────────────────────── */
.action-btn { display: flex; align-items: center; gap: .4rem; font-size: .82rem; font-weight: 600; padding: .45rem 1rem; border-radius: 8px; border: none; cursor: pointer; transition: all .15s; white-space: nowrap; }
.action-btn-primary { background: var(--sl-primary); color: #fff; }
.action-btn-primary:hover { background: #163232; color: #fff; }
.action-btn-danger  { background: #fff1f2; color: #e11d48; }
.action-btn-danger:hover  { background: #fee2e2; }
.action-btn-warning { background: #fff7ed; color: #c2410c; }
.action-btn-success { background: #16a34a; color: #fff; }
.action-btn-success:hover { background: #15803d; color: #fff; }

/* ── Attachment chips ───────────────────────────── */
.att-chip { display: inline-flex; align-items: center; gap: .35rem; padding: .25rem .6rem; border-radius: 6px; background: #f1f5f9; border: 1px solid var(--sl-border); font-size: .75rem; text-decoration: none; color: var(--sl-text); transition: background .12s; }
.att-chip:hover { background: var(--sl-primary-soft); color: var(--sl-primary); }
.att-chip i { font-size: .8rem; color: var(--sl-muted); }

/* ── Remark block ───────────────────────────────── */
.remark-block { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: .85rem 1rem; font-size: .85rem; }
</style>
@endpush

@section('content')
@php
    $user         = auth()->user();
    $isSupervisor = $user->isSupervisor();
    $isPksf       = $user->isPksf();
    $isPo         = $user->isPo();
    $status       = $visit->status;
    $isMyDesk     = $visit->current_desk_emp_id === $user->emp_id;

    $canEditVisit   = $visit->isEditableByPksfCo() && $visit->created_by === $user->emp_id;
    $canAddObs      = $visit->isEditableByPksfCo() && $isPksf;
    $canForward     = $isPksf && !$isSupervisor && $isMyDesk && in_array($status, ['SAVED', 'REJECTED', 'PO_APPROVED']);
    $canSendToPo    = $isPksf && $isSupervisor && $isMyDesk && in_array($status, ['SUBMITTED', 'PO_APPROVED']);
    $canReject      = $isPksf && $isSupervisor && $isMyDesk && $status === 'SUBMITTED';
    $canFwdPoOfficer= $isPo && $isSupervisor && $isMyDesk && $status === 'PO_SO_REVIEW';
    $canSubmitPoSo  = $isPo && !$isSupervisor && $isMyDesk && $status === 'PO_REVIEW';
    $canApprovePoResp = $isPo && $isSupervisor && $isMyDesk && $status === 'PO_SUBMITTED';
    // Anyone can comment only when the visit is on their desk
    $canComment     = $isMyDesk;

    // Used to disable the Forward button when the visit has no observations yet.
    $hasObservations = $visit->observations->isNotEmpty();

    // Resolve supervisor for the Forward modal
    $forwardSupervisor = null;
    if ($canForward) {
        $supEmpId = \Illuminate\Support\Facades\DB::table('user_supervisors')
            ->where('emp_id', $user->emp_id)
            ->where('is_primary', true)
            ->value('supervisor_emp_id');
        $forwardSupervisor = $supEmpId ? \App\Models\User::where('emp_id', $supEmpId)->first() : null;
    }

    // Resolve PO Supervisor for the Send to PO modal
    $sendToPoSupervisor = null;
    if ($canSendToPo) {
        $sendToPoSupervisor = \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'PO_SUPERVISOR'))
            ->where('po_code', $visit->po_code)
            ->first();
    }

    // Resolve PO Supervisor for the Submit to PO SO modal
    $submitPoSupervisor = null;
    if ($canSubmitPoSo) {
        $supEmpId = \Illuminate\Support\Facades\DB::table('user_supervisors')
            ->where('emp_id', $user->emp_id)
            ->where('is_primary', true)
            ->value('supervisor_emp_id');
        $submitPoSupervisor = $supEmpId ? \App\Models\User::where('emp_id', $supEmpId)->first() : null;
    }

    // Resolve PO Officer for the Forward to PO Officer modal
    $fwdPoOfficer = null;
    if ($canFwdPoOfficer) {
        $fwdPoOfficer = \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'PO_CO'))
            ->where('po_code', $visit->po_code)
            ->first();
        // Fallback: any PO user for this po_code
        if (!$fwdPoOfficer) {
            $fwdPoOfficer = \App\Models\User::where('po_code', $visit->po_code)
                ->where('emp_type', 'PO')
                ->first();
        }
    }

    $canDeleteVisit = $isPksf && $status === 'SAVED'
        && \Illuminate\Support\Facades\DB::table('user_po_assignments')
            ->where('emp_id', $user->emp_id)
            ->where('po_code', $visit->po_code)
            ->exists();

    $deskUser = $usersByEmpId->get($visit->current_desk_emp_id);

    function empName($empId, $users, $fallback = '—') {
        $u = $users->get($empId);
        return $u ? $u->name : ($fallback ?? $empId);
    }
@endphp

<div class="container-fluid pt-1 pb-4">

    {{-- Breadcrumb & Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3 gap-2 flex-wrap">
        <div>
            <nav style="font-size:.8rem">
                <a href="{{ route('visits.index') }}" class="text-decoration-none" style="color:var(--sl-muted)">
                    <i class="bi bi-folder2-open me-1"></i>Visits
                </a>
                <span class="mx-2 text-sl-muted">/</span>
                {{-- Visit code and status badge sit together --}}
                <span class="fw-bold" style="color:var(--sl-text)">{{ $visit->visit_code }}</span>
                <span class="badge-status badge-{{ $status }} ms-2">{{ str_replace('_', ' ', $status) }}</span>
            </nav>
        </div>
        <div class="d-flex gap-2 align-items-center">
            @if($canEditVisit)
            <a href="{{ route('visits.edit', $visit->id) }}" class="btn btn-sm btn-outline-secondary" style="border-radius:7px;font-size:.8rem">
                <i class="bi bi-pencil me-1"></i>Edit Visit
            </a>
            @endif
            @if($canDeleteVisit)
            <button type="button" class="btn btn-sm btn-outline-danger" style="border-radius:7px;font-size:.8rem"
                    data-bs-toggle="modal" data-bs-target="#modal-delete-visit">
                <i class="bi bi-trash me-1"></i>Delete
            </button>
            @endif
        </div>
    </div>

    <div class="visit-layout">

        {{-- ════════════════════════ MAIN COLUMN ═══════════════════════════ --}}
        <div>

            {{-- Visit Info Card --}}
            <div class="v-card mb-3">
                <div class="v-card-header">
                    <span class="v-card-title"><i class="bi bi-calendar-week me-2"></i>Visit Details</span>
                </div>
                <div class="v-card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Visit Code</div>
                            <div class="info-value" style="font-family:monospace">{{ $visit->visit_code }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Partner Organization</div>
                            <div class="info-value">{{ $visit->poInfo?->po_name ?? $visit->po_code }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">From</div>
                            <div class="info-value">{{ \Carbon\Carbon::parse($visit->visit_from_date)->format('d M Y') }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">To</div>
                            <div class="info-value">{{ \Carbon\Carbon::parse($visit->visit_to_date)->format('d M Y') }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Type</div>
                            <div class="info-value">{{ $visit->visit_type }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Category</div>
                            <div class="info-value">{{ $visit->visit_category ?? '—' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Letter Issue Date</div>
                            <div class="info-value">{{ $visit->letter_issue_date ? $visit->letter_issue_date->format('d M Y') : '—' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Response Deadline</div>
                            <div class="info-value">{{ $visit->letter_response_date ? $visit->letter_response_date->format('d M Y') : '—' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Created By</div>
                            <div class="info-value">{{ empName($visit->created_by, $usersByEmpId) }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Current Desk</div>
                            <div class="info-value">
                                {{ $deskUser?->name ?? $visit->current_desk_emp_id }}
                                @if($isMyDesk)<span class="badge ms-1" style="background:#dcfce7;color:#15803d;font-size:.65rem">You</span>@endif
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Resolution</div>
                            <div class="info-value">
                                {{ $resolutionSummary['resolved'] }}/{{ $resolutionSummary['total'] }} resolved
                                @if($resolutionSummary['pending_resolved'] > 0)
                                <span class="badge ms-1" style="background:#fef3c7;color:#92400e;font-size:.65rem">{{ $resolutionSummary['pending_resolved'] }} pending</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($resolutionSummary['total'] > 0)
                    <div class="mt-3">
                        <div class="res-progress">
                            @php $pct = $resolutionSummary['total'] > 0 ? round(($resolutionSummary['resolved'] / $resolutionSummary['total']) * 100) : 0; @endphp
                            <div class="res-progress-bar" style="width:{{ $pct }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted" style="font-size:.72rem">{{ $pct }}% resolved</small>
                            <small class="text-muted" style="font-size:.72rem">{{ $resolutionSummary['open'] }} open</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Visit-level remarks from PKSF Supervisor (sent to PO) --}}
            @if($visit->remarks->isNotEmpty())
            <div class="mb-3" style="border-radius:12px;overflow:hidden;border:1.5px solid #bbf7d0;box-shadow:0 4px 16px -4px rgba(22,163,74,.12)">

                {{-- Green accent header --}}
                <div style="background:#f0fdf4;padding:.85rem 1.25rem;border-bottom:1.5px solid #bbf7d0;display:flex;align-items:center;gap:.75rem">
                    <div style="width:32px;height:32px;border-radius:8px;background:#16a34a;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="bi bi-megaphone" style="font-size:.85rem;color:#fff"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:.88rem;color:#14532d">Remarks from PKSF Supervisor</div>
                        <div style="font-size:.72rem;color:#15803d">Sent along with this visit for PO review</div>
                    </div>
                </div>

                {{-- Remark entries --}}
                <div style="background:#f7fef9;padding:.85rem 1.25rem;display:flex;flex-direction:column;gap:.75rem">
                    @foreach($visit->remarks as $remark)
                    @php $remarkAuthor = $usersByEmpId->get($remark->created_by); @endphp
                    <div style="background:#fff;border:1px solid #bbf7d0;border-left:3.5px solid #16a34a;border-radius:8px;padding:.85rem 1rem">
                        @if($remark->remarks)
                        <div style="font-size:.875rem;color:#1a2e1a;line-height:1.65;white-space:pre-wrap">{{ $remark->remarks }}</div>
                        @endif
                        @if($remark->attachments->isNotEmpty())
                        <div class="mt-2 d-flex flex-wrap gap-1">
                            @foreach($remark->attachments as $att)
                            <a href="{{ $att->download_url }}" target="_blank" class="att-chip">
                                <i class="bi bi-paperclip"></i>{{ $att->file_name }}
                            </a>
                            @endforeach
                        </div>
                        @endif
                        <div class="d-flex align-items-center gap-2 mt-2 pt-2" style="font-size:.72rem;color:#15803d;border-top:1px solid #dcfce7">
                            <i class="bi bi-person-fill"></i>
                            <span class="fw-semibold">{{ $remarkAuthor?->name ?? $remark->created_by }}</span>
                            <span class="text-muted">&bull;</span>
                            <span>{{ \Carbon\Carbon::parse($remark->created_at)->format('d M Y, h:i A') }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>

            </div>
            @endif

            {{-- ── Observations ──────────────────────────────────────────── --}}
            <div class="v-card mb-3">
                <div class="v-card-header">
                    <span class="v-card-title">
                        <i class="bi bi-list-check me-2"></i>Observations
                        <span class="badge rounded-pill ms-1" style="background:var(--sl-primary-soft);color:var(--sl-primary);font-size:.7rem">{{ $visit->observations->count() }}</span>
                    </span>
                    {{-- Resolution status legend --}}
                    <div class="d-flex align-items-center gap-3 ms-auto me-3" style="flex-shrink:0;font-size:.72rem;color:#64748b">
                        <span class="d-flex align-items-center gap-1" style="white-space:nowrap">
                            <span style="width:10px;height:10px;border-radius:3px;background:var(--sl-primary);display:inline-block"></span>Open
                        </span>
                        <span class="d-flex align-items-center gap-1" style="white-space:nowrap">
                            <span style="width:10px;height:10px;border-radius:3px;background:#d97706;display:inline-block"></span>Pending Resolution
                        </span>
                        <span class="d-flex align-items-center gap-1" style="white-space:nowrap">
                            <span style="width:10px;height:10px;border-radius:3px;background:#16a34a;display:inline-block"></span>Resolved
                        </span>
                    </div>
                    @if($canAddObs)
                    <button class="action-btn action-btn-primary" id="btn-add-obs" style="font-size:.78rem;padding:.35rem .8rem">
                        <i class="bi bi-plus-lg"></i> Add Observation
                    </button>
                    @endif
                </div>
                <div class="v-card-body">

                    @if($visit->observations->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                        <div>No observations yet.</div>
                        @if($canAddObs)
                        <div class="mt-1" style="font-size:.82rem">Click <strong>Add Observation</strong> to get started.</div>
                        @endif
                    </div>
                    @else
                    <div id="obs-list">
                        @foreach($visit->observations as $idx => $obs)
                        @include('visits.partials.observation_card', [
                            'obs' => $obs,
                            'idx' => $idx + 1,
                            'visit' => $visit,
                            'canAddObs' => $canAddObs,
                            'canComment' => $canComment,
                            'isSupervisor' => $isSupervisor,
                            'isPksf' => $isPksf,
                            'isPo' => $isPo,
                            'usersByEmpId' => $usersByEmpId,
                        ])
                        @endforeach
                    </div>
                    @endif

                </div>
            </div>

            {{-- ── Workflow Action Panel ─────────────────────────────────── --}}
            @if($canForward || $canSendToPo || $canReject || $canFwdPoOfficer || $canSubmitPoSo || $canApprovePoResp)
            <div class="v-card mb-3">
                <div class="v-card-header">
                    <span class="v-card-title"><i class="bi bi-arrow-right-circle me-2"></i>Workflow Actions</span>
                </div>
                <div class="v-card-body d-flex flex-wrap gap-2">

                    @if($canForward)
                    {{-- Disabled with tooltip when no observations exist --}}
                    <span data-bs-toggle="tooltip"
                          title="{{ !$hasObservations ? 'Add at least one observation before forwarding.' : '' }}">
                        <button class="action-btn action-btn-primary"
                                @if($hasObservations) data-bs-toggle="modal" data-bs-target="#modal-forward"
                                @else disabled style="opacity:.5;cursor:not-allowed;pointer-events:none"
                                @endif>
                            <i class="bi bi-send"></i> Forward to Supervisor
                        </button>
                    </span>
                    @endif

                    @if($canSendToPo)
                    <button class="action-btn action-btn-primary" data-bs-toggle="modal" data-bs-target="#modal-send-po">
                        <i class="bi bi-send"></i> Send to PO
                    </button>
                    @endif

                    @if($canReject)
                    <button class="action-btn action-btn-danger" data-bs-toggle="modal" data-bs-target="#modal-reject">
                        <i class="bi bi-arrow-counterclockwise"></i> Reject to Officer
                    </button>
                    @endif

                    @if($canFwdPoOfficer)
                    <button class="action-btn action-btn-primary" data-bs-toggle="modal" data-bs-target="#modal-fwd-po-officer">
                        <i class="bi bi-person-check"></i> Forward to PO Officer
                    </button>
                    @endif

                    @if($canSubmitPoSo)
                    <button class="action-btn action-btn-primary" data-bs-toggle="modal" data-bs-target="#modal-submit-po-so">
                        <i class="bi bi-send"></i> Submit to PO Supervisor
                    </button>
                    @endif

                    @if($canApprovePoResp)
                    <button class="action-btn action-btn-success" data-bs-toggle="modal" data-bs-target="#modal-approve-po">
                        <i class="bi bi-check-circle"></i> Approve &amp; Send to PKSF
                    </button>
                    @endif

                </div>
            </div>
            @endif

        </div>
        {{-- ════════════════════════ END MAIN ═══════════════════════════ --}}

        {{-- ════════════════════════ SIDEBAR ═══════════════════════════ --}}
        <div style="position:sticky;top:1rem">

            {{-- Movement History --}}
            <div class="v-card mb-3" style="display:flex;flex-direction:column;max-height:calc(100vh - 80px)">
                <div class="v-card-header" style="flex-shrink:0">
                    <span class="v-card-title"><i class="bi bi-clock-history me-2"></i>Movement History</span>
                </div>
                <div class="v-card-body" id="visit-history-panel" style="flex:1;overflow-y:auto;min-height:0">
                    @include('visits.partials.visit_timeline', ['visit' => $visit, 'usersByEmpId' => $usersByEmpId])
                </div>
            </div>

        </div>
        {{-- ════════════════════════ END SIDEBAR ═══════════════════════ --}}

    </div>
</div>

{{-- ═══════════════════════════ MODALS ════════════════════════════════════ --}}

{{-- Forward to Supervisor --}}
@if($canForward)
<div class="modal fade" id="modal-forward" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:12px;border:none;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.12)">
            <form method="POST" action="{{ route('visits.forward', $visit->id) }}">
                @csrf

                {{-- Header --}}
                <div style="background:#f8fafc;padding:1.25rem 1.5rem;border-bottom:1px solid #e2e8f0">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:34px;height:34px;border-radius:8px;background:var(--sl-primary);display:flex;align-items:center;justify-content:center">
                                <i class="bi bi-send" style="font-size:.9rem;color:#fff"></i>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:.95rem;color:var(--sl-text)">Forward to Supervisor</div>
                                <div style="font-size:.75rem;color:var(--sl-muted)">{{ $visit->visit_code }} &mdash; {{ $visit->observations->count() }} observation(s)</div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                </div>

                <div class="modal-body" style="padding:1.25rem 1.5rem">

                    {{-- Supervisor info card --}}
                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:.9rem 1.1rem;margin-bottom:1.1rem">
                        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#15803d;margin-bottom:.5rem">
                            <i class="bi bi-person-check me-1"></i>Forwarding to
                        </div>
                        @if($forwardSupervisor)
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:36px;height:36px;border-radius:50%;background:#16a34a;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                <span style="font-size:.85rem;font-weight:700;color:#fff">{{ strtoupper(substr($forwardSupervisor->name, 0, 1)) }}</span>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:.9rem;color:var(--sl-text)">{{ $forwardSupervisor->name }}</div>
                                <div style="font-size:.75rem;color:var(--sl-muted)">{{ $forwardSupervisor->emp_id }} &mdash; PKSF Supervisor</div>
                            </div>
                        </div>
                        @else
                        <div style="font-size:.85rem;color:#dc2626">
                            <i class="bi bi-exclamation-circle me-1"></i>No supervisor configured for your account.
                        </div>
                        @endif
                    </div>

                    {{-- Remarks --}}
                    <div>
                        <label class="form-label" style="font-size:.82rem;font-weight:600">
                            Remarks <small class="text-muted fw-normal">(optional)</small>
                        </label>
                        <textarea name="remarks" class="form-control" rows="3"
                                  style="font-size:.875rem;border-radius:8px;resize:none"
                                  placeholder="Any notes for the supervisor…"></textarea>
                    </div>

                </div>

                <div class="modal-footer" style="padding:.75rem 1.5rem 1.25rem;border-top:1px solid var(--sl-border);gap:.5rem">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                            style="border-radius:8px;font-size:.875rem;font-weight:600;min-width:90px">
                        Cancel
                    </button>
                    <button type="submit" class="btn" style="background:var(--sl-primary);color:#fff;border-radius:8px;font-size:.875rem;font-weight:600;min-width:140px"
                            {{ !$forwardSupervisor ? 'disabled' : '' }}>
                        <i class="bi bi-send me-1"></i>Forward
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Send to PO --}}
@if($canSendToPo)
<div class="modal fade" id="modal-send-po" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:12px;border:none;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.12)">
            <form method="POST" action="{{ route('visits.send-to-po', $visit->id) }}" enctype="multipart/form-data">
                @csrf

                {{-- Header --}}
                <div style="background:#f8fafc;padding:1.25rem 1.5rem;border-bottom:1px solid #e2e8f0">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:34px;height:34px;border-radius:8px;background:#f97316;display:flex;align-items:center;justify-content:center">
                                <i class="bi bi-send" style="font-size:.9rem;color:#fff"></i>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:.95rem;color:var(--sl-text)">Send to PO</div>
                                <div style="font-size:.75rem;color:var(--sl-muted)">{{ $visit->visit_code }} &mdash; {{ $visit->observations->count() }} observation(s)</div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                </div>

                <div class="modal-body" style="padding:1.25rem 1.5rem">

                    {{-- PO Supervisor info card --}}
                    <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:.9rem 1.1rem;margin-bottom:1.1rem">
                        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#c2410c;margin-bottom:.5rem">
                            <i class="bi bi-person-check me-1"></i>Sending to
                        </div>
                        @if($sendToPoSupervisor)
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:36px;height:36px;border-radius:50%;background:#f97316;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                <span style="font-size:.85rem;font-weight:700;color:#fff">{{ strtoupper(substr($sendToPoSupervisor->name, 0, 1)) }}</span>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:.9rem;color:var(--sl-text)">{{ $sendToPoSupervisor->name }}</div>
                                <div style="font-size:.75rem;color:var(--sl-muted)">{{ $sendToPoSupervisor->emp_id }} &mdash; PO Supervisor</div>
                            </div>
                        </div>
                        @else
                        <div style="font-size:.85rem;color:#dc2626">
                            <i class="bi bi-exclamation-circle me-1"></i>No PO Supervisor configured for <strong>{{ $visit->po_code }}</strong>.
                        </div>
                        @endif
                    </div>

                    {{-- Remarks --}}
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.82rem;font-weight:600">
                            Remarks <small class="text-muted fw-normal">(optional)</small>
                        </label>
                        <textarea name="remarks" class="form-control" rows="3"
                                  style="font-size:.875rem;border-radius:8px;resize:none"
                                  placeholder="Overall remarks or instructions applicable to all observations…"></textarea>
                    </div>

                    {{-- File attachments --}}
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label mb-0" style="font-size:.82rem;font-weight:600">Attachments</label>
                            <span id="spo-file-counter" style="font-size:.75rem;color:var(--sl-muted)">0 of 3 files</span>
                        </div>
                        <input type="file" id="spo-file-input" multiple
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" style="display:none">
                        <button type="button" id="spo-attach-btn"
                                style="width:100%;border:1.5px dashed var(--sl-border);border-radius:8px;
                                       background:#fafafa;padding:.6rem 1rem;font-size:.82rem;font-weight:600;
                                       color:var(--sl-primary);cursor:pointer;transition:all .15s;
                                       display:flex;align-items:center;justify-content:center;gap:.5rem">
                            <i class="bi bi-paperclip" style="font-size:.95rem"></i>
                            Attach Files
                            <span style="font-weight:400;color:var(--sl-muted);font-size:.75rem">· PDF, Word, Excel, Images · max 30MB</span>
                        </button>
                        <div id="spo-file-list" class="mt-2 d-flex flex-column gap-1"></div>
                    </div>

                </div>

                <div class="modal-footer" style="padding:.75rem 1.5rem 1.25rem;border-top:1px solid var(--sl-border);gap:.5rem">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                            style="border-radius:8px;font-size:.875rem;font-weight:600;min-width:90px">
                        Cancel
                    </button>
                    <button type="submit" class="btn" style="background:#f97316;color:#fff;border-radius:8px;font-size:.875rem;font-weight:600;min-width:140px"
                            {{ !$sendToPoSupervisor ? 'disabled' : '' }}>
                        <i class="bi bi-send me-1"></i>Send to PO
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Reject to PKSF CO --}}
@if($canReject)
@php $rejectTargetUser = $usersByEmpId->get($visit->created_by); @endphp
<div class="modal fade" id="modal-reject" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:12px;border:none;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.12)">
            <form method="POST" action="{{ route('visits.reject', $visit->id) }}">
                @csrf

                {{-- Header --}}
                <div style="background:#fff1f2;padding:1.25rem 1.5rem;border-bottom:1px solid #fecdd3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:34px;height:34px;border-radius:8px;background:#e11d48;display:flex;align-items:center;justify-content:center">
                                <i class="bi bi-arrow-return-left" style="font-size:.9rem;color:#fff"></i>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:.95rem;color:#9f1239">Return to Officer</div>
                                <div style="font-size:.75rem;color:#e11d48">{{ $visit->visit_code }} &mdash; {{ $visit->observations->count() }} observation(s)</div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                </div>

                <div class="modal-body" style="padding:1.25rem 1.5rem">

                    {{-- CO info card --}}
                    <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:.9rem 1.1rem;margin-bottom:1.1rem">
                        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#c2410c;margin-bottom:.5rem">
                            <i class="bi bi-person-fill-up me-1"></i>Returning to
                        </div>
                        @if($rejectTargetUser)
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:36px;height:36px;border-radius:50%;background:#ea580c;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                <span style="font-size:.85rem;font-weight:700;color:#fff">{{ strtoupper(substr($rejectTargetUser->name, 0, 1)) }}</span>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:.9rem;color:var(--sl-text)">{{ $rejectTargetUser->name }}</div>
                                <div style="font-size:.75rem;color:var(--sl-muted)">{{ $rejectTargetUser->emp_id }} &mdash; PKSF Officer</div>
                            </div>
                        </div>
                        @else
                        <div style="font-size:.85rem;color:var(--sl-muted)">{{ $visit->created_by }}</div>
                        @endif
                    </div>

                    {{-- Reason --}}
                    <div>
                        <label class="form-label" style="font-size:.82rem;font-weight:600">
                            Reason for Return <small class="text-muted fw-normal">(optional)</small>
                        </label>
                        <textarea name="remarks" class="form-control" rows="3"
                                  style="font-size:.875rem;border-radius:8px;resize:none"
                                  placeholder="Explain what needs to be corrected or improved…"></textarea>
                    </div>

                </div>

                <div class="modal-footer" style="padding:.75rem 1.5rem 1.25rem;border-top:1px solid var(--sl-border);gap:.5rem">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                            style="border-radius:8px;font-size:.875rem;font-weight:600;min-width:90px">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-danger"
                            style="border-radius:8px;font-size:.875rem;font-weight:600;min-width:140px">
                        <i class="bi bi-arrow-return-left me-1"></i>Return to Officer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Forward to PO Officer --}}
@if($canFwdPoOfficer)
<div class="modal fade" id="modal-fwd-po-officer" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:12px;border:none;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.12)">
            <form method="POST" action="{{ route('visits.forward-po-officer', $visit->id) }}">
                @csrf

                {{-- Header --}}
                <div style="background:#f8fafc;padding:1.25rem 1.5rem;border-bottom:1px solid #e2e8f0">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:34px;height:34px;border-radius:8px;background:#d97706;display:flex;align-items:center;justify-content:center">
                                <i class="bi bi-send" style="font-size:.9rem;color:#fff"></i>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:.95rem;color:var(--sl-text)">Forward to PO Officer</div>
                                <div style="font-size:.75rem;color:var(--sl-muted)">{{ $visit->visit_code }} &mdash; {{ $visit->observations->count() }} observation(s)</div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                </div>

                <div class="modal-body" style="padding:1.25rem 1.5rem">

                    {{-- PO Officer info card --}}
                    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:.9rem 1.1rem;margin-bottom:1.1rem">
                        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#92400e;margin-bottom:.5rem">
                            <i class="bi bi-person-check me-1"></i>Forwarding to
                        </div>
                        @if($fwdPoOfficer)
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:36px;height:36px;border-radius:50%;background:#d97706;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                <span style="font-size:.85rem;font-weight:700;color:#fff">{{ strtoupper(substr($fwdPoOfficer->name, 0, 1)) }}</span>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:.9rem;color:var(--sl-text)">{{ $fwdPoOfficer->name }}</div>
                                <div style="font-size:.75rem;color:var(--sl-muted)">{{ $fwdPoOfficer->emp_id }} &mdash; PO Concern Officer</div>
                            </div>
                        </div>
                        @else
                        <div style="font-size:.85rem;color:#dc2626">
                            <i class="bi bi-exclamation-circle me-1"></i>No PO Officer found for <strong>{{ $visit->po_code }}</strong>.
                        </div>
                        @endif
                    </div>

                    {{-- Remarks --}}
                    <div>
                        <label class="form-label" style="font-size:.82rem;font-weight:600">
                            Remarks <small class="text-muted fw-normal">(optional)</small>
                        </label>
                        <textarea name="remarks" class="form-control" rows="3"
                                  style="font-size:.875rem;border-radius:8px;resize:none"
                                  placeholder="Any notes for the PO Officer…"></textarea>
                    </div>

                </div>

                <div class="modal-footer" style="padding:.75rem 1.5rem 1.25rem;border-top:1px solid var(--sl-border);gap:.5rem">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                            style="border-radius:8px;font-size:.875rem;font-weight:600;min-width:90px">
                        Cancel
                    </button>
                    <button type="submit" class="btn"
                            style="background:#d97706;color:#fff;border-radius:8px;font-size:.875rem;font-weight:600;min-width:140px"
                            {{ !$fwdPoOfficer ? 'disabled' : '' }}>
                        <i class="bi bi-send me-1"></i>Forward
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Submit to PO Supervisor --}}
@if($canSubmitPoSo)
<div class="modal fade" id="modal-submit-po-so" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:12px;border:none;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.12)">
            <form method="POST" action="{{ route('visits.submit-po-so', $visit->id) }}">
                @csrf

                {{-- Header --}}
                <div style="background:#f8fafc;padding:1.25rem 1.5rem;border-bottom:1px solid #e2e8f0">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:34px;height:34px;border-radius:8px;background:#16a34a;display:flex;align-items:center;justify-content:center">
                                <i class="bi bi-send-check" style="font-size:.9rem;color:#fff"></i>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:.95rem;color:var(--sl-text)">Submit to PO Supervisor</div>
                                <div style="font-size:.75rem;color:var(--sl-muted)">{{ $visit->visit_code }} &mdash; {{ $visit->observations->count() }} observation(s)</div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                </div>

                <div class="modal-body" style="padding:1.25rem 1.5rem">

                    {{-- PO Supervisor info card --}}
                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:.9rem 1.1rem;margin-bottom:1.1rem">
                        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#15803d;margin-bottom:.5rem">
                            <i class="bi bi-person-check me-1"></i>Submitting to
                        </div>
                        @if($submitPoSupervisor)
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:36px;height:36px;border-radius:50%;background:#16a34a;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                <span style="font-size:.85rem;font-weight:700;color:#fff">{{ strtoupper(substr($submitPoSupervisor->name, 0, 1)) }}</span>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:.9rem;color:var(--sl-text)">{{ $submitPoSupervisor->name }}</div>
                                <div style="font-size:.75rem;color:var(--sl-muted)">{{ $submitPoSupervisor->emp_id }} &mdash; PO Supervisor</div>
                            </div>
                        </div>
                        @else
                        <div style="font-size:.85rem;color:#dc2626">
                            <i class="bi bi-exclamation-circle me-1"></i>No supervisor configured for your account.
                        </div>
                        @endif
                    </div>

                    {{-- Remarks --}}
                    <div>
                        <label class="form-label" style="font-size:.82rem;font-weight:600">
                            Remarks <small class="text-muted fw-normal">(optional)</small>
                        </label>
                        <textarea name="remarks" class="form-control" rows="3"
                                  style="font-size:.875rem;border-radius:8px;resize:none"
                                  placeholder="Any notes for the PO Supervisor…"></textarea>
                    </div>

                </div>

                <div class="modal-footer" style="padding:.75rem 1.5rem 1.25rem;border-top:1px solid var(--sl-border);gap:.5rem">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                            style="border-radius:8px;font-size:.875rem;font-weight:600;min-width:90px">
                        Cancel
                    </button>
                    <button type="submit" class="btn"
                            style="background:#16a34a;color:#fff;border-radius:8px;font-size:.875rem;font-weight:600;min-width:140px"
                            {{ !$submitPoSupervisor ? 'disabled' : '' }}>
                        <i class="bi bi-send-check me-1"></i>Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Approve PO Response --}}
@if($canApprovePoResp)
<div class="modal fade" id="modal-approve-po" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width:560px">
        <div class="modal-content" style="border-radius:12px;border:none;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.15)">
            <form method="POST" action="{{ route('visits.approve-po', $visit->id) }}">
                @csrf

                <div style="background:#f0fdf4;padding:1.25rem 1.5rem;border-bottom:1.5px solid #bbf7d0">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:40px;height:40px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <i class="bi bi-send-check" style="font-size:1.2rem;color:#16a34a"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size:.9rem;color:#14532d">Approve &amp; Send to PKSF</div>
                            <div style="font-size:.75rem;color:#16a34a;margin-top:.1rem">PO response will be finalized and forwarded to PKSF</div>
                        </div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                    </div>
                </div>

                <div class="modal-body" style="padding:1.25rem 1.5rem">
                    <p class="mb-3" style="font-size:.875rem;color:var(--sl-text)">
                        Are you sure you want to <strong>approve</strong> the PO response and return this visit to PKSF?
                    </p>
                    <div class="mb-3" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:.75rem 1rem;font-size:.82rem;color:#166534">
                        <i class="bi bi-info-circle me-1"></i>All PO comments on observations will be <strong>finalized</strong> and become visible to PKSF.
                    </div>
                    <label class="form-label" style="font-size:.82rem;font-weight:600;color:var(--sl-text)">
                        Remarks <small class="text-muted fw-normal">(optional)</small>
                    </label>
                    <textarea name="remarks" class="form-control" rows="3"
                              placeholder="Add any remarks for PKSF..."
                              style="font-size:.85rem;border-radius:8px;resize:vertical"></textarea>
                </div>

                <div class="modal-footer" style="padding:.75rem 1.5rem 1.25rem;border-top:1px solid var(--sl-border);gap:.5rem">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                            style="border-radius:8px;font-size:.875rem;font-weight:600;min-width:90px">
                        Cancel
                    </button>
                    <button type="submit" class="btn"
                            style="background:#16a34a;color:#fff;border-radius:8px;font-size:.875rem;font-weight:600;min-width:175px">
                        <i class="bi bi-send-check me-1"></i>Approve &amp; Send to PKSF
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endif

{{-- View Observation Modal — always available, view is never permission-gated --}}
@include('visits.partials.view_observation_modal', ['visit' => $visit])

{{-- Add Observation Modal --}}
@if($canAddObs)
@include('visits.partials.add_observation_modal', ['visit' => $visit])
@endif

{{-- Delete Observation Modal — CO (SAVED/REJECTED) or supervisor (SUBMITTED) --}}
@if($canAddObs || ($isPksf && $isSupervisor && $status === 'SUBMITTED'))
@include('visits.partials.delete_observation_modal')
@endif

{{-- Resolve Observation Modal — supervisor only at PO_APPROVED stage --}}
@if($isPksf && $isSupervisor && $status === 'PO_APPROVED')
@include('visits.partials.resolve_observation_modal')
@include('visits.partials.accept_resolution_modal')
@include('visits.partials.reject_resolution_modal')
@endif

{{-- CO Resolve Observation Modal — CO at PO_APPROVED stage --}}
@if($isPksf && !$isSupervisor && $isMyDesk && $status === 'PO_APPROVED')
@include('visits.partials.co_resolve_observation_modal')
@endif

{{-- Edit Observation Modal --}}
@if($canAddObs || ($isPksf && $isSupervisor))
@include('visits.partials.edit_observation_modal', ['visit' => $visit])
@endif

{{-- Comment Modal (Add / Edit) --}}
@if($canComment)
<div class="modal fade" id="modal-comment" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:12px;border:none;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.12)">

            {{-- Blue accent header --}}
            <div style="background:#eff6ff;padding:1.25rem 1.5rem;border-bottom:1px solid #bfdbfe">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:34px;height:34px;border-radius:8px;background:#dbeafe;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i id="comment-modal-icon" class="bi bi-chat-text" style="font-size:1rem;color:#2563eb"></i>
                    </div>
                    <div>
                        <div class="fw-bold" id="comment-modal-title" style="font-size:.95rem;color:var(--sl-text)">Add Comment</div>
                        <div style="font-size:.75rem;color:#2563eb;margin-top:.1rem">Observation <span id="comment-obs-label"></span></div>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                </div>
            </div>

            <form id="comment-form" enctype="multipart/form-data" novalidate>
                @csrf

                <div class="modal-body" style="padding:1.5rem;max-height:70vh;overflow-y:auto">

                    {{-- Inline error box --}}
                    <div id="comment-error-box" class="alert alert-danger border-0 mb-3 d-none" style="border-radius:8px;font-size:.83rem">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <span id="comment-error-text"></span>
                    </div>

                    {{-- Comment text --}}
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.82rem;font-weight:600">
                            Comment <span class="text-danger">*</span>
                        </label>
                        <textarea name="comment_detail" id="comment-detail" class="form-control"
                                  rows="5" maxlength="5000"
                                  style="font-size:.875rem;border-radius:8px;resize:vertical;min-height:100px"
                                  placeholder="Enter your comment…" required></textarea>
                    </div>

                    {{-- Existing attachments shown in edit mode --}}
                    <div id="comment-existing-atts" class="mb-2 d-flex flex-column gap-1" style="display:none!important"></div>

                    {{-- New file attachments --}}
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label mb-0" style="font-size:.82rem;font-weight:600">Attachments</label>
                            <span id="comment-file-counter" style="font-size:.75rem;color:var(--sl-muted)">0 of 3 files</span>
                        </div>
                        {{-- Hidden real file input --}}
                        <input type="file" id="comment-file-input" name="attachments[]" multiple
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" style="display:none">
                        {{-- Styled trigger --}}
                        <button type="button" id="comment-attach-btn"
                                style="width:100%;border:1.5px dashed var(--sl-border);border-radius:8px;
                                       background:#fafafa;padding:.6rem 1rem;font-size:.82rem;font-weight:600;
                                       color:var(--sl-primary);cursor:pointer;transition:all .15s;
                                       display:flex;align-items:center;justify-content:center;gap:.5rem">
                            <i class="bi bi-paperclip" style="font-size:.95rem"></i>
                            Attach Files
                            <span style="font-weight:400;color:var(--sl-muted);font-size:.75rem">· PDF, Word, Excel, Images · max 30MB</span>
                        </button>
                        {{-- Selected files as badge rows --}}
                        <div id="comment-file-list" class="mt-2 d-flex flex-column gap-1"></div>
                    </div>

                </div>

                <div class="modal-footer" style="padding:.75rem 1.5rem 1.25rem;border-top:1px solid var(--sl-border);gap:.5rem">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                            style="border-radius:8px;font-size:.875rem;font-weight:600;min-width:90px">
                        Cancel
                    </button>
                    <button type="submit" id="btn-save-comment" class="btn"
                            style="background:var(--sl-primary);color:#fff;border-radius:8px;font-size:.875rem;font-weight:600;min-width:150px">
                        <i class="bi bi-send me-1"></i>Save Comment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Comment Confirmation Modal --}}
<div class="modal fade" id="modal-delete-comment" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px">
        <div class="modal-content" style="border-radius:12px;border:none;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.15)">

            <div style="background:#fff1f2;padding:1.25rem 1.5rem;border-bottom:1px solid #fecdd3">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:40px;height:40px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="bi bi-trash3-fill" style="font-size:1.1rem;color:#dc2626"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:.9rem;color:#991b1b">Delete Comment</div>
                        <div style="font-size:.75rem;color:#b91c1c;margin-top:.1rem">This action cannot be undone</div>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                </div>
            </div>

            <div class="modal-body" style="padding:1.25rem 1.5rem;font-size:.875rem;color:var(--sl-text)">
                <p class="mb-2">This comment and all its attachments will be permanently deleted.</p>
                <p class="mb-0" style="font-size:.82rem;color:#dc2626;font-weight:600">
                    <i class="bi bi-exclamation-circle me-1"></i>This action cannot be undone.
                </p>
            </div>

            <div class="modal-footer" style="padding:.75rem 1.5rem 1.25rem;border-top:1px solid var(--sl-border);gap:.5rem">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                        style="border-radius:8px;font-size:.875rem;font-weight:600;min-width:90px">
                    Cancel
                </button>
                <button type="button" id="btn-confirm-delete-comment" class="btn btn-danger"
                        style="border-radius:8px;font-size:.875rem;font-weight:600;min-width:130px">
                    <i class="bi bi-trash3 me-1"></i>Delete
                </button>
            </div>

        </div>
    </div>
</div>
@endif

{{-- ── Delete Visit Confirmation Modal ─────────────────────────────────── --}}
@if($canDeleteVisit)
<div class="modal fade" id="modal-delete-visit" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width:540px">
        <div class="modal-content" style="border-radius:12px;border:none;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.15)">

            {{-- Red accent header --}}
            <div style="background:#fff1f2;padding:1.5rem 1.75rem 1.25rem;border-bottom:1px solid #fecdd3">
                <div class="d-flex align-items-center gap-3">
                    {{-- Icon circle --}}
                    <div style="width:46px;height:46px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="bi bi-trash3-fill" style="font-size:1.2rem;color:#dc2626"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:.95rem;color:#991b1b">Delete Visit</div>
                        <div style="font-size:.78rem;color:#b91c1c;margin-top:.1rem;font-family:monospace">{{ $visit->visit_code }}</div>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                </div>
            </div>

            {{-- Body --}}
            <div class="modal-body" style="padding:1.5rem 1.75rem">
                <p style="font-size:.9rem;margin-bottom:.75rem;color:var(--sl-text)">
                    You are about to permanently delete this visit along with all its content.
                </p>
                {{-- What will be deleted --}}
                <div style="background:#f8fafc;border:1px solid var(--sl-border);border-radius:8px;padding:.85rem 1rem;font-size:.82rem;color:var(--sl-muted)">
                    <div class="fw-semibold mb-1" style="color:var(--sl-text);font-size:.82rem">The following will be permanently removed:</div>
                    <ul class="mb-0 ps-3" style="line-height:1.8">
                        <li>All observations under this visit</li>
                        <li>All comments on each observation</li>
                        <li>All uploaded attachments</li>
                    </ul>
                </div>
                <p class="mt-3 mb-0" style="font-size:.82rem;color:#dc2626;font-weight:600">
                    <i class="bi bi-exclamation-circle me-1"></i>This action cannot be undone.
                </p>
            </div>

            {{-- Footer --}}
            <div class="modal-footer" style="padding:.75rem 1.75rem 1.25rem;border-top:1px solid var(--sl-border);gap:.5rem">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                        style="border-radius:8px;font-size:.875rem;font-weight:600;min-width:90px">
                    Cancel
                </button>
                <form action="{{ route('visits.destroy', $visit->id) }}" method="POST" id="delete-visit-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" id="btn-confirm-delete"
                            style="border-radius:8px;font-size:.875rem;font-weight:600;min-width:140px">
                        <i class="bi bi-trash3 me-1"></i>Yes, Delete Visit
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
(function () {

    // Initialise Bootstrap tooltips for disabled-button hints etc.
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el);
    });

    // ── Flash message helper ────────────────────────────────────────────────
    // Used by AJAX modals that can't set Laravel session flash.
    // Shows the alert and reloads after a short delay to refresh the card data.
    window.flashAndReload = function (msg) {
        const $alert = $(`
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>${msg}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        $('.visit-layout').before($alert);
        setTimeout(() => location.reload(), 1200);
    };

    window.flashSuccess = function (msg) {
        const $alert = $(`
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>${msg}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        $('.visit-layout').before($alert);
    };

    // ── Add Observation ────────────────────────────────────────────────────
    @if($canAddObs)
    initAddObsForm({{ $visit->id }});
    @endif

    // ── View Observation ───────────────────────────────────────────────────
    initViewObsModal({{ $visit->id }});

    // ── Edit Observation ───────────────────────────────────────────────────
    @if($canAddObs || ($isPksf && $isSupervisor))
    initEditObsForm({{ $visit->id }});
    @endif

    // ── Delete Observation — custom modal instead of browser confirm() ──────
    let pendingDeleteObsId = null;

    $(document).on('click', '.btn-delete-obs', function () {
        pendingDeleteObsId = $(this).data('obs-id');
        const idx = $(this).data('idx');
        $('#delete-obs-idx').text(`#${idx}`);
        new bootstrap.Modal(document.getElementById('modal-delete-obs')).show();
    });

    $('#btn-confirm-delete-obs').on('click', function () {
        if (!pendingDeleteObsId) return;
        const obsId   = pendingDeleteObsId;
        const visitId = {{ $visit->id }};
        const btn     = $(this).prop('disabled', true)
                               .html('<span class="spinner-border spinner-border-sm me-1"></span>Deleting…');

        $.ajax({
            url: `/visits/${visitId}/observations/${obsId}`,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function (r) {
                bootstrap.Modal.getInstance(document.getElementById('modal-delete-obs')).hide();
                if (r.success) {
                    $(`#obs-card-${obsId}`).fadeOut(300, function () {
                        $(this).remove();
                        flashSuccess('Observation deleted successfully.');
                    });
                } else {
                    alert(r.message);
                }
            },
            error: function (xhr) {
                bootstrap.Modal.getInstance(document.getElementById('modal-delete-obs')).hide();
                alert(xhr.responseJSON?.message ?? 'Failed to delete.');
            },
            complete: function () {
                btn.prop('disabled', false).html('<i class="bi bi-trash3 me-1"></i>Delete');
                pendingDeleteObsId = null;
            }
        });
    });

    // ── Resolution controls ─────────────────────────────────────────────────
    // CO resolve — custom modal (marks as PENDING_RESOLVED for supervisor review)
    let pendingCoResolveObsId = null;
    $(document).on('click', '.btn-co-resolve-obs', function () {
        pendingCoResolveObsId = $(this).data('obs-id');
        $('#co-resolve-obs-idx').text(`#${$(this).data('idx')}`);
        new bootstrap.Modal(document.getElementById('modal-co-resolve-obs')).show();
    });
    $('#btn-confirm-co-resolve-obs').on('click', function () {
        if (!pendingCoResolveObsId) return;
        const obsId = pendingCoResolveObsId;
        const $btn = $(this).prop('disabled', true)
                            .html('<span class="spinner-border spinner-border-sm me-1"></span>Processing…');
        $.post(`/observations/${obsId}/mark-pending`, { _token: '{{ csrf_token() }}' }, function (r) {
            bootstrap.Modal.getInstance(document.getElementById('modal-co-resolve-obs')).hide();
            if (r.success) {
                window.flashAndReload('Observation marked as resolved.');
            } else {
                alert(r.message ?? 'Failed.');
            }
        }).fail(function (xhr) {
            bootstrap.Modal.getInstance(document.getElementById('modal-co-resolve-obs')).hide();
            alert(xhr.responseJSON?.message ?? 'Failed to resolve observation.');
        }).always(function () {
            $btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i>Yes, Resolve');
            pendingCoResolveObsId = null;
        });
    });
    // SO resolve — custom modal instead of confirm()
    let pendingResolveObsId = null;
    $(document).on('click', '.btn-resolve-obs', function () {
        pendingResolveObsId = $(this).data('obs-id');
        const idx = $(this).data('idx');
        $('#resolve-obs-idx').text(`#${idx}`);
        new bootstrap.Modal(document.getElementById('modal-resolve-obs')).show();
    });
    $('#btn-confirm-resolve-obs').on('click', function () {
        if (!pendingResolveObsId) return;
        const obsId = pendingResolveObsId;
        const $btn  = $(this).prop('disabled', true)
                             .html('<span class="spinner-border spinner-border-sm me-1"></span>Processing…');
        $.post(`/observations/${obsId}/resolve`, { _token: '{{ csrf_token() }}' }, function (r) {
            bootstrap.Modal.getInstance(document.getElementById('modal-resolve-obs')).hide();
            if (r.success) {
                window.flashAndReload('Observation marked as resolved.');
            } else {
                alert(r.message ?? 'Failed.');
            }
        }).fail(function (xhr) {
            bootstrap.Modal.getInstance(document.getElementById('modal-resolve-obs')).hide();
            alert(xhr.responseJSON?.message ?? 'Failed to resolve.');
        }).always(function () {
            $btn.prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i>Confirm Resolve');
            pendingResolveObsId = null;
        });
    });
    // Accept resolution — custom modal
    let pendingAcceptObsId = null;
    $(document).on('click', '.btn-approve-pending', function () {
        pendingAcceptObsId = $(this).data('obs-id');
        $('#accept-resolution-idx').text(`#${$(this).data('obs-id')}`);
        new bootstrap.Modal(document.getElementById('modal-accept-resolution')).show();
    });
    $('#btn-confirm-accept-resolution').on('click', function () {
        if (!pendingAcceptObsId) return;
        const obsId = pendingAcceptObsId;
        const $btn = $(this).prop('disabled', true)
                            .html('<span class="spinner-border spinner-border-sm me-1"></span>Processing…');
        $.post(`/observations/${obsId}/approve-pending`, { _token: '{{ csrf_token() }}' }, function (r) {
            bootstrap.Modal.getInstance(document.getElementById('modal-accept-resolution')).hide();
            if (r.success) window.flashAndReload('Resolution accepted. Observation marked as resolved.');
            else alert(r.message ?? 'Failed.');
        }).fail(function (xhr) {
            bootstrap.Modal.getInstance(document.getElementById('modal-accept-resolution')).hide();
            alert(xhr.responseJSON?.message ?? 'Failed to accept resolution.');
        }).always(function () {
            $btn.prop('disabled', false).html('<i class="bi bi-check-all me-1"></i>Yes, Accept');
            pendingAcceptObsId = null;
        });
    });

    // Reject resolution — custom modal
    let pendingRejectObsId = null;
    $(document).on('click', '.btn-reject-pending', function () {
        pendingRejectObsId = $(this).data('obs-id');
        $('#reject-resolution-idx').text(`#${$(this).data('obs-id')}`);
        new bootstrap.Modal(document.getElementById('modal-reject-resolution')).show();
    });
    $('#btn-confirm-reject-resolution').on('click', function () {
        if (!pendingRejectObsId) return;
        const obsId = pendingRejectObsId;
        const $btn = $(this).prop('disabled', true)
                            .html('<span class="spinner-border spinner-border-sm me-1"></span>Processing…');
        $.post(`/observations/${obsId}/reopen`, { _token: '{{ csrf_token() }}' }, function (r) {
            bootstrap.Modal.getInstance(document.getElementById('modal-reject-resolution')).hide();
            if (r.success) window.flashAndReload('Resolution rejected. Observation reopened.');
            else alert(r.message ?? 'Failed.');
        }).fail(function (xhr) {
            bootstrap.Modal.getInstance(document.getElementById('modal-reject-resolution')).hide();
            alert(xhr.responseJSON?.message ?? 'Failed to reject resolution.');
        }).always(function () {
            $btn.prop('disabled', false).html('<i class="bi bi-x-circle me-1"></i>Yes, Reject');
            pendingRejectObsId = null;
        });
    });
    $(document).on('click', '.btn-reopen-obs', function () {
        const obsId = $(this).data('obs-id');
        resolutionAction(obsId, 'reopen', 'Reopen this observation?');
    });

    function resolutionAction(obsId, action, confirmMsg) {
        if (!confirm(confirmMsg)) return;
        $.post(`/observations/${obsId}/${action}`, { _token: '{{ csrf_token() }}' }, function (r) {
            if (r.success) location.reload();
        }).fail(function (xhr) {
            alert(xhr.responseJSON?.message ?? 'Action failed.');
        });
    }

    // ── Send to PO — file attachment ────────────────────────────────────────
    @if($canSendToPo)
    (function () {
        let spoFiles = [];

        const spoInput      = document.getElementById('spo-file-input');
        const spoAttachBtn  = document.getElementById('spo-attach-btn');
        const spoFileList   = document.getElementById('spo-file-list');
        const spoCounter    = document.getElementById('spo-file-counter');

        if (!spoInput) return;

        spoAttachBtn.addEventListener('click', () => spoInput.click());
        spoAttachBtn.addEventListener('mouseenter', () => {
            if (!spoAttachBtn.disabled) spoAttachBtn.style.borderColor = 'var(--sl-primary)';
        });
        spoAttachBtn.addEventListener('mouseleave', () => {
            if (!spoAttachBtn.disabled) spoAttachBtn.style.borderColor = 'var(--sl-border)';
        });

        spoInput.addEventListener('change', function () {
            const incoming = [...this.files];
            if (spoFiles.length + incoming.length > 3) {
                alert('You can only attach a maximum of 3 files.');
                this.value = '';
                return;
            }
            incoming.forEach(f => spoFiles.push(f));
            this.value = '';
            renderSpoFileList();
        });

        function renderSpoFileList() {
            spoFileList.innerHTML = '';

            // Rebuild file inputs so native form POST picks them up
            document.querySelectorAll('.spo-hidden-file').forEach(el => el.remove());
            const form = spoAttachBtn.closest('form');

            spoFiles.forEach((file, idx) => {
                // Hidden file input carrying the actual File object
                const dt    = new DataTransfer();
                dt.items.add(file);
                const fi    = document.createElement('input');
                fi.type     = 'file';
                fi.name     = 'attachments[]';
                fi.className = 'spo-hidden-file';
                fi.style.display = 'none';
                fi.files    = dt.files;
                form.appendChild(fi);

                // Badge row
                const size = (file.size / 1024 / 1024).toFixed(2);
                const row  = document.createElement('div');
                row.className = 'badge bg-light text-dark border p-2 d-flex align-items-center justify-content-between w-100 shadow-sm';
                row.innerHTML = `
                    <div class="d-flex align-items-center text-truncate pe-2">
                        <i class="bi bi-paperclip me-2 text-primary"></i>
                        <span class="text-truncate fw-normal" style="max-width:260px">${file.name}</span>
                        <span class="text-muted small ms-2 fw-light" style="white-space:nowrap">(${size} MB)</span>
                    </div>
                    <button type="button" class="btn-close spo-remove-file" data-idx="${idx}"
                            style="font-size:.65rem" title="Remove"></button>
                `;
                spoFileList.appendChild(row);
            });

            spoFileList.querySelectorAll('.spo-remove-file').forEach(btn => {
                btn.addEventListener('click', function () {
                    spoFiles.splice(parseInt(this.dataset.idx), 1);
                    renderSpoFileList();
                });
            });

            spoCounter.textContent   = `${spoFiles.length} of 3 files`;
            spoCounter.style.color   = spoFiles.length >= 3 ? '#dc2626' : 'var(--sl-muted)';
            spoAttachBtn.disabled    = spoFiles.length >= 3;
            spoAttachBtn.style.opacity = spoFiles.length >= 3 ? '.45' : '1';
            spoAttachBtn.style.cursor  = spoFiles.length >= 3 ? 'not-allowed' : 'pointer';
        }
    })();
    @endif

    // ── Comments ────────────────────────────────────────────────────────────
    @if($canComment)
    // PHP → JS context needed for permission checks when building bubbles
    const currentEmpId          = '{{ auth()->user()->emp_id }}';
    const currentIsSupervisor   = {{ json_encode($isPksf && $isSupervisor) }};

    let currentCommentObsId = null;
    let editingCommentId    = null;
    let commentFiles        = [];   // newly selected File objects
    let commentRemovedAtts  = [];   // existing attachment IDs to remove on update

    const commentFileInput = document.getElementById('comment-file-input');
    const commentAttachBtn = document.getElementById('comment-attach-btn');

    commentAttachBtn.addEventListener('click', () => commentFileInput.click());
    commentAttachBtn.addEventListener('mouseenter', () => {
        if (!commentAttachBtn.disabled) commentAttachBtn.style.borderColor = 'var(--sl-primary)';
    });
    commentAttachBtn.addEventListener('mouseleave', () => {
        if (!commentAttachBtn.disabled) commentAttachBtn.style.borderColor = 'var(--sl-border)';
    });

    commentFileInput.addEventListener('change', function () {
        const incoming      = [...this.files];
        const existingCount = document.querySelectorAll('#comment-existing-atts .badge').length;
        if (existingCount + commentFiles.length + incoming.length > 3) {
            showCommentError('You can only attach a maximum of 3 files.');
            this.value = '';
            return;
        }
        incoming.forEach(f => commentFiles.push(f));
        this.value = '';
        renderCommentFileList();
    });

    function renderCommentFileList() {
        const list = document.getElementById('comment-file-list');
        list.innerHTML = '';
        commentFiles.forEach((file, idx) => {
            const size = (file.size / 1024 / 1024).toFixed(2);
            const row  = document.createElement('div');
            row.className = 'badge bg-light text-dark border p-2 d-flex align-items-center justify-content-between w-100 shadow-sm';
            row.innerHTML = `
                <div class="d-flex align-items-center text-truncate pe-2">
                    <i class="bi bi-paperclip me-2 text-primary"></i>
                    <span class="text-truncate fw-normal" style="max-width:260px">${file.name}</span>
                    <span class="text-muted small ms-2 fw-light" style="white-space:nowrap">(${size} MB)</span>
                </div>
                <button type="button" class="btn-close comment-remove-file" data-idx="${idx}"
                        style="font-size:.65rem" title="Remove"></button>
            `;
            list.appendChild(row);
        });
        list.querySelectorAll('.comment-remove-file').forEach(btn => {
            btn.addEventListener('click', function () {
                commentFiles.splice(parseInt(this.dataset.idx), 1);
                renderCommentFileList();
            });
        });
        updateCommentFileCounter();
    }

    function renderCommentExistingAtts(atts) {
        const container = document.getElementById('comment-existing-atts');
        container.innerHTML = '';
        commentRemovedAtts  = [];
        if (!atts || !atts.length) { container.style.display = 'none'; return; }
        container.style.display = 'flex';

        const label = document.createElement('div');
        label.style.cssText = 'font-size:.78rem;font-weight:600;color:var(--sl-muted);margin-bottom:.25rem';
        label.textContent   = 'Existing Attachments';
        container.appendChild(label);

        atts.forEach(att => {
            const row = document.createElement('div');
            row.className = 'badge bg-light text-dark border p-2 d-flex align-items-center justify-content-between w-100 shadow-sm';
            row.id        = `comment-att-${att.id}`;
            row.innerHTML = `
                <div class="d-flex align-items-center text-truncate pe-2">
                    <i class="bi bi-paperclip me-2 text-secondary"></i>
                    <a href="${att.url}" target="_blank" class="text-truncate fw-normal text-decoration-none" style="max-width:240px;color:inherit">${att.file_name}</a>
                    <span class="text-muted small ms-2 fw-light" style="white-space:nowrap">${att.file_size ?? ''}</span>
                </div>
                <button type="button" class="btn-close comment-remove-existing" data-att-id="${att.id}"
                        style="font-size:.65rem" title="Remove"></button>
            `;
            container.appendChild(row);
        });
        container.querySelectorAll('.comment-remove-existing').forEach(btn => {
            btn.addEventListener('click', function () {
                const attId = parseInt(this.dataset.attId);
                commentRemovedAtts.push(attId);
                document.getElementById(`comment-att-${attId}`).remove();
                updateCommentFileCounter();
            });
        });
        updateCommentFileCounter();
    }

    function updateCommentFileCounter() {
        const existingCount = document.querySelectorAll('#comment-existing-atts .badge').length;
        const total   = existingCount + commentFiles.length;
        const counter = document.getElementById('comment-file-counter');
        counter.textContent  = `${total} of 3 files`;
        counter.style.color  = total >= 3 ? '#dc2626' : 'var(--sl-muted)';
        const atLimit              = total >= 3;
        commentAttachBtn.disabled      = atLimit;
        commentAttachBtn.style.opacity = atLimit ? '.45' : '1';
        commentAttachBtn.style.cursor  = atLimit ? 'not-allowed' : 'pointer';
    }

    function resetCommentModal() {
        editingCommentId   = null;
        commentFiles       = [];
        commentRemovedAtts = [];
        $('#comment-detail').val('');
        $('#comment-file-list').empty();
        document.getElementById('comment-existing-atts').innerHTML = '';
        document.getElementById('comment-existing-atts').style.display = 'none';
        $('#comment-file-counter').text('0 of 3 files').css('color', 'var(--sl-muted)');
        commentAttachBtn.disabled      = false;
        commentAttachBtn.style.opacity = '1';
        commentAttachBtn.style.cursor  = 'pointer';
        hideCommentError();
        $('#btn-save-comment').prop('disabled', false)
            .html('<i class="bi bi-send me-1"></i>Save Comment');
    }

    // ── DOM helpers: update comment thread in-place (no page reload) ─────────

    // Escape for text content (inside tags)
    function escHtml(str) {
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(str == null ? '' : String(str)));
        return d.innerHTML;
    }
    // Escape for HTML attribute values — must also escape " to avoid breaking the attribute
    function escAttr(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    // Build a comment bubble HTML string from server response data
    function buildCommentBubble(comment, obsId) {
        const isMyComment = String(comment.created_by) === String(currentEmpId);
        const isEditable  = comment.is_editable && isMyComment;
        const isDeletable = (isMyComment || currentIsSupervisor) && comment.is_editable;

        let attHtml = '';
        if (comment.attachments && comment.attachments.length) {
            const chips = comment.attachments.map(a =>
                `<a href="${escAttr(a.url)}" target="_blank" class="att-chip">
                    <i class="bi bi-paperclip"></i>${escHtml(a.file_name)}
                </a>`
            ).join('');
            attHtml = `<div class="mt-2 d-flex flex-wrap gap-1">${chips}</div>`;
        }


        let actionHtml = '';
        if (isEditable || isDeletable) {
            // JSON for data-attachments must be attribute-safe (& and " escaped)
            const attsJson = escAttr(JSON.stringify(comment.attachments || []));
            const editBtn  = isEditable ? `
                <button class="btn btn-sm btn-edit-comment"
                        style="padding:.1rem .4rem;font-size:.7rem;border-radius:5px;background:#f1f5f9;border:none"
                        data-comment-id="${comment.id}" data-obs-id="${obsId}"
                        data-comment-detail="${escAttr(comment.comment_detail)}"
                        data-attachments="${attsJson}">
                    <i class="bi bi-pencil"></i>
                </button>` : '';
            const delBtn = isDeletable ? `
                <button class="btn btn-sm btn-delete-comment"
                        style="padding:.1rem .4rem;font-size:.7rem;border-radius:5px;background:#fff1f2;color:#e11d48;border:none"
                        data-comment-id="${comment.id}" data-obs-id="${obsId}">
                    <i class="bi bi-trash3"></i>
                </button>` : '';
            actionHtml = `<span class="ms-auto d-flex gap-1">${editBtn}${delBtn}</span>`;
        }

        return `
            <div class="comment-bubble comment-${escHtml(comment.comment_source)}" id="comment-bubble-${comment.id}">
                <div style="white-space:pre-wrap">${escHtml(comment.comment_detail)}</div>
                ${attHtml}
                <div class="comment-meta">
                    <span>${escHtml(comment.author_name ?? comment.created_by)}</span>
                    <span>&bull;</span>
                    <span>${escHtml(comment.comment_source)}</span>
                    <span>${escHtml(comment.updated_at ?? '')}</span>
                    ${actionHtml}
                </div>
            </div>`;
    }

    // Append a new bubble to the card, creating the thread wrapper if needed
    function addCommentToDOM(comment, obsId) {
        const $card    = $(`#obs-card-${obsId}`);
        const $section = $card.find('.comment-section');

        // Remove the "No comments yet" placeholder if present
        $section.find('.text-muted.text-center').remove();

        let $thread = $section.find('.comment-thread');
        if (!$thread.length) {
            $section.append('<div class="comment-thread"></div>');
            $thread = $section.find('.comment-thread');
        }
        $thread.append(buildCommentBubble(comment, obsId));
        shiftCommentCount($card, +1);
    }

    // Replace an existing bubble in-place
    function updateCommentInDOM(comment, obsId) {
        const $bubble = $(`#comment-bubble-${comment.id}`);
        if ($bubble.length) $bubble.replaceWith(buildCommentBubble(comment, obsId));
    }

    // Remove a bubble; restore "No comments yet" if thread becomes empty
    function removeCommentFromDOM(commentId, obsId) {
        $(`#comment-bubble-${commentId}`).remove();
        const $card   = $(`#obs-card-${obsId}`);
        const $thread = $card.find('.comment-thread');
        shiftCommentCount($card, -1);
        if ($thread.length && !$thread.children().length) {
            $thread.replaceWith(
                `<div class="text-muted text-center py-2"
                      style="font-size:.8rem;border:1px dashed var(--sl-border);border-radius:8px">
                    No comments yet
                </div>`
            );
        }
    }

    // Update the "(N)" count in the body label and the header badge chip
    function shiftCommentCount($card, delta) {
        // Body label: "Comments (N)"
        const $span = $card.find('.comment-section .info-label span');
        const cur   = parseInt($span.text().replace(/[()]/g, '')) || 0;
        const next  = Math.max(0, cur + delta);
        $span.text(`(${next})`);

        // Header badge chip
        const obsId  = $card.attr('id').replace('obs-card-', '');
        const $badge = $(`#obs-comment-count-${obsId}`);
        $badge.html(`<i class="bi bi-chat-text" style="font-size:.65rem"></i>${next}`);
        if (next > 0) {
            $badge.css({ display: 'inline-flex', background: '#eff6ff', color: '#2563eb' });
        } else {
            $badge.hide();
        }
    }

    // ── Modal open handlers ──────────────────────────────────────────────────

    $(document).on('click', '.btn-add-comment', function () {
        currentCommentObsId = $(this).data('obs-id');
        resetCommentModal();
        $('#comment-modal-title').text('Add Comment');
        $('#comment-modal-icon').attr('class', 'bi bi-chat-text');
        $('#comment-form').attr('action', `/observations/${currentCommentObsId}/comments`);
        new bootstrap.Modal(document.getElementById('modal-comment')).show();
    });

    $(document).on('click', '.btn-edit-comment', function () {
        const cid   = $(this).data('comment-id');
        const obsId = $(this).data('obs-id');
        const atts  = $(this).data('attachments') || [];

        currentCommentObsId = obsId;
        resetCommentModal();
        editingCommentId = cid;

        $('#comment-modal-title').text('Update Comment');
        $('#comment-modal-icon').attr('class', 'bi bi-pencil');
        $('#btn-save-comment').html('<i class="bi bi-send me-1"></i>Update Comment');
        $('#comment-detail').val($(this).data('comment-detail'));
        $('#comment-form').attr('action', `/observations/${obsId}/comments/${cid}`);
        renderCommentExistingAtts(atts);
        new bootstrap.Modal(document.getElementById('modal-comment')).show();
    });

    // Delete: open confirmation modal, then fire request on confirm
    let pendingDeleteCommentId  = null;
    let pendingDeleteCommentObs = null;

    $(document).on('click', '.btn-delete-comment', function () {
        pendingDeleteCommentId  = $(this).data('comment-id');
        pendingDeleteCommentObs = $(this).data('obs-id');
        new bootstrap.Modal(document.getElementById('modal-delete-comment')).show();
    });

    $('#btn-confirm-delete-comment').on('click', function () {
        if (!pendingDeleteCommentId) return;
        const commentId = pendingDeleteCommentId;
        const obsId     = pendingDeleteCommentObs;
        const $btn      = $(this).prop('disabled', true)
                              .html('<span class="spinner-border spinner-border-sm me-1"></span>Deleting…');

        $.ajax({
            url:  `/observations/${obsId}/comments/${commentId}`,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function (r) {
                bootstrap.Modal.getInstance(document.getElementById('modal-delete-comment')).hide();
                if (r.success) {
                    removeCommentFromDOM(commentId, obsId);
                    window.flashSuccess('Comment deleted.');
                }
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="bi bi-trash3 me-1"></i>Delete');
                pendingDeleteCommentId  = null;
                pendingDeleteCommentObs = null;
            }
        });
    });

    // Submit: inject new/updated bubble into DOM, no reload
    $('#comment-form').on('submit', function (e) {
        e.preventDefault();
        hideCommentError();

        const fd = new FormData(this);
        fd.delete('attachments[]');
        commentFiles.forEach(f => fd.append('attachments[]', f));
        commentRemovedAtts.forEach(id => fd.append('remove_attachments[]', id));
        // Route is Route::post — no method spoofing needed

        const isEdit    = !!editingCommentId;
        const savedObsId = currentCommentObsId;
        const btn = $('#btn-save-comment').prop('disabled', true)
                       .html('<span class="spinner-border spinner-border-sm me-1"></span>Saving…');

        $.ajax({
            url:         $(this).attr('action'),
            type:        'POST',
            data:        fd,
            processData: false,
            contentType: false,
            success: function (r) {
                if (r.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modal-comment')).hide();
                    if (isEdit) {
                        updateCommentInDOM(r.comment, savedObsId);
                        window.flashSuccess('Comment updated.');
                    } else {
                        addCommentToDOM(r.comment, savedObsId);
                        window.flashSuccess('Comment added.');
                    }
                } else {
                    showCommentError(r.message ?? 'Failed to save comment.');
                    btn.prop('disabled', false).html('<i class="bi bi-send me-1"></i>Save Comment');
                }
            },
            error: function (xhr) {
                const errs = xhr.responseJSON?.errors;
                const msg  = errs
                    ? Object.values(errs).flat().join(' ')
                    : (xhr.responseJSON?.message ?? 'An error occurred. Please try again.');
                showCommentError(msg);
                btn.prop('disabled', false).html('<i class="bi bi-send me-1"></i>Save Comment');
            }
        });
    });

    function showCommentError(msg) {
        $('#comment-error-text').text(msg);
        $('#comment-error-box').removeClass('d-none');
        $('#modal-comment .modal-body').scrollTop(0);
    }
    function hideCommentError() { $('#comment-error-box').addClass('d-none'); }
    @endif

    // ── Delete Visit ───────────────────────────────────────────────────────
    @if($canDeleteVisit)
    $('#delete-visit-form').on('submit', function () {
        $('#btn-confirm-delete').prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-1"></span>Deleting…');
    });
    @endif

    // ── Toggle observation body (chevron rotates on expand) ────────────────
    // Ignore clicks that originate from buttons — those open modals, not the toggle.
    $(document).on('click', '.obs-card-head', function (e) {
        if ($(e.target).closest('button').length) return;

        const $clicked  = $(this);
        const isOpen    = $clicked.hasClass('expanded');

        // Collapse all open cards first
        $('.obs-card-head.expanded').each(function () {
            $(this).removeClass('expanded')
                   .find('.obs-toggle-icon').css('transform', 'rotate(0deg)');
            $(this).next('.obs-body').slideUp(180);
        });

        // If the clicked card was closed, open it
        if (!isOpen) {
            $clicked.addClass('expanded')
                    .find('.obs-toggle-icon').css('transform', 'rotate(180deg)');
            $clicked.next('.obs-body').slideDown(180);
        }
    });

    // Expand first obs by default
    $firstHead.find('.obs-toggle-icon').css('transform', 'rotate(180deg)');

})();
</script>
@endpush
