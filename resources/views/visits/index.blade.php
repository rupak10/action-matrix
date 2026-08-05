@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
.stat-card {
    border: 1px solid var(--sl-border);
    border-radius: var(--sl-radius);
    background: var(--sl-surface);
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: box-shadow .18s;
    cursor: pointer;
    text-decoration: none;
}
.stat-card:hover { box-shadow: 0 6px 20px -4px rgba(27,58,58,.1); }
.stat-card .stat-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; flex-shrink: 0;
}
.stat-card .stat-value { font-size: 1.75rem; font-weight: 700; line-height: 1; color: var(--sl-text); }
.stat-card .stat-label { font-size: .78rem; color: var(--sl-muted); margin-top: .15rem; text-transform: uppercase; letter-spacing: .04em; }

.filter-bar { background: var(--sl-surface); border: 1px solid var(--sl-border); border-radius: var(--sl-radius); padding: 1rem 1.25rem; }

.visits-table th { font-size: .74rem; text-transform: uppercase; letter-spacing: .05em; color: var(--sl-muted); font-weight: 600; border-bottom: 2px solid var(--sl-border); white-space: nowrap; }
.visits-table td { vertical-align: middle; font-size: .875rem; }
.visits-table tbody tr:hover { background: var(--sl-primary-soft); }

