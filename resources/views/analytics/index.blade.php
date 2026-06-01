@extends('layouts.app')

@section('title', 'Analytics Dashboard')

@push('styles')
<style>
/* ── Page header ───────────────────────────────────────────────────── */
.analytics-header {
    background: linear-gradient(135deg, #1a237e 0%, #283593 40%, #1565c0 100%);
    border-radius: 16px;
    padding: 2rem 2.5rem;
    margin-bottom: 1.75rem;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.analytics-header::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 220px; height: 220px;
    border-radius: 50%;
    background: rgba(255,255,255,0.06);
}
.analytics-header::after {
    content: '';
    position: absolute;
    bottom: -80px; left: -40px;
    width: 280px; height: 280px;
    border-radius: 50%;
    background: rgba(255,255,255,0.04);
}
.analytics-header h4 { font-size: 1.6rem; font-weight: 700; margin: 0 0 .25rem; }
.analytics-header p  { opacity: .75; margin: 0; font-size: .9rem; }

/* ── Filter bar ────────────────────────────────────────────────────── */
.filter-bar {
    background: #fff;
    border-radius: 12px;
    padding: 1rem 1.5rem;
    margin-bottom: 1.75rem;
    box-shadow: 0 1px 6px rgba(0,0,0,.06);
    display: flex;
    flex-wrap: wrap;
    gap: .75rem;
    align-items: center;
}
.filter-bar label { font-size: .8rem; font-weight: 600; color: #546e7a; margin-bottom: .2rem; }
.filter-bar select { font-size: .875rem; border-radius: 8px; border-color: #dee2e6; padding: .45rem .75rem; }

/* ── Stat cards ────────────────────────────────────────────────────── */
.stat-card {
    border-radius: 14px;
    padding: 1.5rem 1.75rem;
    color: #fff;
    position: relative;
    overflow: hidden;
    transition: transform .2s, box-shadow .2s;
    cursor: default;
}
.stat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0,0,0,.18); }
.stat-card::after {
    content: '';
    position: absolute;
    bottom: -30px; right: -30px;
    width: 110px; height: 110px;
    border-radius: 50%;
    background: rgba(255,255,255,.12);
}
.stat-card .stat-icon {
    font-size: 2.2rem;
    opacity: .85;
    margin-bottom: .75rem;
}
.stat-card .stat-value {
    font-size: 2.4rem;
    font-weight: 800;
    line-height: 1;
    margin-bottom: .3rem;
}
.stat-card .stat-label {
    font-size: .8rem;
    font-weight: 600;
    opacity: .85;
    text-transform: uppercase;
    letter-spacing: .05em;
}
.stat-card .stat-sub {
    font-size: .75rem;
    opacity: .7;
    margin-top: .25rem;
}

.sc-total    { background: linear-gradient(135deg, #1565c0, #1976d2); }
.sc-open     { background: linear-gradient(135deg, #e65100, #f57c00); }
.sc-closed   { background: linear-gradient(135deg, #1b5e20, #2e7d32); }
.sc-action   { background: linear-gradient(135deg, #880e4f, #ad1457); }
.sc-avgdays  { background: linear-gradient(135deg, #4a148c, #6a1b9a); }

/* ── Section headers ───────────────────────────────────────────────── */
.section-heading {
    font-size: 1rem;
    font-weight: 700;
    color: #1a237e;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.section-heading::after {
    content: '';
    flex: 1;
    height: 2px;
    background: linear-gradient(90deg, #1a237e22, transparent);
    border-radius: 2px;
}

/* ── Chart cards ───────────────────────────────────────────────────── */
.chart-card {
    background: #fff;
    border-radius: 14px;
    padding: 1.5rem;
    box-shadow: 0 1px 6px rgba(0,0,0,.06);
    height: 100%;
}
.chart-card .chart-title {
    font-size: .875rem;
    font-weight: 700;
    color: #37474f;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: .4rem;
}
.chart-card .chart-title i { color: #1565c0; }

/* ── Table cards ───────────────────────────────────────────────────── */
.table-card {
    background: #fff;
    border-radius: 14px;
    padding: 1.5rem;
    box-shadow: 0 1px 6px rgba(0,0,0,.06);
}
.table-card table thead th {
    background: #1a237e;
    color: #fff;
    font-size: .8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .04em;
    border: none;
    padding: .65rem .75rem;
}
.table-card table tbody td {
    font-size: .85rem;
    vertical-align: middle;
    border-color: #f0f4f8;
    padding: .6rem .75rem;
}
.table-card table tbody tr:hover td { background: #f5f7ff; }

/* ── Status badges ─────────────────────────────────────────────────── */
.badge-status {
    font-size: .7rem;
    padding: .3em .65em;
    border-radius: 6px;
    font-weight: 600;
    white-space: nowrap;
}
.bs-SAVED               { background: #e3f2fd; color: #0d47a1; }
.bs-SUBMITTED           { background: #fff3e0; color: #bf360c; }
.bs-REJECTED            { background: #fce4ec; color: #880e4f; }
.bs-PO_REVIEW           { background: #e8eaf6; color: #283593; }
.bs-PO_SUBMITTED        { background: #e0f2f1; color: #004d40; }
.bs-PO_APPROVED         { background: #e8f5e9; color: #1b5e20; }
.bs-PO_REJECTED         { background: #fff8e1; color: #e65100; }
.bs-WAITING_FOR_CLOSURE { background: #fce4ec; color: #880e4f; }
.bs-REVISION_REQUESTED  { background: #f3e5f5; color: #4a148c; }
.bs-PKSF_REJECTED       { background: #ffebee; color: #b71c1c; }
.bs-CLOSED              { background: #e8f5e9; color: #1b5e20; }

/* ── Priority pill ─────────────────────────────────────────────────── */
.badge-priority { font-size: .7rem; padding: .3em .65em; border-radius: 20px; font-weight: 700; }
.bp-HIGH   { background: #ffebee; color: #c62828; }
.bp-MEDIUM { background: #fff8e1; color: #ef6c00; }
.bp-LOW    { background: #e8f5e9; color: #2e7d32; }

/* ── Days pending indicator ────────────────────────────────────────── */
.days-bar {
    display: flex;
    align-items: center;
    gap: .5rem;
}
.days-bar-fill {
    height: 6px;
    border-radius: 3px;
    flex: 1;
    background: #e9ecef;
    overflow: hidden;
}
.days-bar-fill span {
    display: block;
    height: 100%;
    border-radius: 3px;
    background: linear-gradient(90deg, #1565c0, #e53935);
    transition: width .4s ease;
}
.days-num { font-size: .75rem; font-weight: 700; color: #546e7a; white-space: nowrap; }

/* ── Loading overlay ───────────────────────────────────────────────── */
#loadingOverlay {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(255,255,255,.55);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; pointer-events: none; transition: opacity .2s;
}
#loadingOverlay.active { opacity: 1; pointer-events: all; }
#loadingOverlay .spinner-wrapper {
    background: #fff; border-radius: 16px; padding: 2rem 2.5rem;
    text-align: center; box-shadow: 0 8px 32px rgba(0,0,0,.15);
}
#loadingOverlay .spinner-border { width: 2.5rem; height: 2.5rem; color: #1565c0; }
#loadingOverlay p { margin: .75rem 0 0; font-size: .875rem; color: #546e7a; font-weight: 500; }

/* ── Responsive tweaks ─────────────────────────────────────────────── */
@media (max-width: 768px) {
    .analytics-header { padding: 1.25rem 1.5rem; }
    .analytics-header h4 { font-size: 1.25rem; }
    .stat-card .stat-value { font-size: 2rem; }
}
</style>
@endpush

@section('content')

{{-- Loading overlay --}}
<div id="loadingOverlay">
    <div class="spinner-wrapper">
        <div class="spinner-border" role="status"></div>
        <p>Refreshing analytics…</p>
    </div>
</div>

<div class="container-fluid">

    {{-- ── Page header ─────────────────────────────────────────────── --}}
    <div class="analytics-header">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h4><i class="bi bi-bar-chart-line me-2"></i>Analytics Dashboard</h4>
                <p>Real-time overview of the PKSF Action Matrix system</p>
            </div>
            <div class="text-end">
                <div style="font-size:.8rem;opacity:.7;">Last refreshed</div>
                <div id="refreshedAt" style="font-size:.9rem;font-weight:600;">{{ now()->format('d M Y, h:i A') }}</div>
            </div>
        </div>
    </div>

    {{-- ── Filter bar ──────────────────────────────────────────────── --}}
    <div class="filter-bar">
        <i class="bi bi-funnel text-primary me-1" style="font-size:1.1rem;"></i>
        <strong style="font-size:.875rem;color:#37474f;margin-right:.25rem;">Filters:</strong>

        {{-- Period --}}
        <div>
            <label for="filterPeriod">Period</label>
            <select id="filterPeriod" class="form-select form-select-sm">
                <option value="1month"  {{ $selectedPeriod === '1month'  ? 'selected' : '' }}>Last Month</option>
                <option value="3months" {{ $selectedPeriod === '3months' ? 'selected' : '' }}>Last 3 Months</option>
                <option value="6months" {{ $selectedPeriod === '6months' ? 'selected' : '' }}>Last 6 Months</option>
                <option value="1year"   {{ $selectedPeriod === '1year'   ? 'selected' : '' }}>Last Year</option>
                <option value="all"     {{ $selectedPeriod === 'all'     ? 'selected' : '' }}>All Time</option>
            </select>
        </div>

        @if($showPoChart)
        {{-- PO Code — Admin / PKSF only --}}
        <div>
            <label for="filterPo">PO Code</label>
            <select id="filterPo" class="form-select form-select-sm" style="min-width:160px;">
                <option value="">All POs</option>
                @foreach($poList as $po)
                    <option value="{{ $po }}" {{ $selectedPo === $po ? 'selected' : '' }}>{{ $po }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <button id="btnRefresh" class="btn btn-primary btn-sm ms-auto" style="border-radius:8px;">
            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
        </button>
        <button id="btnClearFilters" class="btn btn-outline-secondary btn-sm" style="border-radius:8px;">
            <i class="bi bi-x-circle me-1"></i>Clear
        </button>
    </div>

    {{-- ── Stat cards ──────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-sm-6 col-lg">
            <div class="stat-card sc-total">
                <div class="stat-icon"><i class="bi bi-grid-3x3-gap-fill"></i></div>
                <div class="stat-value" id="statTotal">{{ $stats['total'] }}</div>
                <div class="stat-label">Total Matrices</div>
                <div class="stat-sub">All time in scope</div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-lg">
            <div class="stat-card sc-open">
                <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-value" id="statOpen">{{ $stats['open'] }}</div>
                <div class="stat-label">Open</div>
                <div class="stat-sub">Active / In-progress</div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-lg">
            <div class="stat-card sc-closed">
                <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
                <div class="stat-value" id="statClosed">{{ $stats['closed'] }}</div>
                <div class="stat-label">Closed</div>
                <div class="stat-sub">Successfully resolved</div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-lg">
            <div class="stat-card sc-action">
                <div class="stat-icon"><i class="bi bi-bell-fill"></i></div>
                <div class="stat-value" id="statAction">{{ $stats['action_required'] }}</div>
                <div class="stat-label">Action Required</div>
                <div class="stat-sub">Awaiting your desk</div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-lg">
            <div class="stat-card sc-avgdays">
                <div class="stat-icon"><i class="bi bi-calendar-event"></i></div>
                <div class="stat-value" id="statAvg">{{ $stats['avg_days'] }}</div>
                <div class="stat-label">Avg Days to Response</div>
                <div class="stat-sub">Creation → first comment</div>
            </div>
        </div>
    </div>

    {{-- ── Row 1: Status donut + Priority bar ─────────────────────── --}}
    <div class="row g-3 mb-4">
        {{-- Status donut --}}
        <div class="col-12 col-lg-5">
            <div class="chart-card">
                <div class="chart-title">
                    <i class="bi bi-pie-chart-fill"></i> Status Distribution
                </div>
                <div style="position:relative;height:280px;">
                    <canvas id="chartStatus"></canvas>
                </div>
            </div>
        </div>

        {{-- Priority bar --}}
        <div class="col-12 col-lg-3">
            <div class="chart-card">
                <div class="chart-title">
                    <i class="bi bi-bar-chart-fill"></i> Priority Breakdown
                </div>
                <div style="position:relative;height:280px;">
                    <canvas id="chartPriority"></canvas>
                </div>
            </div>
        </div>

        {{-- Category bar --}}
        <div class="col-12 col-lg-4">
            <div class="chart-card">
                <div class="chart-title">
                    <i class="bi bi-tags-fill"></i> Observation Category
                </div>
                <div style="position:relative;height:280px;">
                    <canvas id="chartCategory"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Row 2: Monthly trend (full width) ──────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="chart-card">
                <div class="chart-title">
                    <i class="bi bi-graph-up-arrow"></i> Monthly Creation Trend
                    <span style="font-size:.75rem;font-weight:400;color:#90a4ae;margin-left:.5rem;">(Always last 12 months)</span>
                </div>
                <div style="position:relative;height:230px;">
                    <canvas id="chartMonthly"></canvas>
                </div>
            </div>
        </div>
    </div>

    @if($showPoChart)
    {{-- ── Row 3: PO Chart (Admin / PKSF only) ────────────────────── --}}
    <div class="row g-3 mb-4" id="poChartRow">
        <div class="col-12">
            <div class="chart-card">
                <div class="chart-title">
                    <i class="bi bi-buildings-fill"></i> Matrices by PO Code
                    <span style="font-size:.75rem;font-weight:400;color:#90a4ae;margin-left:.5rem;">(Top 10)</span>
                </div>
                <div style="position:relative;height:260px;">
                    <canvas id="chartPo"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Row 4: Recent matrices + Longest pending ────────────────── --}}
    <div class="row g-3 mb-4">
        {{-- Recent matrices --}}
        <div class="col-12 col-xl-6">
            <div class="table-card">
                <div class="section-heading">
                    <i class="bi bi-clock-history text-primary"></i> Recently Created (Top 8)
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tblRecent">
                        <thead>
                            <tr>
                                <th>ACM ID</th>
                                <th>PO Code</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyRecent">
                            @foreach($recentMatrices as $m)
                            <tr>
                                <td>
                                    <a href="{{ route('action-matrix.show', $m['acm_id']) }}"
                                       class="text-decoration-none fw-semibold" style="color:#1565c0;">
                                        {{ $m['acm_id'] }}
                                    </a>
                                </td>
                                <td>{{ $m['po_code'] }}</td>
                                <td>
                                    <span class="badge-priority bp-{{ $m['priority'] }}">
                                        {{ $m['priority'] }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-status bs-{{ $m['status_raw'] }}">
                                        {{ $m['status'] }}
                                    </span>
                                </td>
                                <td style="white-space:nowrap;font-size:.8rem;color:#78909c;">
                                    {{ $m['created_at'] }}
                                </td>
                            </tr>
                            @endforeach
                            @if(empty($recentMatrices))
                            <tr><td colspan="5" class="text-center text-muted py-4">No data available</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Longest pending --}}
        <div class="col-12 col-xl-6">
            <div class="table-card">
                <div class="section-heading">
                    <i class="bi bi-exclamation-triangle-fill text-warning"></i> Longest Pending (Top 8)
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tblPending">
                        <thead>
                            <tr>
                                <th>ACM ID</th>
                                <th>PO Code</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Days Pending</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyPending">
                            @php $maxDays = collect($longestPending)->max('days_pending') ?: 1; @endphp
                            @foreach($longestPending as $m)
                            <tr>
                                <td>
                                    <a href="{{ route('action-matrix.show', $m['acm_id']) }}"
                                       class="text-decoration-none fw-semibold" style="color:#1565c0;">
                                        {{ $m['acm_id'] }}
                                    </a>
                                </td>
                                <td>{{ $m['po_code'] }}</td>
                                <td>
                                    <span class="badge-priority bp-{{ $m['priority'] }}">
                                        {{ $m['priority'] }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-status bs-{{ $m['status_raw'] }}">
                                        {{ $m['status'] }}
                                    </span>
                                </td>
                                <td>
                                    <div class="days-bar">
                                        <div class="days-bar-fill">
                                            <span style="width:{{ min(100, round($m['days_pending']/$maxDays*100)) }}%"></span>
                                        </div>
                                        <span class="days-num">{{ $m['days_pending'] }}d</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            @if(empty($longestPending))
                            <tr><td colspan="5" class="text-center text-muted py-4">No pending matrices</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div><!-- /container-fluid -->
@endsection

@php
    $analyticsJson = [
        'statusChart'    => $statusChart,
        'priorityChart'  => $priorityChart,
        'categoryChart'  => $categoryChart,
        'monthlyChart'   => $monthlyChart,
        'poChart'        => $poChart,
        'recentMatrices' => $recentMatrices,
        'longestPending' => $longestPending,
        'stats'          => $stats,
        'showPoChart'    => $showPoChart,
    ];
@endphp

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
// ── Initial data from server ─────────────────────────────────────────────
const ANALYTICS = @json($analyticsJson);

const AJAX_URL   = '{{ route("analytics.data") }}';
const MATRIX_URL = '{{ url("action-matrix") }}';

// ── Chart.js defaults ────────────────────────────────────────────────────
Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
Chart.defaults.font.size   = 11;

// ── Colour palettes ──────────────────────────────────────────────────────
const STATUS_COLORS = [
    '#1565c0','#e65100','#c62828','#283593','#004d40',
    '#1b5e20','#f57f17','#880e4f','#4a148c','#b71c1c','#2e7d32',
];
const PRIORITY_COLORS = {
    HIGH:   '#ef5350',
    MEDIUM: '#ffa726',
    LOW:    '#66bb6a',
};
const CATEGORY_COLORS = [
    '#42a5f5','#26a69a','#ab47bc','#ef5350','#ffa726',
    '#66bb6a','#ec407a','#7e57c2','#26c6da','#d4e157',
];
const PO_GRADIENT_COLORS = [
    '#1565c0','#1976d2','#1e88e5','#2196f3','#42a5f5',
    '#64b5f6','#90caf9','#bbdefb','#e3f2fd','#f5f5f5',
];

// ── Chart instances ──────────────────────────────────────────────────────
let chartStatus, chartPriority, chartCategory, chartMonthly, chartPo;

// ── Build / rebuild all charts ───────────────────────────────────────────
function buildCharts(d) {
    buildStatus(d.statusChart);
    buildPriority(d.priorityChart);
    buildCategory(d.categoryChart);
    buildMonthly(d.monthlyChart);
    if (d.showPoChart && d.poChart) buildPo(d.poChart);
}

function destroyAndCreate(instance, canvasId, config) {
    if (instance) instance.destroy();
    return new Chart(document.getElementById(canvasId), config);
}

function buildStatus(data) {
    chartStatus = destroyAndCreate(chartStatus, 'chartStatus', {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.data,
                backgroundColor: STATUS_COLORS.slice(0, data.labels.length),
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 8,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'right',
                    labels: { boxWidth: 12, padding: 10, font: { size: 10 } },
                },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.label}: ${ctx.parsed} (${Math.round(ctx.parsed / data.data.reduce((a,b)=>a+b,0)*100)}%)`,
                    },
                },
            },
        },
    });
}

function buildPriority(data) {
    const colors = data.labels.map(l => PRIORITY_COLORS[l] || '#90a4ae');
    chartPriority = destroyAndCreate(chartPriority, 'chartPriority', {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Count',
                data: data.data,
                backgroundColor: colors,
                borderRadius: 6,
                borderSkipped: false,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f0f4f8' } },
                x: { grid: { display: false } },
            },
        },
    });
}

function buildCategory(data) {
    chartCategory = destroyAndCreate(chartCategory, 'chartCategory', {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Count',
                data: data.data,
                backgroundColor: CATEGORY_COLORS.slice(0, data.labels.length),
                borderRadius: 6,
                borderSkipped: false,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f0f4f8' } },
                y: { grid: { display: false }, ticks: { font: { size: 10 } } },
            },
        },
    });
}

function buildMonthly(data) {
    chartMonthly = destroyAndCreate(chartMonthly, 'chartMonthly', {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Matrices Created',
                data: data.data,
                borderColor: '#1565c0',
                backgroundColor: 'rgba(21,101,192,.12)',
                borderWidth: 2.5,
                pointRadius: 4,
                pointBackgroundColor: '#1565c0',
                pointHoverRadius: 6,
                tension: 0.4,
                fill: true,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false },
            },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f0f4f8' } },
                x: { grid: { display: false } },
            },
        },
    });
}

function buildPo(data) {
    chartPo = destroyAndCreate(chartPo, 'chartPo', {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Matrices',
                data: data.data,
                backgroundColor: PO_GRADIENT_COLORS.slice(0, data.labels.length),
                borderRadius: 6,
                borderSkipped: false,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f0f4f8' } },
                x: { grid: { display: false } },
            },
        },
    });
}

// ── Render stat cards ────────────────────────────────────────────────────
function renderStats(stats) {
    document.getElementById('statTotal').textContent  = stats.total;
    document.getElementById('statOpen').textContent   = stats.open;
    document.getElementById('statClosed').textContent = stats.closed;
    document.getElementById('statAction').textContent = stats.action_required;
    document.getElementById('statAvg').textContent    = stats.avg_days;
}

// ── Render recent matrices table ─────────────────────────────────────────
function renderRecent(rows) {
    const tbody = document.getElementById('tbodyRecent');
    if (!rows || !rows.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No data available</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(m => `
        <tr>
            <td><a href="${MATRIX_URL}/${m.acm_id}" class="text-decoration-none fw-semibold" style="color:#1565c0;">${esc(m.acm_id)}</a></td>
            <td>${esc(m.po_code)}</td>
            <td><span class="badge-priority bp-${esc(m.priority)}">${esc(m.priority)}</span></td>
            <td><span class="badge-status bs-${esc(m.status_raw)}">${esc(m.status)}</span></td>
            <td style="white-space:nowrap;font-size:.8rem;color:#78909c;">${esc(m.created_at)}</td>
        </tr>
    `).join('');
}

// ── Render longest pending table ─────────────────────────────────────────
function renderPending(rows) {
    const tbody = document.getElementById('tbodyPending');
    if (!rows || !rows.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No pending matrices</td></tr>';
        return;
    }
    const maxDays = Math.max(...rows.map(r => r.days_pending), 1);
    tbody.innerHTML = rows.map(m => {
        const pct = Math.min(100, Math.round(m.days_pending / maxDays * 100));
        return `
        <tr>
            <td><a href="${MATRIX_URL}/${m.acm_id}" class="text-decoration-none fw-semibold" style="color:#1565c0;">${esc(m.acm_id)}</a></td>
            <td>${esc(m.po_code)}</td>
            <td><span class="badge-priority bp-${esc(m.priority)}">${esc(m.priority)}</span></td>
            <td><span class="badge-status bs-${esc(m.status_raw)}">${esc(m.status)}</span></td>
            <td>
                <div class="days-bar">
                    <div class="days-bar-fill"><span style="width:${pct}%"></span></div>
                    <span class="days-num">${m.days_pending}d</span>
                </div>
            </td>
        </tr>`;
    }).join('');
}

// ── XSS helper ───────────────────────────────────────────────────────────
function esc(s) {
    if (s == null) return '';
    return String(s)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

// ── AJAX refresh ─────────────────────────────────────────────────────────
function refresh() {
    const period  = document.getElementById('filterPeriod').value;
    const poEl    = document.getElementById('filterPo');
    const poCode  = poEl ? poEl.value : '';

    document.getElementById('loadingOverlay').classList.add('active');

    fetch(`${AJAX_URL}?period=${encodeURIComponent(period)}&po_code=${encodeURIComponent(poCode)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
    .then(r => r.json())
    .then(d => {
        renderStats(d.stats);
        buildStatus(d.statusChart);
        buildPriority(d.priorityChart);
        buildCategory(d.categoryChart);
        buildMonthly(d.monthlyChart);
        if (d.showPoChart && d.poChart) buildPo(d.poChart);
        renderRecent(d.recentMatrices);
        renderPending(d.longestPending);
        document.getElementById('refreshedAt').textContent =
            new Date().toLocaleString('en-BD', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' });
    })
    .catch(console.error)
    .finally(() => {
        document.getElementById('loadingOverlay').classList.remove('active');
    });
}

// ── Events ───────────────────────────────────────────────────────────────
document.getElementById('btnRefresh').addEventListener('click', refresh);

document.getElementById('btnClearFilters').addEventListener('click', () => {
    document.getElementById('filterPeriod').value = '6months';
    const poEl = document.getElementById('filterPo');
    if (poEl) poEl.value = '';
    refresh();
});

// Auto-refresh on filter change (debounced)
['filterPeriod', 'filterPo'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('change', refresh);
});

// ── Initial render ───────────────────────────────────────────────────────
buildCharts(ANALYTICS);
renderRecent(ANALYTICS.recentMatrices);
renderPending(ANALYTICS.longestPending);
</script>
@endpush
