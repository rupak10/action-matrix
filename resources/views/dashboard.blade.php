<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
            <div>
                <h2 class="h4 mb-1 fw-bold text-sl-primary">Dashboard Overview</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 smaller">
                        <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-sl-muted">Action Matrix</a></li>
                        <li class="breadcrumb-item active text-sl-primary fw-semibold" aria-current="page">Overview</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary border-sl-border bg-white text-sl-primary fw-600 px-3 py-2 rounded-3">
                    <i class="bi bi-calendar3 me-2"></i> Last 30 Days
                </button>
                <button class="btn btn-sm btn-sl-primary px-3 py-2 rounded-3">
                    <i class="bi bi-plus-lg me-2"></i> New Matrix
                </button>
            </div>
        </div>
    </x-slot>

    <div class="row g-4">
        @php
            $statConfigs = [
                ['label' => 'Total Matrices', 'icon' => 'bi-grid-fill', 'color' => 'primary'],
                ['label' => 'Active Projects', 'icon' => 'bi-briefcase-fill', 'color' => 'success'],
                ['label' => 'Pending Actions', 'icon' => 'bi-clock-history', 'color' => 'warning'],
            ];
        @endphp

        @foreach ($stats as $index => $stat)
            @php $config = $statConfigs[$index % 3]; @endphp
            <div class="col-sm-6 col-xl-4">
                <div class="sl-card h-100 p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-sl-primary-soft p-3 rounded-4">
                            <i class="bi {{ $config['icon'] }} text-sl-primary fs-4"></i>
                        </div>
                        <div class="text-end">
                            <span class="badge-sl-soft badge-sl-soft-success">
                                <i class="bi bi-graph-up-arrow me-1"></i> {{ 4 + ($index * 2) }}%
                            </span>
                        </div>
                    </div>
                    <div>
                        <p class="text-sl-muted smaller fw-bold text-uppercase mb-1">{{ $config['label'] }}</p>
                        <h3 class="fw-bold mb-0 text-sl-primary">{{ $stat['value'] }}</h3>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mt-1">
        <div class="col-xl-8">
            <div class="sl-card h-100">
                <div class="p-4 border-bottom border-sl-border d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-sl-primary">Recent Activity</h5>
                    <a href="#" class="smaller text-decoration-none text-sl-primary fw-600">View All Activity</a>
                </div>
                <div class="p-4">
                    <div class="d-grid gap-4">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="bg-sl-primary-soft p-2 rounded-circle">
                                <i class="bi bi-check2-circle text-sl-primary fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold text-sl-primary">Laravel Project Bootstrap</h6>
                                <p class="mb-0 text-sl-muted smaller">Application shell, auth, and starter dashboard created successfully.</p>
                                <span class="smaller text-sl-muted mt-2 d-block"><i class="bi bi-clock me-1"></i> 2 hours ago</span>
                            </div>
                        </div>
                        <div class="d-flex gap-3 align-items-start">
                            <div class="bg-sl-primary-soft p-2 rounded-circle">
                                <i class="bi bi-palette text-sl-primary fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold text-sl-primary">Silva Redesign Implemented</h6>
                                <p class="mb-0 text-sl-muted smaller">Updated sidebar, header, and dashboard widgets to match Silva aesthetic.</p>
                                <span class="smaller text-sl-muted mt-2 d-block"><i class="bi bi-clock me-1"></i> Just now</span>
                            </div>
                        </div>
                        <div class="d-flex gap-3 align-items-start">
                            <div class="bg-warning-subtle p-2 rounded-circle">
                                <i class="bi bi-database text-warning fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold text-sl-primary">Schema Migration Pending</h6>
                                <p class="mb-0 text-sl-muted smaller">Convert PostgreSQL schema into Laravel migrations for the core modules.</p>
                                <span class="smaller text-sl-muted mt-2 d-block"><i class="bi bi-clock me-1"></i> Upcoming</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="sl-card h-100">
                <div class="p-4 border-bottom border-sl-border">
                    <h5 class="mb-0 fw-bold text-sl-primary">Quick Settings</h5>
                </div>
                <div class="p-4">
                    <div class="bg-sl-bg p-3 rounded-4 mb-4 border border-sl-border">
                        <p class="text-sl-muted smaller mb-1 fw-semibold">ADMIN ACCESS</p>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-envelope text-sl-muted me-2"></i>
                            <span class="small fw-bold text-sl-primary">{{ env('ADMIN_EMAIL', 'admin@example.com') }}</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-key text-sl-muted me-2"></i>
                            <span class="small fw-bold text-sl-primary">••••••••</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-end mb-2">
                            <span class="smaller fw-bold text-sl-primary text-uppercase">Project Progress</span>
                            <span class="smaller fw-bold text-sl-primary">78%</span>
                        </div>
                        <div class="progress rounded-pill" style="height: 6px;">
                            <div class="progress-bar" style="width: 78%; background-color: var(--sl-primary);"></div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <div class="d-flex justify-content-between align-items-center p-2 hover-bg-sl rounded-3 transition-all">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-shield-check text-success"></i>
                                <span class="smaller fw-semibold text-sl-primary">Authentication</span>
                            </div>
                            <span class="badge-sl-soft badge-sl-soft-success">DONE</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 hover-bg-sl rounded-3 transition-all">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-layout-sidebar text-success"></i>
                                <span class="smaller fw-semibold text-sl-primary">Admin Shell</span>
                            </div>
                            <span class="badge-sl-soft badge-sl-soft-success">DONE</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 hover-bg-sl rounded-3 transition-all">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-database-exclamation text-warning"></i>
                                <span class="smaller fw-semibold text-sl-primary">Schema Mapping</span>
                            </div>
                            <span class="badge-sl-soft badge-sl-soft-warning">PENDING</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-12">
            <div class="sl-card overflow-hidden">
                <div class="p-4 border-bottom border-sl-border d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold text-sl-primary">Module Roadmap</h5>
                        <p class="mb-0 text-sl-muted smaller">Suggested next implementation blocks for Action Matrix</p>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary border-sl-border text-sl-primary fw-600 px-3">Export CSV</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-sl-bg">
                            <tr>
                                <th class="ps-4 py-3 text-sl-muted smaller text-uppercase fw-bold border-0">Module</th>
                                <th class="py-3 text-sl-muted smaller text-uppercase fw-bold border-0">Description</th>
                                <th class="py-3 text-sl-muted smaller text-uppercase fw-bold border-0">Status</th>
                                <th class="py-3 text-sl-muted smaller text-uppercase fw-bold border-0">Priority</th>
                                <th class="pe-4 py-3 text-end border-0"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-sl-primary">ACM Master</div>
                                </td>
                                <td><span class="text-sl-muted smaller">Core listing/detail flow from PG schema.</span></td>
                                <td><span class="badge-sl-soft badge-sl-soft-warning">NEXT PHASE</span></td>
                                <td><span class="text-danger fw-600 smaller">CRITICAL</span></td>
                                <td class="pe-4 text-end">
                                    <button class="btn btn-link text-sl-muted p-0"><i class="bi bi-three-dots-vertical"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-sl-primary">Comments</div>
                                </td>
                                <td><span class="text-sl-muted smaller">History and source-based threads.</span></td>
                                <td><span class="badge-sl-soft badge-sl-soft-danger">BACKLOG</span></td>
                                <td><span class="text-warning fw-600 smaller">MEDIUM</span></td>
                                <td class="pe-4 text-end">
                                    <button class="btn btn-link text-sl-muted p-0"><i class="bi bi-three-dots-vertical"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-sl-primary">Attachments</div>
                                </td>
                                <td><span class="text-sl-muted smaller">Master/comment-related file storage.</span></td>
                                <td><span class="badge-sl-soft badge-sl-soft-danger">BACKLOG</span></td>
                                <td><span class="text-warning fw-600 smaller">MEDIUM</span></td>
                                <td class="pe-4 text-end">
                                    <button class="btn btn-link text-sl-muted p-0"><i class="bi bi-three-dots-vertical"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        .fw-600 { font-weight: 600; }
        .smaller { font-size: 0.75rem; }
        .hover-bg-sl:hover { background-color: var(--sl-bg); }
        .transition-all { transition: all 0.2s ease; }
    </style>
</x-app-layout>
