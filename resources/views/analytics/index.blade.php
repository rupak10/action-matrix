@extends('layouts.app')

@section('title', 'Analytics')

@push('styles')
<style>
/* ── KPI cards ─────────────────────────────────────────────────────────── */
.kpi-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.2rem 1.4rem;
    height: 100%;
    transition: box-shadow .15s, border-color .15s;
}
.kpi-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.07); border-color: #cbd5e1; }
.kpi-icon {
    width: 36px; height: 36px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.kpi-label { font-size: .7rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .06em; }
.kpi-value { font-size: 2rem; font-weight: 800; color: #0f172a; line-height: 1; margin: .6rem 0 .2rem; }
.kpi-sub   { font-size: .71rem; color: #94a3b8; }

/* ── Chart panels ──────────────────────────────────────────────────────── */
.chart-panel {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
    height: 100%;
}
.chart-panel-title {
    font-size: .78rem; font-weight: 700; color: #374151;
    display: flex; align-items: center; gap: .4rem;
    margin-bottom: 1rem;
}
.chart-panel-title i { color: #94a3b8; font-size: .85rem; }
.chart-panel-subtitle { font-size: .7rem; font-weight: 400; color: #94a3b8; margin-left: .25rem; }
.chart-empty {
    display: none; position: absolute; inset: 0;
    align-items: center; justify-content: center; flex-direction: column;
    color: #cbd5e1;
}
.chart-empty i { font-size: 2rem; margin-bottom: .4rem; }
.chart-empty span { font-size: .78rem; }

/* ── Filter bar ────────────────────────────────────────────────────────── */
.ana-filter-bar {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: .875rem 1.25rem;
    margin-bottom: 1.5rem;
    display: flex; flex-wrap: wrap; gap: .75rem; align-items: center;
}
.period-btn {
    padding: .3rem .75rem; border-radius: 6px;
    border: 1px solid #e2e8f0; background: #fff;
    font-size: .78rem; font-weight: 600; color: #64748b;
    cursor: pointer; transition: all .12s; white-space: nowrap;
}
.period-btn:hover:not(.active) { background: #f8fafc; border-color: #cbd5e1; color: #374151; }
.period-btn.active { background: var(--sl-primary); color: #fff; border-color: var(--sl-primary); }

/* ── Table panels ──────────────────────────────────────────────────────── */
.table-panel {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    height: 100%;
}
.table-panel-header {
    padding: .9rem 1.25rem;
    border-bottom: 1px solid #f1f5f9;
    font-size: .78rem; font-weight: 700; color: #374151;
    display: flex; align-items: center; gap: .4rem;
}
.table-panel-header i { color: #94a3b8; }
.ana-table thead th {
    background: #f8fafc;
    color: #64748b;
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    border-bottom: 1px solid #e2e8f0 !important;
    border-top: none !important;
    padding: .55rem .85rem;
    white-space: nowrap;
}
.ana-table tbody td {
    font-size: .83rem; vertical-align: middle;
    border-color: #f8fafc; padding: .55rem .85rem; color: #374151;
}
.ana-table tbody tr:hover td { background: #fafbfc; }

/* ── Status badges ─────────────────────────────────────────────────────── */
.vbadge { font-size: .68rem; padding: .25em .6em; border-radius: 5px; font-weight: 600; white-space: nowrap; }
.vb-SAVED        { background: #f1f5f9; color: #475569; }
.vb-SUBMITTED    { background: #eff6ff; color: #1d4ed8; }
.vb-REJECTED     { background: #fff1f2; color: #be123c; }
.vb-PO_SO_REVIEW { background: #fffbeb; color: #b45309; }
.vb-PO_REVIEW    { background: #f5f3ff; color: #6d28d9; }
.vb-PO_SUBMITTED { background: #ecfeff; color: #0e7490; }
.vb-PO_APPROVED  { background: #f0fdf4; color: #15803d; }

/* ── Days bar ──────────────────────────────────────────────────────────── */
.days-bar { display: flex; align-items: center; gap: .5rem; min-width: 100px; }
.days-bar-track { flex: 1; height: 5px; border-radius: 3px; background: #e2e8f0; overflow: hidden; }
.days-bar-fill  { height: 100%; border-radius: 3px; background: linear-gradient(90deg, var(--sl-primary), #d97706); }
.days-num { font-size: .73rem; font-weight: 700; color: #64748b; white-space: nowrap; }

/* ── Section label ─────────────────────────────────────────────────────── */
.section-label {
    font-size: .68rem; font-weight: 700; color: #94a3b8;
    text-transform: uppercase; letter-spacing: .07em;
    margin-bottom: .75rem;
}

/* ── Loading overlay ───────────────────────────────────────────────────── */
#loadingOverlay {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(255,255,255,.55) backdrop-filter: blur(2px);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; pointer-events: none; transition: opacity .2s;
}
#loadingOverlay.active { opacity: 1; pointer-events: all; }
#loadingOverlay .spinner-wrap {
    background: #fff; border-radius: 12px; padding: 1.5rem 2rem;
    text-align: center; box-shadow: 0 8px 32px rgba(0,0,0,.12);
    border: 1px solid #e2e8f0;
}
#loadingOverlay p { margin: .6rem 0 0; font-size: .82rem; color: #64748b; }

@media (max-width: 576px) {
    .kpi-value { font-size: 1.6rem; }
    .chart-panel { padding: 1rem 1rem; }
}
</style>
@endpush

@section('content')

<div id="loadingOverlay">
    <div class="spinner-wrap">
        <div class="spinner-border spinner-border-sm" role="status" style="color:var(--sl-primary);width:1.6rem;height:1.6rem"></div>
        <p>Updating…</p>
    </div>
</div>

<div class="container-fluid">

    {{-- ── Page title ──────────────────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <h5 style="font-weight:700;color:#0f172a;margin:0;font-size:1.05rem">Analytics</h5>
            <div style="font-size:.74rem;color:#94a3b8;margin-top:.15rem">
                @if($currentUser->hasAnyRole(['SM_MD', 'Super_Admin', 'Admin']))
                    All POs
                @elseif($currentUser->isPo())
                    {{ $currentUser->po_code }}
                @else
                    {{ count($poList) }} assigned PO{{ count($poList) !== 1 ? 's' : '' }}
                @endif
                &nbsp;&bull;&nbsp; as of {{ now()->format('d M Y, h:i A') }}
            </div>
        </div>
    </div>

    {{-- ── Filter bar ──────────────────────────────────────────────────── --}}
    <div class="ana-filter-bar">
        <span style="font-size:.75rem;font-weight:600;color:#64748b;white-space:nowrap">Period:</span>
        <div class="d-flex gap-1 flex-wrap">
            @foreach(['1month'=>'1M','3months'=>'3M','6months'=>'6M','1year'=>'1Y','all'=>'All'] as $val=>$lbl)
            <button class="period-btn {{ $selectedPeriod === $val ? 'active' : '' }}" data-period="{{ $val }}">{{ $lbl }}</button>
            @endforeach
        </div>

        @if($showPoPanel && count($poList))
        <div class="d-flex align-items-center gap-2 ms-2" style="border-left:1px solid #e2e8f0;padding-left:.85rem">
            <span style="font-size:.75rem;font-weight:600;color:#64748b;white-space:nowrap">PO:</span>
            <select id="filterPo" class="form-select form-select-sm" style="min-width:160px;font-size:.8rem;border-radius:7px;border-color:#e2e8f0">
                <option value="">All POs</option>
                @foreach($poList as $po)
                <option value="{{ $po }}" {{ $selectedPo === $po ? 'selected' : '' }}>{{ $po }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <button id="btnClear" class="btn btn-sm ms-auto" style="border-radius:7px;font-size:.78rem;font-weight:600;color:#64748b;border:1px solid #e2e8f0;background:#fff;padding:.3rem .75rem">
            <i class="bi bi-x-circle me-1"></i>Clear
        </button>
    </div>

    {{-- ── KPI Cards — Row 1 ───────────────────────────────────────────── --}}
    <div class="section-label">Overview</div>
    <div class="row g-3 mb-2">
        <div class="col-6 col-md-4 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex align-items-center gap-2">
                    <div class="kpi-icon" style="background:#f1f5f9">
                        <i class="bi bi-grid-3x3-gap" style="color:#64748b"></i>
                    </div>
                    <span class="kpi-label">Total Visits</span>
                </div>
                <div class="kpi-value" id="statTotal">{{ $stats['total'] }}</div>
                <div class="kpi-sub">All visits in scope</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex align-items-center gap-2">
                    <div class="kpi-icon" style="background:#fffbeb">
                        <i class="bi bi-hourglass-split" style="color:#d97706"></i>
                    </div>
                    <span class="kpi-label">Open</span>
                </div>
                <div class="kpi-value" id="statOpen">{{ $stats['open'] }}</div>
                <div class="kpi-sub">Active / in-progress</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex align-items-center gap-2">
                    <div class="kpi-icon" style="background:#f0fdf4">
                        <i class="bi bi-check-circle" style="color:#16a34a"></i>
                    </div>
                    <span class="kpi-label">Completed</span>
                </div>
                <div class="kpi-value" id="statCompleted">{{ $stats['completed'] }}</div>
                <div class="kpi-sub">PO Approved</div>
            </div>
        </div>
        @if(!$currentUser->isPo())
        <div class="col-6 col-md-4 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex align-items-center gap-2">
                    <div class="kpi-icon" style="background:#eef2ff">
                        <i class="bi bi-inbox" style="color:#4f46e5"></i>
                    </div>
                    <span class="kpi-label">On My Desk</span>
                </div>
                <div class="kpi-value" id="statMyDesk">{{ $stats['on_my_desk'] ?? 0 }}</div>
                <div class="kpi-sub">Awaiting your action</div>
            </div>
        </div>
        @else
        <div class="col-6 col-md-4 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex align-items-center gap-2">
                    <div class="kpi-icon" style="background:#fffbeb">
                        <i class="bi bi-hourglass-split" style="color:#d97706"></i>
                    </div>
                    <span class="kpi-label">Pending Review</span>
                </div>
                <div class="kpi-value" id="statObsPending">{{ $stats['obs_pending'] }}</div>
                <div class="kpi-sub">Awaiting PKSF confirmation</div>
            </div>
        </div>
        @endif
    </div>

    {{-- ── KPI Cards — Row 2 ───────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex align-items-center gap-2">
                    <div class="kpi-icon" style="background:#eff6ff">
                        <i class="bi bi-eye" style="color:#2563eb"></i>
                    </div>
                    <span class="kpi-label">Total Observations</span>
                </div>
                <div class="kpi-value" id="statObsTotal">{{ $stats['obs_total'] }}</div>
                <div class="kpi-sub">Across all visits</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex align-items-center gap-2">
                    <div class="kpi-icon" style="background:#ecfeff">
                        <i class="bi bi-check2-all" style="color:#0891b2"></i>
                    </div>
                    <span class="kpi-label">Resolved Obs</span>
                </div>
                <div class="kpi-value" id="statObsResolved">{{ $stats['obs_resolved'] }}</div>
                <div class="kpi-sub">Resolution confirmed</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex align-items-center gap-2">
                    <div class="kpi-icon" style="background:#f5f3ff">
                        <i class="bi bi-lightning" style="color:#7c3aed"></i>
                    </div>
                    <span class="kpi-label">Action Matrix</span>
                </div>
                <div class="kpi-value" id="statActionMatrix">{{ $stats['action_matrix'] }}</div>
                <div class="kpi-sub">Obs requiring action plan</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex align-items-center gap-2">
                    <div class="kpi-icon" style="background:#fff7ed">
                        <i class="bi bi-calendar2-check" style="color:#ea580c"></i>
                    </div>
                    <span class="kpi-label">Avg Days to Complete</span>
                </div>
                <div class="kpi-value" id="statAvgDays">{{ $stats['avg_days'] }}</div>
                <div class="kpi-sub">For completed visits</div>
            </div>
        </div>
    </div>

    {{-- ── Charts Row 1: Status + Obs Resolution + Priority ───────────── --}}
    <div class="section-label">Distributions</div>
    <div class="row g-3 mb-3">

        {{-- Visit Status --}}
        <div class="col-12 col-lg-5">
            <div class="chart-panel">
                <div class="chart-panel-title">
                    <i class="bi bi-pie-chart-fill"></i> Visit Status
                </div>
                <div style="position:relative;height:240px">
                    <canvas id="chartStatus"></canvas>
                    <div class="chart-empty" id="emptyStatus">
                        <i class="bi bi-pie-chart"></i><span>No data</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Observation Resolution --}}
        <div class="col-12 col-lg-4">
            <div class="chart-panel">
                <div class="chart-panel-title">
                    <i class="bi bi-circle-half"></i> Observation Resolution
                </div>
                <div style="position:relative;height:240px">
                    <canvas id="chartObsRes"></canvas>
                    <div class="chart-empty" id="emptyObsRes">
                        <i class="bi bi-circle-half"></i><span>No data</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Priority --}}
        <div class="col-12 col-lg-3">
            <div class="chart-panel">
                <div class="chart-panel-title">
                    <i class="bi bi-bar-chart-fill"></i> By Priority
                </div>
                <div style="position:relative;height:240px">
                    <canvas id="chartPriority"></canvas>
                    <div class="chart-empty" id="emptyPriority">
                        <i class="bi bi-bar-chart"></i><span>No data</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Charts Row 2: Category + Monthly Trend ──────────────────────── --}}
    <div class="row g-3 mb-3">

        {{-- Category --}}
        <div class="col-12 col-lg-5">
            <div class="chart-panel">
                <div class="chart-panel-title">
                    <i class="bi bi-tags-fill"></i> By Category
                </div>
                <div style="position:relative;height:240px">
                    <canvas id="chartCategory"></canvas>
                    <div class="chart-empty" id="emptyCategory">
                        <i class="bi bi-tags"></i><span>No data</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Monthly Trend --}}
        <div class="col-12 col-lg-7">
            <div class="chart-panel">
                <div class="chart-panel-title">
                    <i class="bi bi-graph-up-arrow"></i> Monthly Trend
                    <span class="chart-panel-subtitle">(Last 12 months)</span>
                </div>
                <div style="position:relative;height:240px">
                    <canvas id="chartMonthly"></canvas>
                    <div class="chart-empty" id="emptyMonthly">
                        <i class="bi bi-graph-up"></i><span>No data</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── PO Comparison (PKSF / SM only) ──────────────────────────────── --}}
    @if($showPoPanel)
    <div class="section-label mt-1">PO Comparison</div>
    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="chart-panel">
                <div class="chart-panel-title">
                    <i class="bi bi-buildings-fill"></i> Open vs Resolved Observations by PO
                    <span class="chart-panel-subtitle">(Top 12 by total observations)</span>
                </div>
                <div id="poComparisonContainer" style="position:relative;height:280px">
                    <canvas id="chartPoComparison"></canvas>
                    <div class="chart-empty" id="emptyPoComparison">
                        <i class="bi bi-buildings"></i><span>No data</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Tables ───────────────────────────────────────────────────────── --}}
    <div class="section-label mt-1">Visit Lists</div>
    <div class="row g-3 mb-4">

        {{-- Recent visits --}}
        <div class="col-12 col-xl-6">
            <div class="table-panel">
                <div class="table-panel-header">
                    <i class="bi bi-clock-history"></i> Recently Created
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 ana-table" id="tblRecent">
                        <thead>
                            <tr>
                                <th>Visit</th>
                                <th>PO</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyRecent">
                            @foreach($recentVisits as $v)
                            <tr>
                                <td>
                                    <a href="{{ route('visits.show', $v['id']) }}"
                                       class="text-decoration-none fw-semibold" style="color:var(--sl-primary)">
                                        {{ $v['visit_code'] }}
                                    </a>
                                </td>
                                <td style="color:#64748b">{{ $v['po_code'] }}</td>
                                <td><span class="vbadge vb-{{ $v['status_raw'] }}">{{ $v['status'] }}</span></td>
                                <td style="color:#94a3b8;font-size:.78rem;white-space:nowrap">{{ $v['created_at'] }}</td>
                            </tr>
                            @endforeach
                            @if(empty($recentVisits))
                            <tr><td colspan="4" class="text-center text-muted py-4" style="font-size:.82rem">No visits found</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Longest pending --}}
        <div class="col-12 col-xl-6">
            <div class="table-panel">
                <div class="table-panel-header">
                    <i class="bi bi-exclamation-triangle"></i> Longest Pending
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 ana-table" id="tblPending">
                        <thead>
                            <tr>
                                <th>Visit</th>
                                <th>PO</th>
                                <th>Status</th>
                                <th>Age</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyPending">
                            @php $maxDays = collect($longestPending)->max('days_pending') ?: 1; @endphp
                            @foreach($longestPending as $v)
                            <tr>
                                <td>
                                    <a href="{{ route('visits.show', $v['id']) }}"
                                       class="text-decoration-none fw-semibold" style="color:var(--sl-primary)">
                                        {{ $v['visit_code'] }}
                                    </a>
                                </td>
                                <td style="color:#64748b">{{ $v['po_code'] }}</td>
                                <td><span class="vbadge vb-{{ $v['status_raw'] }}">{{ $v['status'] }}</span></td>
                                <td>
                                    <div class="days-bar">
                                        <div class="days-bar-track">
                                            <div class="days-bar-fill" style="width:{{ min(100, round($v['days_pending']/$maxDays*100)) }}%"></div>
                                        </div>
                                        <span class="days-num">{{ $v['days_pending'] }}d</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            @if(empty($longestPending))
                            <tr><td colspan="4" class="text-center text-muted py-4" style="font-size:.82rem">No pending visits</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection

@php
$analyticsJson = [
    'stats'              => $stats,
    'statusChart'        => $statusChart,
    'obsResolutionChart' => $obsResolutionChart,
    'priorityChart'      => $priorityChart,
    'categoryChart'      => $categoryChart,
    'monthlyChart'       => $monthlyChart,
    'poComparisonChart'  => $poComparisonChart,
    'recentVisits'       => $recentVisits,
    'longestPending'     => $longestPending,
    'showPoPanel'        => $showPoPanel,
];
@endphp

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
const ANA      = @json($analyticsJson);
const AJAX_URL = '{{ route("analytics.data") }}';
const VISIT_URL= '{{ url("visits") }}';

// ── Colour maps ──────────────────────────────────────────────────────────
const STATUS_COLORS = {
    SAVED:        '#94a3b8',
    SUBMITTED:    '#2563eb',
    REJECTED:     '#e11d48',
    PO_SO_REVIEW: '#d97706',
    PO_REVIEW:    '#7c3aed',
    PO_SUBMITTED: '#0891b2',
    PO_APPROVED:  '#16a34a',
};
const RESOLUTION_COLORS = {
    OPEN:             '#2563eb',
    PENDING_RESOLVED: '#d97706',
    RESOLVED:         '#16a34a',
};
const PRIORITY_COLORS = { HIGH: '#ef4444', MEDIUM: '#f59e0b', LOW: '#22c55e' };
const CATEGORY_COLORS  = ['#2563eb', '#16a34a', '#7c3aed', '#0891b2', '#d97706'];

// ── Chart.js defaults ────────────────────────────────────────────────────
Chart.defaults.font.family = "'Inter','Segoe UI',sans-serif";
Chart.defaults.font.size   = 11;

// ── Chart instances ──────────────────────────────────────────────────────
let chartStatus, chartObsRes, chartPriority, chartCategory, chartMonthly, chartPoComparison;

// ── Helpers ──────────────────────────────────────────────────────────────
function toggleEmpty(canvasId, emptyId, isEmpty) {
    document.getElementById(canvasId).style.display = isEmpty ? 'none' : '';
    const el = document.getElementById(emptyId);
    if (el) el.style.display = isEmpty ? 'flex' : 'none';
}
function kill(inst) { if (inst) { inst.destroy(); } return null; }
function esc(s) {
    if (s == null) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
                    .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

// ── Build: Status donut ──────────────────────────────────────────────────
function buildStatus(d) {
    chartStatus = kill(chartStatus);
    const empty = !d.data.length || d.data.every(v => v === 0);
    toggleEmpty('chartStatus', 'emptyStatus', empty);
    if (empty) return;

    chartStatus = new Chart(document.getElementById('chartStatus'), {
        type: 'doughnut',
        data: {
            labels: d.labels,
            datasets: [{
                data: d.data,
                backgroundColor: d.raw.map(r => STATUS_COLORS[r] || '#94a3b8'),
                borderWidth: 2, borderColor: '#fff', hoverOffset: 6,
            }],
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '68%',
            plugins: {
                legend: { position: 'right', labels: { boxWidth: 10, padding: 10, font: { size: 10 } } },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const total = ctx.dataset.data.reduce((a,b) => a+b, 0);
                            return ` ${ctx.label}: ${ctx.parsed} (${Math.round(ctx.parsed/total*100)}%)`;
                        },
                    },
                },
            },
        },
    });
}

// ── Build: Obs Resolution donut ──────────────────────────────────────────
function buildObsRes(d) {
    chartObsRes = kill(chartObsRes);
    const empty = !d.data.length || d.data.every(v => v === 0);
    toggleEmpty('chartObsRes', 'emptyObsRes', empty);
    if (empty) return;

    chartObsRes = new Chart(document.getElementById('chartObsRes'), {
        type: 'doughnut',
        data: {
            labels: d.labels,
            datasets: [{
                data: d.data,
                backgroundColor: d.raw.map(r => RESOLUTION_COLORS[r] || '#94a3b8'),
                borderWidth: 2, borderColor: '#fff', hoverOffset: 6,
            }],
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '68%',
            plugins: {
                legend: { position: 'right', labels: { boxWidth: 10, padding: 10, font: { size: 10 } } },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const total = ctx.dataset.data.reduce((a,b) => a+b, 0);
                            return ` ${ctx.label}: ${ctx.parsed} (${Math.round(ctx.parsed/total*100)}%)`;
                        },
                    },
                },
            },
        },
    });
}

// ── Build: Priority bar ──────────────────────────────────────────────────
function buildPriority(d) {
    chartPriority = kill(chartPriority);
    const empty = !d.data.length || d.data.every(v => v === 0);
    toggleEmpty('chartPriority', 'emptyPriority', empty);
    if (empty) return;

    chartPriority = new Chart(document.getElementById('chartPriority'), {
        type: 'bar',
        data: {
            labels: d.labels,
            datasets: [{
                data: d.data,
                backgroundColor: d.labels.map(l => PRIORITY_COLORS[l] || '#94a3b8'),
                borderRadius: 6, borderSkipped: false,
            }],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false } },
            },
        },
    });
}

// ── Build: Category horizontal bar ───────────────────────────────────────
function buildCategory(d) {
    chartCategory = kill(chartCategory);
    const empty = !d.data.length || d.data.every(v => v === 0);
    toggleEmpty('chartCategory', 'emptyCategory', empty);
    if (empty) return;

    chartCategory = new Chart(document.getElementById('chartCategory'), {
        type: 'bar',
        data: {
            labels: d.labels,
            datasets: [{
                data: d.data,
                backgroundColor: CATEGORY_COLORS.slice(0, d.labels.length),
                borderRadius: 6, borderSkipped: false,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                y: { grid: { display: false } },
            },
        },
    });
}

// ── Build: Monthly dual-line trend ───────────────────────────────────────
function buildMonthly(d) {
    chartMonthly = kill(chartMonthly);
    const allZero = d.visits.every(v => v === 0) && d.observations.every(v => v === 0);
    toggleEmpty('chartMonthly', 'emptyMonthly', allZero);
    if (allZero) return;

    const primary = getComputedStyle(document.documentElement).getPropertyValue('--sl-primary').trim() || '#1d7a6e';

    chartMonthly = new Chart(document.getElementById('chartMonthly'), {
        type: 'line',
        data: {
            labels: d.labels,
            datasets: [
                {
                    label: 'Visits',
                    data: d.visits,
                    borderColor: primary,
                    backgroundColor: primary + '18',
                    borderWidth: 2, pointRadius: 3, pointHoverRadius: 5,
                    tension: 0.4, fill: true,
                },
                {
                    label: 'Observations',
                    data: d.observations,
                    borderColor: '#d97706',
                    backgroundColor: '#d9770610',
                    borderWidth: 2, pointRadius: 3, pointHoverRadius: 5,
                    tension: 0.4, fill: false, borderDash: [4, 3],
                },
            ],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 10, padding: 12, font: { size: 10 } } },
                tooltip: { mode: 'index', intersect: false },
            },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false } },
            },
        },
    });
}