.badge-status { font-size: .72rem; font-weight: 600; padding: .3em .65em; border-radius: 6px; letter-spacing: .02em; white-space: nowrap; }
.badge-SAVED         { background: #f1f5f9; color: #64748b; }
.badge-SUBMITTED     { background: #eff6ff; color: #2563eb; }
.badge-REJECTED      { background: #fff1f2; color: #e11d48; }
.badge-PO_SO_REVIEW  { background: #fff7ed; color: #c2410c; }
.badge-PO_REVIEW     { background: #fefce8; color: #92400e; }
.badge-PO_SUBMITTED  { background: #f0fdf4; color: #166534; }
.badge-PO_APPROVED   { background: #dcfce7; color: #15803d; }

.obs-pill { display: inline-flex; align-items: center; gap: .3rem; font-size: .72rem; font-weight: 600; padding: .2em .55em; border-radius: 20px; }
.obs-total    { background: #f1f5f9; color: #475569; }
.obs-resolved { background: #dcfce7; color: #15803d; }
.obs-pending  { background: #fef3c7; color: #92400e; }

.priority-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; flex-shrink: 0; }
.priority-HIGH   { background: #ef4444; }
.priority-MEDIUM { background: #f59e0b; }
.priority-LOW    { background: #94a3b8; }

.my-desk-row { border-left: 3px solid var(--sl-primary); }
.visit-code-link { font-family: 'Courier New', monospace; font-weight: 700; font-size: .82rem; color: var(--sl-primary); }

.view-tab { cursor: pointer; padding: .4rem .9rem; border-radius: 8px; font-size: .82rem; font-weight: 600; border: 1px solid var(--sl-border); background: transparent; color: var(--sl-muted); transition: all .15s; }
.view-tab.active, .view-tab:hover { background: var(--sl-primary); color: #fff; border-color: var(--sl-primary); }
</style>
@endpush

@section('content')
@php $user = auth()->user(); @endphp

<div class="container-fluid pt-1 pb-4">

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3 gap-2 flex-wrap">
        <div>
            <h5 class="fw-bold mb-0" style="color:var(--sl-text)">Visits</h5>
            <div class="text-sl-muted" style="font-size:.8rem">Field visits and PO observations</div>
        </div>
        @if($user->isPksf())
        <a href="{{ route('visits.create') }}" class="btn btn-sm d-flex align-items-center gap-1" style="background:var(--sl-primary);color:#fff;border-radius:8px;font-weight:600;padding:.45rem 1rem">
            <i class="bi bi-plus-lg"></i> New Visit
        </a>
        @endif
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <a href="#" class="stat-card" data-filter-view="all">
                <div class="stat-icon" style="background:#f0f9ff"><i class="bi bi-folder2-open" style="color:#0284c7"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-label">Total Visits</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="#" class="stat-card" data-filter-view="action_required">
                <div class="stat-icon" style="background:#fff7ed"><i class="bi bi-bell" style="color:#ea580c"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['action_required'] }}</div>
                    <div class="stat-label">On My Desk</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="#" class="stat-card" data-filter-view="in_progress">
                <div class="stat-icon" style="background:#f0fdf4"><i class="bi bi-arrow-repeat" style="color:#16a34a"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['in_progress'] }}</div>
                    <div class="stat-label">In Progress</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="#" class="stat-card" data-filter-view="all">
                <div class="stat-icon" style="background:#fef9c3"><i class="bi bi-hourglass-split" style="color:#ca8a04"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['pending_observations'] }}</div>
                    <div class="stat-label">Pending Observations</div>
                </div>
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="filter-bar mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-auto d-flex gap-1 flex-wrap" style="flex:1">
                <button class="view-tab active" data-view="all">All</button>
                <button class="view-tab" data-view="action_required">On My Desk</button>
                <button class="view-tab" data-view="created_by_me">Created by Me</button>
            </div>
            <div class="col-sm-auto">
                <select id="filter-po" class="form-select form-select-sm" style="min-width:160px">
                    <option value="">All POs</option>
                    @foreach($poList as $po)
                    <option value="{{ $po->po_code }}">{{ $po->po_short_name ?? $po->po_code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-auto">
                <select id="filter-type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <option value="ONSITE">Onsite</option>
                    <option value="OFFSITE">Offsite</option>
                </select>
            </div>
            <div class="col-sm-auto">
                <select id="filter-status" class="form-select form-select-sm" style="min-width:140px">
                    <option value="">All Statuses</option>
                    @foreach($formOptions['statuses'] as $s)
                    <option value="{{ $s }}">{{ str_replace('_', ' ', $s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-auto">
                <button id="btn-reset-filters" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i>Reset
                </button>
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="card border-0 shadow-sm" style="border-radius:var(--sl-radius)">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="visits-table" class="table visits-table mb-0" style="width:100%">
                    <thead>
                        <tr>
                            <th>Visit Code</th>
                            <th>Partner Organization</th>
                            <th>Visit Period</th>
                            <th>Type</th>
                            <th>Observations</th>
                            <th>Status</th>
                            <th>Current Desk</th>
                            <th style="width:80px">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    let activeView = 'all';

    const statusLabels = {
        SAVED:        'Saved',
        SUBMITTED:    'Submitted',
        REJECTED:     'Rejected',
        PO_SO_REVIEW: 'PO Supervisor',
        PO_REVIEW:    'PO Officer',
        PO_SUBMITTED: 'PO Submitted',
        PO_APPROVED:  'PO Approved',
    };

    const dt = $('#visits-table').DataTable({
        serverSide: true,
        processing: true,
        responsive: false,
        pageLength: 25,
        language: {
            processing: '<div class="d-flex align-items-center gap-2 py-3"><div class="spinner-border spinner-border-sm text-secondary"></div><span class="text-muted">Loading…</span></div>',
            emptyTable: '<div class="py-4 text-center text-muted"><i class="bi bi-inbox fs-3 d-block mb-1"></i>No visits found</div>',
        },
        ajax: {
            url: '{{ route("visits.data") }}',
            data: function (d) {
                d.view       = activeView;
                d.po_code    = $('#filter-po').val();
                d.visit_type = $('#filter-type').val();
                d.status     = $('#filter-status').val();
            },
        },
        columns: [
            {
                data: null,
                orderable: true,
                render: function (_, __, row) {
                    const myDesk = row.is_my_desk
                        ? '<span class="badge rounded-pill bg-warning text-dark ms-1" style="font-size:.65rem">You</span>' : '';
                    return `<a href="/visits/${row.id}" class="visit-code-link text-decoration-none">${row.visit_code}</a>${myDesk}`;
                }
            },
            {
                data: 'po_name',
                render: (val, _, row) => `<span class="fw-medium">${val}</span><br><small class="text-muted">${row.po_code}</small>`
            },
            {
                data: null,
                orderable: true,
                render: (_, __, row) => {
                    const from = row.visit_from_date ? new Date(row.visit_from_date).toLocaleDateString('en-GB', {day:'2-digit',month:'short',year:'numeric'}) : '—';
                    const to   = row.visit_to_date   ? new Date(row.visit_to_date).toLocaleDateString('en-GB', {day:'2-digit',month:'short',year:'numeric'}) : '—';
                    return `<span style="font-size:.82rem">${from}</span><span class="text-muted mx-1">→</span><span style="font-size:.82rem">${to}</span>`;
                }
            },
            {
                data: 'visit_type',
                render: val => `<span class="badge rounded-pill" style="background:#f1f5f9;color:#475569;font-size:.7rem">${val}</span>`
            },
            {
                data: null,
                orderable: false,
                render: (_, __, row) => {
                    const open = row.observations_total - row.observations_resolved - row.observations_pending;
                    let html = `<span class="obs-pill obs-total me-1"><i class="bi bi-list-ul"></i>${row.observations_total}</span>`;
                    if (row.observations_resolved > 0) html += `<span class="obs-pill obs-resolved me-1"><i class="bi bi-check-circle"></i>${row.observations_resolved}</span>`;
                    if (row.observations_pending  > 0) html += `<span class="obs-pill obs-pending"><i class="bi bi-hourglass-split"></i>${row.observations_pending}</span>`;
                    if (row.observations_total === 0) html = `<span class="text-muted" style="font-size:.78rem">None yet</span>`;
                    return html;
                }
            },
            {
                data: 'status',
                render: val => `<span class="badge-status badge-${val}">${statusLabels[val] ?? val}</span>`
            },
            {
                data: 'current_desk',
                render: (val, _, row) => {
                    if (!val) return '<span class="text-muted">—</span>';
                    const badge = row.is_my_desk ? `<span class="badge ms-1" style="background:#dcfce7;color:#15803d;font-size:.65rem">You</span>` : '';
                    const from  = row.incoming_from ? `<br><small class="text-muted" style="font-size:.72rem">from ${row.incoming_from}</small>` : '';
                    return `<span style="font-size:.82rem">${val}</span>${badge}${from}`;
                }
            },
            {
                data: null,
                orderable: false,
                render: (_, __, row) => `<a href="/visits/${row.id}" class="btn btn-sm" style="background:var(--sl-primary-soft);color:var(--sl-primary);border-radius:7px;font-size:.75rem;font-weight:600">Open</a>`
            },
        ],
        drawCallback: function () {
            // Highlight rows on my desk
            $('#visits-table tbody tr').each(function () {
                const data = dt.row(this).data();
                if (data && data.is_my_desk) $(this).addClass('my-desk-row');
            });
        },
        order: [[0, 'desc']],
    });

    // View tabs
    $('.view-tab').on('click', function () {
        $('.view-tab').removeClass('active');
        $(this).addClass('active');
        activeView = $(this).data('view');
        dt.draw();
    });

    // Stat card filter shortcuts
    $('[data-filter-view]').on('click', function (e) {
        e.preventDefault();
        const view = $(this).data('filter-view');
        $('.view-tab').removeClass('active');
        $(`.view-tab[data-view="${view}"]`).addClass('active');
        activeView = view;
        dt.draw();
    });

    // Filter dropdowns
    $('#filter-po, #filter-type, #filter-status').on('change', function () { dt.draw(); });

    // Reset
    $('#btn-reset-filters').on('click', function () {
        $('#filter-po, #filter-type, #filter-status').val('');
        $('.view-tab').removeClass('active');
        $('.view-tab[data-view="all"]').addClass('active');
        activeView = 'all';
        dt.draw();
    });
})();
</script>
@endpush