// ── Build: PO Comparison stacked horizontal bar ──────────────────────────
function buildPoComparison(d) {
    if (!d) return;
    chartPoComparison = kill(chartPoComparison);
    const empty = !d.labels.length;
    toggleEmpty('chartPoComparison', 'emptyPoComparison', empty);
    if (empty) return;

    // Adjust container height based on number of POs
    const h = Math.max(220, d.labels.length * 30 + 60);
    document.getElementById('poComparisonContainer').style.height = h + 'px';

    chartPoComparison = new Chart(document.getElementById('chartPoComparison'), {
        type: 'bar',
        data: {
            labels: d.labels,
            datasets: [
                {
                    label: 'Open',
                    data: d.open,
                    backgroundColor: '#fbbf24',
                    borderRadius: 4, borderSkipped: false, stack: 'obs',
                },
                {
                    label: 'Resolved',
                    data: d.resolved,
                    backgroundColor: '#16a34a',
                    borderRadius: 4, borderSkipped: false, stack: 'obs',
                },
            ],
        },
        options: {
            indexAxis: 'y',
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 10, padding: 12, font: { size: 10 } } },
                tooltip: { mode: 'index', intersect: false },
            },
            scales: {
                x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' }, stacked: true },
                y: { grid: { display: false }, stacked: true, ticks: { font: { size: 10 } } },
            },
        },
    });
}

// ── Render stat cards ────────────────────────────────────────────────────
function renderStats(s) {
    document.getElementById('statTotal').textContent        = s.total;
    document.getElementById('statOpen').textContent         = s.open;
    document.getElementById('statCompleted').textContent    = s.completed;
    document.getElementById('statObsTotal').textContent     = s.obs_total;
    document.getElementById('statObsResolved').textContent  = s.obs_resolved;
    document.getElementById('statObsPending').textContent   = s.obs_pending ?? 0;
    document.getElementById('statActionMatrix').textContent = s.action_matrix;
    document.getElementById('statAvgDays').textContent      = s.avg_days;
    const myDesk = document.getElementById('statMyDesk');
    if (myDesk) myDesk.textContent = s.on_my_desk ?? 0;
}

// ── Render recent visits table ────────────────────────────────────────────
function renderRecent(rows) {
    const tbody = document.getElementById('tbodyRecent');
    if (!rows?.length) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4" style="font-size:.82rem">No visits found</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(v => `
        <tr>
            <td><a href="${VISIT_URL}/${v.id}" class="text-decoration-none fw-semibold" style="color:var(--sl-primary)">${esc(v.visit_code)}</a></td>
            <td style="color:#64748b">${esc(v.po_code)}</td>
            <td><span class="vbadge vb-${esc(v.status_raw)}">${esc(v.status)}</span></td>
            <td style="color:#94a3b8;font-size:.78rem;white-space:nowrap">${esc(v.created_at)}</td>
        </tr>
    `).join('');
}

// ── Render longest pending table ──────────────────────────────────────────
function renderPending(rows) {
    const tbody = document.getElementById('tbodyPending');
    if (!rows?.length) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4" style="font-size:.82rem">No pending visits</td></tr>';
        return;
    }
    const maxDays = Math.max(...rows.map(r => r.days_pending), 1);
    tbody.innerHTML = rows.map(v => {
        const pct = Math.min(100, Math.round(v.days_pending / maxDays * 100));
        return `
        <tr>
            <td><a href="${VISIT_URL}/${v.id}" class="text-decoration-none fw-semibold" style="color:var(--sl-primary)">${esc(v.visit_code)}</a></td>
            <td style="color:#64748b">${esc(v.po_code)}</td>
            <td><span class="vbadge vb-${esc(v.status_raw)}">${esc(v.status)}</span></td>
            <td>
                <div class="days-bar">
                    <div class="days-bar-track"><div class="days-bar-fill" style="width:${pct}%"></div></div>
                    <span class="days-num">${v.days_pending}d</span>
                </div>
            </td>
        </tr>`;
    }).join('');
}

// ── Full rebuild ──────────────────────────────────────────────────────────
function buildAll(d) {
    buildStatus(d.statusChart);
    buildObsRes(d.obsResolutionChart);
    buildPriority(d.priorityChart);
    buildCategory(d.categoryChart);
    buildMonthly(d.monthlyChart);
    if (d.showPoPanel && d.poComparisonChart) buildPoComparison(d.poComparisonChart);
    renderStats(d.stats);
    renderRecent(d.recentVisits);
    renderPending(d.longestPending);
}

// ── AJAX refresh ──────────────────────────────────────────────────────────
function refresh() {
    const period = document.querySelector('.period-btn.active')?.dataset.period || '6months';
    const poEl   = document.getElementById('filterPo');
    const poCode = poEl ? poEl.value : '';

    document.getElementById('loadingOverlay').classList.add('active');

    fetch(`${AJAX_URL}?period=${encodeURIComponent(period)}&po_code=${encodeURIComponent(poCode)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
    .then(r => r.json())
    .then(d => buildAll(d))
    .catch(console.error)
    .finally(() => document.getElementById('loadingOverlay').classList.remove('active'));
}

// ── Period pill events ────────────────────────────────────────────────────
document.querySelectorAll('.period-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        refresh();
    });
});

// ── PO filter ─────────────────────────────────────────────────────────────
const poEl = document.getElementById('filterPo');
if (poEl) poEl.addEventListener('change', refresh);

// ── Clear ─────────────────────────────────────────────────────────────────
document.getElementById('btnClear').addEventListener('click', () => {
    document.querySelectorAll('.period-btn').forEach(b => b.classList.toggle('active', b.dataset.period === '6months'));
    if (poEl) poEl.value = '';
    refresh();
});

// ── Initial render ────────────────────────────────────────────────────────
buildAll(ANA);
</script>
@endpush
