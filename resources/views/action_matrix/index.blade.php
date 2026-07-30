@extends('layouts.app')

@section('content')
@php
    $myPrimarySupervisorRow = auth()->user()->supervisors()->where('is_primary', true)->with('supervisor')->first();
    $mySupervisor    = $myPrimarySupervisorRow?->supervisor;
    $mySupervisorEmpId = $mySupervisor?->emp_id;
@endphp
@php
    $employeeName = function ($empId) use ($usersByEmpId) {
        if (!$empId) {
            return 'Not assigned';
        }

        $user = $usersByEmpId->get($empId);

        return $user ? $user->name : $empId;
    };

    $employeeLabel = function ($empId) use ($usersByEmpId) {
        if (!$empId) {
            return 'Not assigned';
        }

        $user = $usersByEmpId->get($empId);

        return $user ? $user->name . ' (' . $empId . ')' : $empId;
    };
@endphp
<div class="container-fluid pt-1 pb-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="display-6 fw-bold text-gradient mb-1">Follow-up Observation List</h2>
            @if(auth()->user()->isPksf())
                <p class="text-muted">Manage and track all observations and PO responses.</p>
            @else
                <p class="text-muted">View observations assigned to you and submit your responses.</p>
            @endif
        </div>
        @if(auth()->user()->hasAnyRole(['PKSF_CO']))
            {{-- <a href="{{ route('action-matrix.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="bi bi-plus-lg me-2"></i> Create New Matrix
            </a> --}}
            <a href="{{ route('action-matrix.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="bi bi-plus-lg me-2"></i> Create New Observation
            </a>
        @endif
    </div>

    <!-- Stats Overview -->
    <div class="row g-3 mb-4">
        {{-- Total Matrices --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 text-white"
                 style="background: linear-gradient(135deg, #1b3a3a 0%, #2e5454 100%);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small opacity-75 mb-1">Total Matrices</div>
                        <div class="h4 fw-bold mb-0">{{ $stats['total'] }}</div>
                    </div>
                    <i class="bi bi-layers fs-1 opacity-25"></i>
                </div>
            </div>
        </div>

        {{-- Action Required --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 text-white"
                 style="background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small opacity-75 mb-1">Action Required</div>
                        <div class="h4 fw-bold mb-0">{{ $stats['action_required'] }}</div>
                    </div>
                    <i class="bi bi-exclamation-circle fs-1 opacity-25"></i>
                </div>
            </div>
        </div>

        {{-- In Progress --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3"
                 style="background: linear-gradient(135deg, #d4a017 0%, #f0c040 100%);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-dark opacity-75 mb-1">In Progress</div>
                        <div class="h4 fw-bold mb-0 text-dark">{{ $stats['in_progress'] }}</div>
                    </div>
                    <i class="bi bi-arrow-repeat fs-1 opacity-25 text-dark"></i>
                </div>
            </div>
        </div>

        {{-- Closed --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 text-white"
                 style="background: linear-gradient(135deg, #1a7a4a 0%, #27ae60 100%);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small opacity-75 mb-1">Closed</div>
                        <div class="h4 fw-bold mb-0">{{ $stats['closed'] }}</div>
                    </div>
                    <i class="bi bi-check-circle fs-1 opacity-25"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden glass-card">
        <div class="card-body p-0">

            <!-- Tabs -->
            <div class="px-4 pt-3 border-bottom bg-white">
                <ul class="nav nav-tabs border-0" id="matrixTabs">
                    <li class="nav-item">
                        <button class="nav-link active fw-semibold px-4" id="tab-action" data-tab="action_required">
                            <i class="bi bi-exclamation-circle me-1"></i>Action Required
                            @if($stats['action_required'] > 0)
                                <span class="badge rounded-pill bg-danger ms-1">{{ $stats['action_required'] }}</span>
                            @endif
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold px-4" id="tab-ongoing" data-tab="ongoing">
                            <i class="bi bi-arrow-repeat me-1"></i>Ongoing
                            @if($stats['in_progress'] > 0)
                                <span class="badge rounded-pill bg-warning text-dark ms-1">{{ $stats['in_progress'] }}</span>
                            @endif
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold px-4" id="tab-all" data-tab="all">
                            <i class="bi bi-list-ul me-1"></i>All Observations
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Filter Bar -->
            <div class="p-3 bg-light border-bottom d-flex flex-wrap align-items-center gap-2">
                <i class="bi bi-funnel text-primary me-1" style="font-size:1.1rem;"></i>
                <strong style="font-size:.875rem;color:#37474f;margin-right:.25rem;">Filters:</strong>

                {{-- Dropdown 1: View (hidden on Action Required tab) --}}
                <select id="filterView" class="form-select form-select-sm rounded-pill border-0 shadow-sm" style="width:auto; min-width:170px; display:none;">
                    <option value="all">All Matrices</option>
                    <option value="action_required">Action Required</option>
                    @if(auth()->user()->isPksf())
                        <option value="created_by_me">Created by Me</option>
                    @endif
                    <option value="completed">Completed</option>
                </select>

                {{-- Dropdown 2: PO — PKSF users only (PO users belong to exactly one PO) --}}
                @if(auth()->user()->isPksf())
                <select id="filterPo" class="form-select form-select-sm rounded-pill border-0 shadow-sm" style="width:auto; min-width:150px;">
                    <option value="">All POs</option>
                    @foreach($formOptions['poList'] as $po)
                        <option value="{{ $po['code'] }}">{{ $po['name'] }}</option>
                    @endforeach
                </select>
                @endif

                {{-- Dropdown 3: Priority --}}
                <select id="filterPriority" class="form-select form-select-sm rounded-pill border-0 shadow-sm" style="width:auto; min-width:150px;">
                    <option value="">All Priorities</option>
                    @foreach($formOptions['priorities'] as $p)
                        <option value="{{ $p }}">{{ ucfirst(strtolower($p)) }}</option>
                    @endforeach
                </select>

                {{-- Clear --}}
                <button id="btnClearFilters" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="display:none;">
                    <i class="bi bi-x-circle me-1"></i>Clear
                </button>
            </div>

            <div class="table-responsive p-4">
                <table id="matrixTable" class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="border-0">ACM ID</th>
                            <th class="border-0 text-center">PO Code</th>
                            <th class="border-0">Visit Date</th>
                            <th class="border-0">Category</th>
                            <th class="border-0">Priority</th>
                            <th class="border-0">Status</th>
                            <th class="border-0">Incoming From</th>
                            <th class="border-0 text-center" data-orderable="false">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Review Modal (PKSF Supervisor) -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-bottom-0 p-4 pb-3">
                <h5 class="modal-title fw-bold text-dark" id="reviewModalLabel">Review Action Matrix <span class="text-primary" id="reviewAcmId"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reviewForm" method="POST" action="">
                @csrf
                <div class="modal-body p-4 pt-3">
                    <input type="hidden" name="acm_id" id="review_acm_id_hidden">
                    
                    <div class="alert alert-info border-0 shadow-none smaller mb-4">
                        <i class="bi bi-info-circle-fill me-2"></i>You are reviewing this matrix. Please provide your decision below.
                    </div>

                    <div class="mb-3">
                        <label for="review_remarks" class="form-label fw-bold">Reviewer Remarks</label>
                        <textarea class="form-control" id="review_remarks" name="remarks" rows="4" placeholder="Enter your reasons for approval or sending back..."></textarea>
                    </div>

                    <div class="mb-3" id="poAssignWrapper">
                        <label class="form-label fw-bold text-primary">Assigned PO Supervisor</label>
                        <div class="p-3 bg-light-primary rounded-3 border border-primary-subtle d-flex align-items-center">
                            <div class="avatar-xs bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 32px; height: 32px;">
                                <i class="bi bi-person"></i>
                            </div>
                            <div>
                                <div id="po_officer_name_display" class="fw-bold text-dark">Searching for supervisor...</div>
                                <div class="smaller text-muted">Role: PO Supervisor</div>
                            </div>
                        </div>
                        <input type="hidden" name="to_emp_id" id="to_emp_id_hidden">

                        {{-- Data source for JS --}}
                        <div id="poOfficersData" style="display:none;">
                            @foreach($poOfficers as $puser)
                                <span data-emp-id="{{ $puser->emp_id }}" data-name="{{ $puser->name }}" data-pocode="{{ $puser->po_code }}"></span>
                            @endforeach
                        </div>

                        <div class="form-text text-muted smaller mt-2">
                            <i class="bi bi-info-circle me-1"></i>System automatically assigns this to the PO Supervisor (PO_SUPERVISOR).
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 bg-light p-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <div class="d-flex gap-2">
                        <button type="button" id="btnSendBack" class="btn btn-outline-danger px-4 rounded-pill shadow-sm">
                            <i class="bi bi-arrow-left-circle me-2"></i>Send Back
                        </button>
                        <button type="button" id="btnApprove" class="btn btn-success px-4 rounded-pill shadow-sm">
                            <i class="bi bi-check-circle me-2"></i>Approve
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Comment Modal (PO Officer) -->
<div class="modal fade" id="commentModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:780px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            <!-- Header -->
            <div class="modal-header border-bottom px-4 py-3" style="background:rgba(27,58,58,0.05);">
                <div class="flex-grow-1 min-w-0 me-3">
                    <div class="d-flex flex-wrap align-items-center gap-2" id="cm_meta_row">
                        <span class="po-cm-meta-chip fw-bold" id="cm_acm_id_display" style="background:#1b3a3a;color:#fff;border-color:#1b3a3a;">—</span>
                    </div>
                </div>
                <button type="button" class="btn-close flex-shrink-0" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="commentForm" method="POST" action="{{ route('action-matrix.comment') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="acm_id" id="comment_acm_id">
                <input type="hidden" name="forward_to_supervisor" id="cm_forward_flag" value="0">
                <input type="hidden" name="comment_sl" id="comment_sl" value="">
            </form>

            <div class="modal-body px-4 py-3">

                    <!-- Observation context -->
                    <div class="fw-bold small mb-2">
                        <span class="d-inline-flex align-items-center justify-content-center rounded me-2"
                              style="width:20px;height:20px;background:#1b3a3a;">
                            <i class="bi bi-eye-fill text-white" style="font-size:0.6rem;"></i>
                        </span>PKSF Observation
                    </div>
                    <div class="bg-light rounded-3 p-3 mb-3" style="border-left:4px solid #1b3a3a;">
                        <div class="small text-dark" id="cm_observation_text" style="max-height:80px;overflow-y:auto;line-height:1.6;">—</div>
                    </div>

                    <!-- PKSF Direction -->
                    <div class="fw-bold small mb-2">
                        <span class="d-inline-flex align-items-center justify-content-center rounded me-2"
                              style="width:20px;height:20px;background:#1b3a3a;">
                            <i class="bi bi-arrow-right-circle-fill text-white" style="font-size:0.6rem;"></i>
                        </span>PKSF Direction
                    </div>
                    <div class="bg-light rounded-3 p-3 mb-3" style="border-left:4px solid #1b3a3a;">
                        <div class="small text-dark" id="cm_direction_text" style="max-height:80px;overflow-y:auto;line-height:1.6;">—</div>
                    </div>

                    <!-- PKSF Attachments (read-only) -->
                    <div class="cm-pksf-att-section mb-3">
                        <div class="fw-bold small mb-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded me-2"
                                  style="width:20px;height:20px;background:#1b3a3a;">
                                <i class="bi bi-paperclip text-white" style="font-size:0.6rem;"></i>
                            </span>PKSF Attachments
                        </div>
                        <div id="cm_pksf_attachments" class="d-flex flex-wrap"></div>
                    </div>

                    <!-- Response textarea -->
                    <div class="mb-3">
                        <label for="comment_detail" class="form-label fw-bold small">
                            <span class="d-inline-flex align-items-center justify-content-center rounded me-2"
                                  style="width:20px;height:20px;background:#0369a1;">
                                <i class="bi bi-pencil-fill text-white" style="font-size:0.6rem;"></i>
                            </span>Your Response
                        </label>
                        <textarea
                            name="comment_detail"
                            id="comment_detail"
                            rows="5"
                            class="form-control"
                            placeholder="Write your formal response to the observation above…"
                            required
                            maxlength="5000"
                            style="resize:vertical;"></textarea>
                        <div class="text-end mt-1">
                            <span class="text-muted" style="font-size:0.75rem;"><span id="cm_char_count">0</span> / 5000</span>
                        </div>
                    </div>

                    <!-- Attachments -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold small mb-0">
                                <span class="d-inline-flex align-items-center justify-content-center rounded me-2"
                                      style="width:20px;height:20px;background:#0369a1;">
                                    <i class="bi bi-paperclip text-white" style="font-size:0.6rem;"></i>
                                </span>Your Attachments <span class="text-muted fw-normal">(optional)</span>
                            </label>
                            <span class="badge bg-light text-dark border" style="font-size:0.75rem;" id="cmFileCountBadge">0 / 3</span>
                        </div>
                        <div id="cmUploadZone" class="border rounded-3 px-3 py-3 text-center"
                             style="cursor:pointer;background:#fafafa;transition:background 0.15s,border-color 0.15s;">
                            <div id="cmUploadPrompt" class="d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-paperclip text-muted" style="font-size:1rem;"></i>
                                <span class="small text-muted">Click to attach files</span>
                                <span class="text-muted" style="font-size:0.72rem;">· PDF · Word · Excel · Images · max 30 MB</span>
                            </div>
                            <div id="cmFileList" style="display:none;text-align:left;"></div>
                            <div id="cmAddMoreWrap" class="mt-2" style="display:none;">
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="cmAddMoreBtn">
                                    <i class="bi bi-plus me-1"></i>Add another file
                                </button>
                            </div>
                        </div>
                        <input type="file" name="attachments[]" id="cmActualFileInput" multiple style="display:none;"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.txt">
                    </div>

                    <!-- Routing info -->
                    <div class="fw-bold small mb-2 mt-1">
                        <span class="d-inline-flex align-items-center justify-content-center rounded me-2"
                              style="width:20px;height:20px;background:#d97706;">
                            <i class="bi bi-send-fill text-white" style="font-size:0.6rem;"></i>
                        </span>Routing
                    </div>
                    <div id="po_routing_card" class="d-flex align-items-center gap-3 bg-light rounded-3 p-2 border" style="border-left:4px solid #d97706 !important;">
                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                             style="width:34px;height:34px;background:#1b3a3a;font-size:0.8rem;">
                            {{ $mySupervisor ? strtoupper(substr($mySupervisor->name, 0, 1)) : '?' }}
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-bold small text-truncate">{{ $mySupervisor ? $mySupervisor->name : 'No Supervisor Assigned' }}</div>
                            <div class="text-muted" style="font-size:0.75rem;">Will review before forwarding to PKSF</div>
                        </div>
                        @if($mySupervisorEmpId)
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Assigned</span>
                        @else
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Not Set</span>
                        @endif
                    </div>

                    @if(!$mySupervisorEmpId)
                        <div class="alert alert-warning mt-2 py-2 small border-0 mb-0">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>Please assign a supervisor in your profile first.
                        </div>
                    @endif

            </div>

            <!-- Footer -->
            <div class="modal-footer border-0 px-4 py-2" style="background:rgba(27,58,58,0.04);">
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                <div class="d-flex gap-2 ms-auto">
                    <button type="submit" form="commentForm" id="btnSaveDraft" class="btn btn-sm btn-warning rounded-pill px-4 fw-semibold shadow-sm">
                        <i class="bi bi-floppy2 me-1"></i>Save Draft
                    </button>
                    <button type="submit" form="commentForm" id="btnSaveForward" class="btn btn-sm btn-primary rounded-pill px-4 fw-semibold shadow-sm"
                            {{ !$mySupervisorEmpId ? 'disabled' : '' }}>
                        <i class="bi bi-send-fill me-1"></i>Submit to Supervisor
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- History / Discussion Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-bottom p-4">
                <h5 class="modal-title fw-bold">Action Matrix History & Discussion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light-subtle">
                <div id="historyContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PO Forward Confirmation Modal -->
<div class="modal fade" id="poForwardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 p-4">
                <h5 class="modal-title fw-bold">Forward to Supervisor?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="poForwardForm" method="POST" action="{{ route('action-matrix.po-forward') }}">
                @csrf
                <div class="modal-body p-4 pt-0">
                    <input type="hidden" name="acm_id" id="po_forward_acm_id">
                    <p class="text-muted">Are you sure you want to forward this response to your supervisor for review? You won't be able to edit your comments once forwarded.</p>
                    
                    <div class="d-flex align-items-center bg-soft-success p-3 rounded-3 border border-success-subtle mb-4">
                        <div class="avatar-xs bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 32px; height: 32px;">
                            {{ $mySupervisor ? substr($mySupervisor->name, 0, 1) : '?' }}
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">{{ $mySupervisor ? $mySupervisor->name : 'No Supervisor Assigned' }}</h6>
                            <small class="text-muted">PO Supervisor</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="po_forward_remarks" class="form-label fw-bold small text-uppercase text-muted">Final Remarks (Optional)</label>
                        <textarea name="remarks" id="po_forward_remarks" class="form-control" rows="3" placeholder="Any final notes for your supervisor..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 d-flex gap-2">
                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4 rounded-pill shadow-sm">
                        <i class="bi bi-send-fill me-2"></i>Forward Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="poReviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:620px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-warning text-dark p-4">
                <h5 class="modal-title fw-bold">Review PO Response</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="poReviewForm" method="POST" action="">
                @csrf
                <div class="modal-body p-4">
                    <input type="hidden" name="acm_id" id="po_review_acm_id">
                    
                    <div class="alert alert-warning border-0 shadow-none smaller mb-4">
                        <i class="bi bi-shield-check me-2"></i>You are reviewing the response prepared by your officer. 
                    </div>

                    <div class="mb-3">
                        <label for="po_review_remarks" class="form-label fw-bold">Supervisor Remarks</label>
                        <textarea class="form-control" id="po_review_remarks" name="remarks" rows="4" placeholder="Enter remarks for approval or sending back to officer..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3 d-flex justify-content-end gap-2 flex-nowrap">
                    <button type="button" class="btn btn-light px-3 rounded-pill text-nowrap" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="btnPoReject" class="btn btn-outline-danger px-3 rounded-pill text-nowrap">
                        <i class="bi bi-x-circle me-1"></i>Send Back to Officer
                    </button>
                    <button type="button" id="btnPoApprove" class="btn btn-success px-3 rounded-pill text-nowrap">
                        <i class="bi bi-check-all me-1"></i>Approve & Send to PKSF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="forwardModal" tabindex="-1" aria-labelledby="forwardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-bottom-0 p-4 pb-3">
                <h5 class="modal-title fw-bold text-dark" id="forwardModalLabel">Forward Matrix <span class="text-primary" id="displayAcmId"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="forwardForm" method="POST" action="{{ route('action-matrix.forward') }}">
                @csrf
                <div class="modal-body p-4 pt-3">
                    <input type="hidden" name="acm_id" id="acm_id_hidden">
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-1">Forwarding To Supervisor</label>
                        <div class="d-flex align-items-center bg-soft-primary p-3 rounded-3 border border-primary-subtle">
                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold">
                                {{ $mySupervisor ? substr($mySupervisor->name, 0, 1) : '?' }}
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-primary">{{ $mySupervisor ? $mySupervisor->name : 'No Supervisor Assigned' }}</h6>
                                <small class="text-muted">{{ $mySupervisorEmpId ?? 'Please assign a supervisor in your profile' }}</small>
                            </div>
                        </div>
                        @if(!$mySupervisorEmpId)
                            <div class="alert alert-warning mt-3 border-0 shadow-none smaller">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>Please assign a supervisor in your profile first.
                            </div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="remarks" class="form-label fw-bold">Remarks / Notes</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Add any comments for your supervisor..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 bg-light p-3">
                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4 rounded-pill shadow-sm" {{ !$mySupervisorEmpId ? 'disabled' : '' }}>
                        <i class="bi bi-send me-2"></i>Send to Supervisor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- PO Supervisor → Forward to PO Officer Modal -->
<div class="modal fade" id="poForwardToOfficerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white p-4">
                <h5 class="modal-title fw-bold">Forward to PO Concern Officer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="poForwardToOfficerForm" method="POST" action="{{ route('action-matrix.po-forward-to-officer') }}">
                @csrf
                <div class="modal-body p-4">
                    <input type="hidden" name="acm_id" id="po_forward_officer_acm_id">
                    <div class="alert alert-info border-0 shadow-none smaller mb-4">
                        <i class="bi bi-info-circle-fill me-2"></i>You are forwarding this observation to the PO Concern Officer for response.
                    </div>

                    {{-- PO CO Info Card --}}
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-1">Forwarding To</label>
                        <div id="poCOInfoCard" class="d-flex align-items-center bg-light p-3 rounded-3 border">
                            <div class="avatar-xs bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 36px; height: 36px;">
                                <i class="bi bi-person" id="poCOInitial"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark" id="poCONameDisplay">Searching for officer...</h6>
                                <small class="text-muted">PO Concern Officer</small>
                            </div>
                        </div>
                        <div id="poCONotFoundAlert" class="alert alert-warning border-0 shadow-none smaller mt-2" style="display:none;">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>No PO Concern Officer found for this PO.
                        </div>
                    </div>

                    {{-- Hidden data source for JS --}}
                    <div id="poCOData" style="display:none;">
                        @foreach($poConcernOfficers as $co)
                            <span data-emp-id="{{ $co->emp_id }}" data-name="{{ $co->name }}" data-pocode="{{ $co->po_code }}"></span>
                        @endforeach
                    </div>

                    <div class="mb-3">
                        <label for="po_forward_officer_remarks" class="form-label fw-bold">Remarks (Optional)</label>
                        <textarea name="remarks" id="po_forward_officer_remarks" class="form-control" rows="3" placeholder="Any instructions for the PO Officer..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 d-flex gap-2">
                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="btnForwardToOfficer" class="btn btn-primary px-4 rounded-pill shadow-sm">
                        <i class="bi bi-send-fill me-2"></i>Forward to Officer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- PKSF Request Closure Modal -->
<div class="modal fade" id="pksfRequestClosureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-success text-white p-4">
                <h5 class="modal-title fw-bold">Submit Closure Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="pksfRequestClosureForm" method="POST" action="{{ route('action-matrix.request-closure') }}">
                @csrf
                <div class="modal-body p-4">
                    <input type="hidden" name="acm_id" id="pksf_closure_acm_id">
                    
                    <div class="alert alert-success border-0 shadow-none smaller mb-4">
                        <i class="bi bi-info-circle-fill me-2"></i>You are satisfied with the response and resolution. This will forward the matrix to your supervisor for final closure.
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-1">Target Supervisor</label>
                        <div class="d-flex align-items-center bg-light p-3 rounded-3 border">
                            <div class="avatar-xs bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 32px; height: 32px;">
                                {{ $mySupervisor ? substr($mySupervisor->name, 0, 1) : '?' }}
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark">{{ $mySupervisor ? $mySupervisor->name : 'No Supervisor Assigned' }}</h6>
                                <small class="text-muted">PKSF Supervisor (Automatic Forward)</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="pksf_closure_remarks" class="form-label fw-bold">Closure Remarks / Justification</label>
                        <textarea class="form-control" id="pksf_closure_remarks" name="remarks" rows="4" placeholder="Explain why you recommend closing this observation..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 p-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4 rounded-pill shadow-sm" {{ !$mySupervisorEmpId ? 'disabled' : '' }}>
                        <i class="bi bi-check-circle-fill me-2"></i>Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- PKSF Request Revision Modal -->
<div class="modal fade" id="pksfRequestRevisionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-warning text-dark p-4">
                <h5 class="modal-title fw-bold">Request PO Revision</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="pksfRequestRevisionForm" method="POST" action="{{ route('action-matrix.request-revision') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <input type="hidden" name="acm_id" id="pksf_revision_acm_id">
                    <input type="hidden" name="prev_comment_sl" id="pksf_revision_prev_sl" value="">

                    <!-- Supervisor's rejection feedback — shown only when status is PKSF_REJECTED -->
                    <div id="pksfRevSupContext" class="mb-4 p-3 rounded-3" style="display:none;background:#fffbeb;border-left:4px solid #ffc107;">
                        <div class="small fw-bold text-uppercase mb-2" style="font-size:.67rem;letter-spacing:.08em;color:#92400e;">
                            <i class="bi bi-chat-left-quote me-1"></i><span id="pksfRevSupName">Supervisor's Feedback</span>
                        </div>
                        <div id="pksfRevSupRemarkText" style="font-size:.875rem;color:#78350f;line-height:1.65;white-space:pre-line;"></div>
                    </div>

                    <div class="alert alert-warning border-0 shadow-none smaller mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>You are not satisfied with the PO response. Please detail what requires revision. Your request will be forwarded to your supervisor for review before going to the PO.
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-1">Forwarding to Supervisor</label>
                        <div class="d-flex align-items-center bg-light p-3 rounded-3 border">
                            <div class="avatar-xs bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 32px; height: 32px;">
                                {{ $mySupervisor ? substr($mySupervisor->name, 0, 1) : '?' }}
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark">{{ $mySupervisor ? $mySupervisor->name : 'No Supervisor Assigned' }}</h6>
                                <small class="text-muted">PKSF Supervisor (Automatic Forward)</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="pksf_revision_detail" class="form-label fw-bold">Detail Comments for PO</label>
                        <textarea name="comment_detail" id="pksf_revision_detail" rows="5" class="form-control" placeholder="Specify clearly what is missing or needs improvement..." required></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Attachments</label>

                        <!-- Previously attached files — shown only at PKSF_REJECTED -->
                        <div id="pksfRevExistingFilesSection" style="display:none;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-muted text-uppercase" style="font-size:.68rem;letter-spacing:.06em;">
                                    <i class="bi bi-paperclip me-1"></i>Previously Attached
                                </span>
                                <span class="small text-muted" style="font-size:.72rem;">Click × to remove</span>
                            </div>
                            <div id="pksfRevExistingFiles" class="mb-3"></div>
                        </div>

                        <!-- New file inputs -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted" style="font-size:.75rem;">Add new files (Max 3 total)</span>
                        </div>
                        <div class="row g-2" id="pksfRevisionAttachmentContainer">
                            <div class="col-12">
                                <input type="file" name="attachments[]" class="form-control mb-2">
                            </div>
                        </div>
                        <button type="button" id="btnPksfAddMoreRevisionFile" class="btn btn-sm btn-outline-secondary rounded-pill mt-1" style="display:none;">
                            <i class="bi bi-plus-circle me-1"></i>Add Another File
                        </button>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 p-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning px-4 rounded-pill shadow-sm" {{ !$mySupervisorEmpId ? 'disabled' : '' }}>
                        <i class="bi bi-send-fill me-2"></i>Send to Supervisor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- PKSF Review Closure Modal (Supervisor) -->
<div class="modal fade" id="pksfReviewClosureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-success text-white p-4">
                <h5 class="modal-title fw-bold">Review Closure Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="pksfReviewClosureForm" method="POST" action="">
                @csrf
                <div class="modal-body p-4">
                    <input type="hidden" name="acm_id" id="pksf_review_closure_acm_id">
                    
                    <div class="alert alert-success border-0 shadow-none smaller mb-4">
                        <i class="bi bi-shield-check me-2"></i>You are reviewing the closure request submitted by your officer. 
                    </div>

                    <div class="mb-3">
                        <label for="pksf_review_closure_remarks" class="form-label fw-bold">Supervisor Remarks</label>
                        <textarea class="form-control" id="pksf_review_closure_remarks" name="remarks" rows="4" placeholder="Enter remarks for approving closure or rejecting back to officer..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <div class="d-flex gap-2">
                        <button type="button" id="btnPksfRejectClosure" class="btn btn-outline-danger px-4 rounded-pill">
                            <i class="bi bi-arrow-left-circle me-1"></i>Send Back to Officer
                        </button>
                        <button type="button" id="btnPksfApproveClosure" class="btn btn-success px-4 rounded-pill">
                            <i class="bi bi-check-circle me-1"></i>Approve & Close
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- PKSF Review Revision Modal (Supervisor) -->
<div class="modal fade" id="pksfReviewRevisionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:620px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-warning text-dark p-4">
                <h5 class="modal-title fw-bold">Review Revision Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="pksfReviewRevisionForm" method="POST" action="">
                @csrf
                <div class="modal-body p-4">
                    <input type="hidden" name="acm_id" id="pksf_review_revision_acm_id">
                    
                    <div class="alert alert-warning border-0 shadow-none smaller mb-4">
                        <i class="bi bi-shield-exclamation me-2"></i>You are reviewing the request to ask the PO for a revised response.
                    </div>

                    <div class="mb-3">
                        <label for="pksf_review_revision_remarks" class="form-label fw-bold">Supervisor Remarks</label>
                        <textarea class="form-control" id="pksf_review_revision_remarks" name="remarks" rows="4" placeholder="Enter remarks for forwarding this request to PO or sending it back to your officer..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3 d-flex justify-content-end gap-2 flex-nowrap">
                    <button type="button" class="btn btn-light px-3 rounded-pill text-nowrap" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="btnPksfRejectRevision" class="btn btn-outline-danger px-3 rounded-pill text-nowrap">
                        <i class="bi bi-arrow-left-circle me-1"></i>Send Back to Officer
                    </button>
                    <button type="button" id="btnPksfApproveRevision" class="btn btn-warning px-3 rounded-pill text-nowrap">
                        <i class="bi bi-send-check me-1"></i>Approve & Forward to PO
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .text-gradient {
        background: linear-gradient(45deg, #1a237e, #0d47a1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .bg-soft-primary { background-color: rgba(13, 71, 161, 0.1); }
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); }
    .bg-soft-warning { background-color: rgba(255, 193, 7, 0.1); }
    .bg-soft-secondary { background-color: rgba(108, 117, 125, 0.1); }
    .btn-soft-success {
        background: linear-gradient(45deg, #2e7d32, #43a047);
        color: white !important;
        border: none;
        box-shadow: 0 4px 10px rgba(46, 125, 50, 0.2);
        cursor: not-allowed;
    }
    .btn-soft-success:hover {
        color: white !important;
        opacity: 0.9;
    }
    .btn-soft-secondary {
        background-color: #f8f9fa;
        border-color: #e9ecef;
        color: #6c757d;
        cursor: not-allowed;
    }
    .cursor-not-allowed { cursor: not-allowed !important; }

    /* ── Matrix tabs ── */
    #matrixTabs .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        border-radius: 0;
        color: #6c757d;
        font-size: 0.875rem;
        padding-bottom: 0.75rem;
        transition: color 0.15s, border-color 0.15s;
    }
    #matrixTabs .nav-link:hover { color: #1b3a3a; }
    #matrixTabs .nav-link.active {
        color: #1b3a3a;
        border-bottom-color: #1b3a3a;
        background: transparent;
    }

    .search-wrapper { position: relative; }
    .search-wrapper i {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: #adb5bd;
    }

    .form-control:focus {
        box-shadow: 0 0 0 4px rgba(13, 71, 161, 0.1);
        border-color: #0d47a1;
    }

    .table thead th {
        font-size: 0.85rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #1a237e;
        background-color: rgba(13, 71, 161, 0.04);
        padding: 1.25rem 1rem;
    }

    .table tbody td { padding: 1.25rem 1rem; }

    .incoming-mini {
        align-items: flex-start;
        background: linear-gradient(135deg, rgba(27, 58, 58, 0.08), rgba(13, 110, 253, 0.06));
        border: 1px solid rgba(27, 58, 58, 0.14);
        border-radius: 12px;
        display: grid;
        gap: 9px;
        grid-template-columns: minmax(0, 1fr);
        min-width: 220px;
        padding: 9px 10px;
    }

    .incoming-mini-copy {
        min-width: 0;
        text-align: left;
    }

    .incoming-mini-copy small {
        color: #617171;
        display: block;
        font-size: 0.68rem;
        line-height: 1.25;
    }

    .incoming-mini-copy strong {
        color: #173434;
        display: block;
        font-size: 0.8rem;
        line-height: 1.25;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .btn-white { background: #fff; border: 1px solid #f0f0f0; }
    .btn-white:hover { background: #f8f9fa; }

    /* ── PO Comment Modal — upload zone states ── */
    #cmUploadZone:hover:not(.cm-zone-full),
    #cmUploadZone.cm-drag-over {
        border-color: #1b3a3a !important;
        background: #f0f4f4 !important;
    }
    #cmUploadZone.cm-zone-full { cursor: default; background: #f0f9ff !important; border-color: #90cdf4 !important; }

    /* ── Meta chips (JS-generated in modal header) ── */
    .po-cm-meta-chip {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        border: 1px solid #dee2e6;
        background: #f8f9fa;
        color: #495057;
    }
    /* PO code */
    .po-cm-chip-po       { background: #2d6a6a; border-color: #2d6a6a; color: #fff; }
    /* Category */
    .po-cm-chip-category { background: #5b21b6; border-color: #5b21b6; color: #fff; }
    /* Priority */
    .po-cm-chip-high     { background: #dc2626; border-color: #dc2626; color: #fff; }
    .po-cm-chip-medium   { background: #d97706; border-color: #d97706; color: #fff; }
    .po-cm-chip-low      { background: #059669; border-color: #059669; color: #fff; }
    /* Date */
    .po-cm-chip-date     { background: #0369a1; border-color: #0369a1; color: #fff; }

    /* ── File pills (JS-generated) ── */
    .po-cm-file-pill {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 8px 12px;
        margin-bottom: 6px;
    }
    .po-cm-file-pill:last-child { margin-bottom: 0; }
    .po-cm-file-pill-icon { color: #6c757d; font-size: 1rem; flex-shrink: 0; }
    .po-cm-file-pill-name { flex: 1; min-width: 0; font-size: 0.84rem; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .po-cm-file-pill-size { font-size: 0.72rem; color: #adb5bd; flex-shrink: 0; white-space: nowrap; }
    .po-cm-file-pill-remove {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 50%;
        color: #adb5bd;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        flex-shrink: 0;
        padding: 0;
        transition: background 0.15s, color 0.15s;
    }
    .po-cm-file-pill-remove:hover { background: #f8d7da; border-color: #f5c2c7; color: #dc3545; }
</style>

@push('scripts')
<script>
    $(document).ready(function() {
        /* ── Auth context (passed from PHP) ──────────────────────────── */
        const AUTH = {
            empId:          '{{ auth()->user()->emp_id }}',
            isPksf:         {{ auth()->user()->isPksf() ? 'true' : 'false' }},
            isPo:           {{ auth()->user()->isPo()   ? 'true' : 'false' }},
            isPoSupervisor: {{ auth()->user()->hasAnyRole(['PO_SUPERVISOR']) ? 'true' : 'false' }},
        };

        /* ── String-escape helpers ────────────────────────────────────── */
        function escHtml(str) {
            const d = document.createElement('div');
            d.appendChild(document.createTextNode(str || ''));
            return d.innerHTML;
        }
        function escAttr(str) {
            return (str || '')
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

        /* ── Cell renderers ───────────────────────────────────────────── */
        function renderPriority(priority) {
            const map = { HIGH: 'danger', MEDIUM: 'warning text-dark', LOW: 'info text-dark' };
            const cls = map[priority] || 'secondary';
            return `<span class="badge bg-${cls} rounded-pill px-3">${escHtml(priority || '—')}</span>`;
        }

        function renderStatus(status) {
            const map = {
                SAVED:               ['secondary',         'Draft'],
                SUBMITTED:           ['primary',           'Submitted'],
                REJECTED:            ['danger',            'Returned'],
                PO_REVIEW:           ['info text-dark',    'PO Review'],
                PO_SUBMITTED:        ['warning text-dark', 'PO Submitted'],
                PO_APPROVED:         ['success',           'PO Approved'],
                PO_REJECTED:         ['danger',            'PO Returned'],
                PO_SO_REVIEW:        ['info text-dark',    'PO Supervisor Review'],
                WAITING_FOR_CLOSURE: ['primary',           'Awaiting Closure'],
                REVISION_REQUESTED:  ['warning text-dark', 'Revision Requested'],
                PKSF_REJECTED:       ['danger',            'Returned by PKSF'],
                CLOSED:              ['success',           'Closed'],
            };
            const [cls, label] = map[status] || ['secondary', status];
            return `<span class="badge bg-${cls} rounded-pill px-3">${escHtml(label)}</span>`;
        }

        /* ── Action-button builder ────────────────────────────────────── */
        function renderActions(row) {
            const myDesk   = row._current_desk_emp_id === AUTH.empId;
            const myMatrix = row._created_by          === AUTH.empId;
            const s        = row.status;
            const id       = escAttr(row.acm_id);
            const pc       = escAttr(row.po_code);
            const cat      = escAttr(row.observation_category);
            const pri      = escAttr(row.priority);
            const vd       = escAttr(row.visiting_date);
            const obs      = escAttr(row._pksf_observation  || '');
            const dir      = escAttr(row._direction_to_po   || '');
            const pksfatts = encodeURIComponent(JSON.stringify(row._pksf_attachments || []));

            let btns = '';

            /* ── PKSF-side buttons ── */
            if (AUTH.isPksf) {
                // Edit draft / returned matrix (SAVED or REJECTED only — not PKSF_REJECTED)
                if (myMatrix && myDesk && ['SAVED','REJECTED'].includes(s)) {
                    btns += `<a href="/action-matrix/${id}/edit"
                                class="btn btn-sm btn-outline-secondary rounded-pill me-1"
                                title="Edit"><i class="bi bi-pencil me-1"></i>Edit</a>`;
                }
                // Forward to PKSF supervisor
                if (myMatrix && myDesk && ['SAVED', 'REJECTED'].includes(s)) {
                    btns += `<button class="btn btn-sm btn-outline-primary rounded-pill me-1 btn-forward-matrix"
                                     data-acmid="${id}" title="Forward to Supervisor">
                                <i class="bi bi-send me-1"></i>Forward</button>`;
                }
                // PKSF Supervisor: review submitted matrix
                if (myDesk && s === 'SUBMITTED') {
                    btns += `<button class="btn btn-sm btn-success rounded-pill me-1 btn-review-matrix"
                                     data-acmid="${id}" data-pocode="${pc}" title="Review">
                                <i class="bi bi-check-circle me-1"></i>Review</button>`;
                }
                // PKSF CO: PO responded — offer Closure or Revision
                if (myMatrix && myDesk && s === 'PO_APPROVED') {
                    btns += `<button class="btn btn-sm btn-outline-success rounded-pill me-1 btn-pksf-request-closure"
                                     data-acmid="${id}" title="Request Closure">
                                <i class="bi bi-check-circle-fill me-1"></i>Closure</button>
                             <button class="btn btn-sm btn-outline-warning rounded-pill me-1 btn-pksf-request-revision"
                                     data-acmid="${id}" data-status="${s}" title="Request Revision">
                                <i class="bi bi-arrow-repeat me-1"></i>Revision</button>`;
                }
                // PKSF CO: supervisor rejected closure/revision — re-request with updated comment
                if (myMatrix && myDesk && s === 'PKSF_REJECTED') {
                    btns += `<button class="btn btn-sm btn-outline-success rounded-pill me-1 btn-pksf-request-closure"
                                     data-acmid="${id}" title="Request Closure">
                                <i class="bi bi-check-circle-fill me-1"></i>Closure</button>
                             <button class="btn btn-sm btn-outline-warning rounded-pill me-1 btn-pksf-request-revision"
                                     data-acmid="${id}" data-status="${s}" title="Request Revision">
                                <i class="bi bi-arrow-repeat me-1"></i>Revision</button>`;
                }
                // PKSF Supervisor: approve/reject closure request
                if (myDesk && s === 'WAITING_FOR_CLOSURE') {
                    btns += `<button class="btn btn-sm btn-success rounded-pill me-1 btn-pksf-review-closure"
                                     data-acmid="${id}" title="Review Closure">
                                <i class="bi bi-check-circle me-1"></i>Review Closure</button>`;
                }
                // PKSF Supervisor: approve/reject revision request
                if (myDesk && s === 'REVISION_REQUESTED') {
                    btns += `<button class="btn btn-sm btn-warning rounded-pill me-1 btn-pksf-review-revision"
                                     data-acmid="${id}" title="Review Revision">
                                <i class="bi bi-arrow-repeat me-1"></i>Review Revision</button>`;
                }
            }

            /* ── PO-side buttons ── */
            if (AUTH.isPo) {
                // PO Supervisor: forward to PO CO when matrix first arrives
                if (AUTH.isPoSupervisor && myDesk && s === 'PO_SO_REVIEW') {
                    btns += `<button class="btn btn-sm btn-primary rounded-pill me-1 btn-po-forward-to-officer"
                                     data-acmid="${id}" data-pocode="${pc}" title="Forward to PO Officer">
                                <i class="bi bi-send me-1"></i>Forward to Officer</button>`;
                }
                // PO CO: comment + optional forward
                if (!AUTH.isPoSupervisor && myDesk && ['PO_REVIEW','PO_REJECTED'].includes(s)) {
                    btns += `<button class="btn btn-sm btn-outline-primary rounded-pill me-1 btn-comment-matrix"
                                     data-acmid="${id}" data-pocode="${pc}"
                                     data-category="${cat}" data-priority="${pri}"
                                     data-visitdate="${vd}" data-observation="${obs}"
                                     data-direction="${dir}" data-pksfatts="${pksfatts}"
                                     title="Write / Edit Response">
                                <i class="bi bi-chat-dots me-1"></i>Comment</button>`;
                    if (row._has_comments) {
                        btns += `<button class="btn btn-sm btn-success rounded-pill me-1 btn-po-forward"
                                         data-acmid="${id}" title="Forward to Supervisor">
                                    <i class="bi bi-send-fill me-1"></i>Forward</button>`;
                    }
                }
                // PO Supervisor: review officer's response
                if (AUTH.isPoSupervisor && myDesk && s === 'PO_SUBMITTED') {
                    btns += `<button class="btn btn-sm btn-warning rounded-pill me-1 btn-po-review"
                                     data-acmid="${id}" title="Review PO Response">
                                <i class="bi bi-shield-check me-1"></i>Review</button>`;
                }
            }

            /* ── Universal buttons ── */
            // History button commented out — the detail view (show page) covers this content
            // btns += `<button class="btn btn-sm btn-outline-secondary rounded-pill me-1 btn-view-discussion"
            //                  data-acmid="${id}" title="View History">
            //             <i class="bi bi-clock-history me-1"></i>History</button>`;

            btns += `<a href="/action-matrix/${id}"
                        class="btn btn-sm btn-outline-info rounded-pill"
                        title="View Detail"><i class="bi bi-eye me-1"></i>View</a>`;

            return `<div class="d-flex flex-wrap gap-1 justify-content-center">${btns}</div>`;
        }

        /* ── DataTable — server-side ──────────────────────────────────── */
        const table = $('#matrixTable').DataTable({
            serverSide: true,
            processing: true,
            dom: 'rt<"d-flex justify-content-between align-items-center p-4 border-top"lp>',
            pageLength: 25,
            language: {
                processing: '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading…</span></div></div>',
                emptyTable:  '<div class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2 opacity-40"></i>No action matrices found.</div>',
                zeroRecords: '<div class="text-center py-5 text-muted"><i class="bi bi-search fs-1 d-block mb-2 opacity-40"></i>No matching records found.</div>',
                paginate: {
                    previous: '<i class="bi bi-chevron-left"></i>',
                    next:     '<i class="bi bi-chevron-right"></i>'
                }
            },
            ajax: {
                url: '{{ route("action-matrix.data") }}',
                type: 'GET',
                data: function (d) {
                    const activeTab = $('#matrixTabs .nav-link.active').data('tab');
                    if (activeTab === 'action_required') {
                        d.view = 'action_required';
                    } else if (activeTab === 'ongoing') {
                        d.view = 'ongoing';
                    } else {
                        d.view = $('#filterView').val();
                    }
                    d.po_code  = $('#filterPo').val();
                    d.priority = $('#filterPriority').val();
                }
            },
            columns: [
                { data: 'acm_id',               orderable: true  },
                { data: 'po_code',              orderable: true,  className: 'text-center' },
                { data: 'visiting_date',        orderable: true  },
                { data: 'observation_category', orderable: true  },
                { data: 'priority',  orderable: true,  render: function(d)          { return renderPriority(d);  } },
                { data: 'status',    orderable: true,  render: function(d)          { return renderStatus(d);    } },
                { data: 'incoming_html', orderable: false, render: function(d)      { return d || '<span class="text-muted">—</span>'; } },
                { data: null,        orderable: false, render: function(d, t, row)  { return renderActions(row); } },
            ],
            order: [[0, 'desc']],
            drawCallback: function () {
                const activeTab   = $('#matrixTabs .nav-link.active').data('tab');
                const isActionTab  = activeTab === 'action_required';
                const isOngoingTab = activeTab === 'ongoing';
                const isAllTab     = !isActionTab && !isOngoingTab;

                const anyFilter = isAllTab && (
                                   $('#filterView').val()     !== 'all'
                               || ($('#filterPo').length && $('#filterPo').val() !== '')
                               || $('#filterPriority').val() !== '');
                $('#btnClearFilters').toggle(anyFilter);

                const isCompleted = isAllTab && $('#filterView').val() === 'completed';
                // Show "Incoming From" only on Action Required tab
                table.column(6).visible(isActionTab && !isCompleted, false);
            }
        });

        /* ── Filter dropdown handlers ─────────────────────────────────── */
        $('#filterView, #filterPo, #filterPriority').on('change', function () {
            table.ajax.reload();
        });

        $('#btnClearFilters').on('click', function () {
            $('#filterView').val('all');
            if ($('#filterPo').length) $('#filterPo').val('');
            $('#filterPriority').val('');
            $(this).hide();
            table.ajax.reload();
        });

        /* ── Tab switching ────────────────────────────────────────────── */
        $('#matrixTabs .nav-link').on('click', function () {
            $('#matrixTabs .nav-link').removeClass('active');
            $(this).addClass('active');

            const tab = $(this).data('tab');
            const isActionTab  = tab === 'action_required';
            const isOngoingTab = tab === 'ongoing';
            // Hide View dropdown on Action Required and Ongoing tabs (tab controls the view)
            $('#filterView').toggle(!isActionTab && !isOngoingTab);
            // Reset other filters on tab switch
            if ($('#filterPo').length) $('#filterPo').val('');
            $('#filterPriority').val('');
            $('#btnClearFilters').hide();

            table.ajax.reload();
        });

        // Manual Modal Control: Populate data FIRST, then show modal
        $('#matrixTable').on('click', '.btn-forward-matrix', function(e) {
            e.preventDefault();
            const acmId = $(this).attr('data-acmid');
            
            console.log('Manual click detected. ACM ID:', acmId);
            
            if (acmId) {
                // 1. Populate the UI
                $('#displayAcmId').text('#' + acmId);
                $('#acm_id_hidden').val(acmId);
                
                // 2. Show the modal programmatically
                const modalEl = document.getElementById('forwardModal');
                const myModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                myModal.show();
            } else {
                console.error('No ACM ID found on button');
            }
        });
        // Supervisor Review Modal Control
        $('#matrixTable').on('click', '.btn-review-matrix', function(e) {
            e.preventDefault();
            const acmId = $(this).attr('data-acmid');
            const poCode = $(this).attr('data-pocode');
            
            if (acmId) {
                $('#reviewAcmId').text('#' + acmId);
                $('#review_acm_id_hidden').val(acmId);
                
                // Auto-find PO Officer based on poCode
                const officerDisplay = $('#po_officer_name_display');
                const officerInput = $('#to_emp_id_hidden');
                
                let foundOfficer = null;
                $('#poOfficersData span').each(function() {
                    if ($(this).attr('data-pocode') == poCode) {
                        foundOfficer = {
                            id: $(this).attr('data-emp-id'),
                            name: $(this).attr('data-name')
                        };
                        return false; // break loop
                    }
                });

                if (foundOfficer) {
                    officerDisplay.text(foundOfficer.name);
                    officerInput.val(foundOfficer.id);
                    $('#btnApprove').prop('disabled', false).removeClass('opacity-50');
                } else {
                    officerDisplay.text('No PO Concern Officer (PO_CO) found for this PO!');
                    officerDisplay.addClass('text-danger');
                    officerInput.val('');
                    $('#btnApprove').prop('disabled', true).addClass('opacity-50');
                }
                
                const modalEl = document.getElementById('reviewModal');
                const myModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                myModal.show();
            }
        });

        // Handle Approval
        $('#btnApprove').on('click', function() {
            const form = $('#reviewForm');
            const poOfficer = $('#to_emp_id_hidden').val();
            
            if (!poOfficer) {
                alert('Cannot approve: No PO Concern Officer found for this PO.');
                return;
            }
            
            form.attr('action', "{{ route('action-matrix.approve') }}");
            form.submit();
        });

        // Handle Send Back (Reject)
        $('#btnSendBack').on('click', function() {
            const form = $('#reviewForm');
            form.attr('action', "{{ route('action-matrix.reject') }}");
            form.submit();
        });

        // ── PO Comment Modal ─────────────────────────────────────────────
        let cmDt = new DataTransfer();       // new files chosen this session
        let cmExistingAttachments = [];      // [{file_id, file_name, file_size, file_type}] from server draft
        let cmRemovedFileIds = [];           // file_ids of server files the user wants to delete

        // ── Helpers ──────────────────────────────────────────────────────
        function cmFormatBytes(bytes) {
            if (!bytes || bytes < 1024) return (bytes || 0) + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }
        function cmEscape(text) { return $('<span>').text(text).html(); }

        // Total kept-existing + new files
        function cmTotalCount() {
            const kept = cmExistingAttachments.filter(a => !cmRemovedFileIds.includes(a.file_id)).length;
            return kept + cmDt.files.length;
        }

        // Render all file pills (existing + new)
        function cmRenderPills() {
            const $list = $('#cmFileList');
            $list.empty();
            let hasAny = false;

            // Existing files from saved draft
            cmExistingAttachments.forEach(function (att) {
                if (cmRemovedFileIds.includes(att.file_id)) return;
                hasAny = true;
                $list.append(`
                    <div class="po-cm-file-pill">
                        <span class="po-cm-file-pill-icon"><i class="bi bi-paperclip text-success"></i></span>
                        <span class="po-cm-file-pill-name" title="${cmEscape(att.file_name)}">${cmEscape(att.file_name)}</span>
                        <span class="po-cm-file-pill-size">${cmFormatBytes(att.file_size)}</span>
                        <button type="button" class="po-cm-file-pill-remove po-cm-existing-remove" data-fileid="${att.file_id}" title="Remove">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>`);
            });

            // Newly chosen files
            for (let i = 0; i < cmDt.files.length; i++) {
                const f = cmDt.files[i];
                hasAny = true;
                $list.append(`
                    <div class="po-cm-file-pill">
                        <span class="po-cm-file-pill-icon"><i class="bi bi-file-earmark-text"></i></span>
                        <span class="po-cm-file-pill-name" title="${cmEscape(f.name)}">${cmEscape(f.name)}</span>
                        <span class="po-cm-file-pill-size">${cmFormatBytes(f.size)}</span>
                        <button type="button" class="po-cm-file-pill-remove" data-idx="${i}" title="Remove">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>`);
            }

            $list.toggle(hasAny);
        }

        function cmSyncInput() {
            document.getElementById('cmActualFileInput').files = cmDt.files;
        }

        function cmUpdateZone() {
            const n = cmTotalCount();
            $('#cmFileCountBadge').text(n + ' / 3');
            if (n === 0) {
                $('#cmUploadZone').removeClass('cm-zone-full');
                $('#cmUploadPrompt').show();
                $('#cmAddMoreWrap').hide();
            } else if (n < 3) {
                $('#cmUploadZone').removeClass('cm-zone-full');
                $('#cmUploadPrompt').hide();
                $('#cmAddMoreWrap').show();
            } else {
                $('#cmUploadZone').addClass('cm-zone-full');
                $('#cmUploadPrompt').hide();
                $('#cmAddMoreWrap').hide();
            }
        }

        function cmPickFile() {
            if (cmTotalCount() >= 3) return;
            const tmp = document.createElement('input');
            tmp.type = 'file';
            tmp.accept = '.pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.txt';
            tmp.onchange = function () {
                const file = this.files[0];
                if (!file) return;
                if (cmTotalCount() >= 3) { alert('Maximum 3 files allowed.'); return; }
                cmDt.items.add(file);
                cmSyncInput();
                cmRenderPills();
                cmUpdateZone();
            };
            tmp.click();
        }

        function cmReset() {
            $('#commentForm')[0].reset();
            cmDt = new DataTransfer();
            cmExistingAttachments = [];
            cmRemovedFileIds = [];
            cmSyncInput();
            cmRenderPills();
            cmUpdateZone();
            $('#cm_char_count').text('0');
            $('#comment_detail').removeClass('is-invalid');
            $('#cm_forward_flag').val('0');
            $('#comment_sl').val('');
            $('.po-cm-kicker').html('<i class="bi bi-building me-1"></i>PO Official Response');
            $('#cm_pksf_attachments').html('');
            $('.cm-pksf-att-section').hide();
        }

        // Pre-populate modal with an existing draft from the server
        function cmLoadDraft(draft) {
            $('#comment_sl').val(draft.sl);
            const detail = draft.comment_detail || '';   // guard against null/undefined
            $('#comment_detail').val(detail);
            $('#cm_char_count').text(detail.length);
            cmExistingAttachments = draft.attachments || [];
            cmRenderPills();
            cmUpdateZone();
            // Signal to user that they are editing a saved draft
            $('.po-cm-kicker').html('<i class="bi bi-pencil-square me-1"></i>Editing Your Saved Draft');
        }

        // ── Open modal ───────────────────────────────────────────────────
        $('#matrixTable').on('click', '.btn-comment-matrix', function () {
            cmReset();

            const acmId    = $(this).data('acmid');
            const poCode   = $(this).data('pocode')   || '—';
            const category = $(this).data('category') || '—';
            const priority = $(this).data('priority') || '';
            const visitDate= $(this).data('visitdate')|| '—';
            const obs        = $(this).data('observation') || 'No observation text available.';
            const dir        = $(this).data('direction')   || 'No direction provided.';
            const pksfAtts   = JSON.parse(decodeURIComponent($(this).data('pksfatts') || '%5B%5D'));

            $('#comment_acm_id').val(acmId);
            $('#cm_observation_text').text(obs);
            $('#cm_direction_text').text(dir);

            // Render PKSF attachments
            const $attWrap = $('#cm_pksf_attachments');
            if (pksfAtts.length) {
                $attWrap.closest('.cm-pksf-att-section').show();
                $attWrap.html(pksfAtts.map(f => {
                    const url  = '/storage/' + f.path;
                    const size = f.size ? ' (' + (f.size / 1024).toFixed(0) + ' KB)' : '';
                    return `<a href="${url}" target="_blank" class="d-inline-flex align-items-center gap-1 text-decoration-none me-2 mb-1 px-2 py-1 border rounded-2 bg-white small">
                                <i class="bi bi-file-earmark-arrow-down text-primary"></i>
                                <span class="text-dark">${cmEscape(f.name || 'File')}</span>
                                <span class="text-muted" style="font-size:0.7rem;">${size}</span>
                            </a>`;
                }).join(''));
            } else {
                $attWrap.closest('.cm-pksf-att-section').hide();
            }

            const pColors = { HIGH: 'po-cm-chip-high', MEDIUM: 'po-cm-chip-medium', LOW: 'po-cm-chip-low' };
            const pClass  = pColors[priority] || '';
            $('#cm_meta_row').html(`
                <span class="po-cm-meta-chip fw-bold" id="cm_acm_id_display" style="background:#1b3a3a;color:#fff;border-color:#1b3a3a;">${cmEscape(acmId)}</span>
                <span class="po-cm-meta-chip po-cm-chip-po"><i class="bi bi-building me-1"></i>${cmEscape(poCode)}</span>
                <span class="po-cm-meta-chip po-cm-chip-category"><i class="bi bi-tag me-1"></i>${cmEscape(category)}</span>
                ${priority ? `<span class="po-cm-meta-chip ${pClass}"><i class="bi bi-flag me-1"></i>${cmEscape(priority)} Priority</span>` : ''}
                <span class="po-cm-meta-chip po-cm-chip-date"><i class="bi bi-calendar3 me-1"></i>${cmEscape(visitDate)}</span>
            `);

            // Fetch existing draft from server, then show modal
            $.ajax({
                url: '{{ url("action-matrix") }}/' + encodeURIComponent(acmId) + '/my-draft',
                method: 'GET',
                dataType: 'json'   // explicit — prevents "null" string being treated as truthy
            })
            .done(function (draft) {
                try {
                    if (draft && typeof draft === 'object') cmLoadDraft(draft);
                } catch (err) {
                    console.error('[CommentModal] cmLoadDraft error:', err);
                }
            })
            .always(function () {
                // Always show the modal, even if the draft fetch or load failed
                bootstrap.Modal.getOrCreateInstance(document.getElementById('commentModal')).show();
            });
        });

        // ── File interactions ─────────────────────────────────────────────
        $('#cmUploadZone').on('click', function (e) {
            if ($(e.target).closest('.po-cm-file-pill-remove').length) return;
            cmPickFile();
        });

        $('#cmAddMoreBtn').on('click', function (e) {
            e.stopPropagation();
            cmPickFile();
        });

        // Remove a NEW file pill (DataTransfer index)
        $(document).on('click', '.po-cm-file-pill-remove:not(.po-cm-existing-remove)', function (e) {
            e.stopPropagation();
            const idx = parseInt($(this).data('idx'));
            cmDt.items.remove(idx);
            cmSyncInput();
            cmRenderPills();
            cmUpdateZone();
        });

        // Remove an EXISTING (server-side) file pill
        $(document).on('click', '.po-cm-existing-remove', function (e) {
            e.stopPropagation();
            const fileId = parseInt($(this).data('fileid'));
            if (!cmRemovedFileIds.includes(fileId)) {
                cmRemovedFileIds.push(fileId);
            }
            cmRenderPills();
            cmUpdateZone();
        });

        // Drag-and-drop
        $('#cmUploadZone').on('dragover', function (e) {
            e.preventDefault();
            if (cmTotalCount() < 3) $(this).addClass('cm-drag-over');
        });
        $('#cmUploadZone').on('dragleave drop', function () { $(this).removeClass('cm-drag-over'); });
        $('#cmUploadZone').on('drop', function (e) {
            e.preventDefault();
            $(this).removeClass('cm-drag-over');
            for (const file of e.originalEvent.dataTransfer.files) {
                if (cmTotalCount() >= 3) break;
                cmDt.items.add(file);
            }
            cmSyncInput();
            cmRenderPills();
            cmUpdateZone();
        });

        // Character counter
        $('#comment_detail').on('input', function () {
            $('#cm_char_count').text($(this).val().length);
            $(this).removeClass('is-invalid');
        });

        // Footer buttons — set the forward flag before submit
        $('#btnSaveForward').on('click', function () { $('#cm_forward_flag').val('1'); });
        $('#btnSaveDraft').on('click',   function () { $('#cm_forward_flag').val('0'); });

        // Form submission — validate + inject remove_file_ids hidden inputs
        $('#commentForm').on('submit', function (e) {
            const comment = $('#comment_detail').val().trim();
            if (!comment) {
                e.preventDefault();
                $('#comment_detail').addClass('is-invalid').focus();
                return false;
            }
            // Inject remove_file_ids as hidden inputs so they reach the controller
            $('.cm-remove-hidden').remove();
            cmRemovedFileIds.forEach(function (fileId) {
                $('<input>').attr({
                    type: 'hidden', name: 'remove_file_ids[]',
                    value: fileId, class: 'cm-remove-hidden'
                }).appendTo('#commentForm');
            });
        });
        // ── End PO Comment Modal ──────────────────────────────────────────

        // PO Forward Confirmation Logic
        $('#matrixTable').on('click', '.btn-po-forward', function() {
            const acmId = $(this).attr('data-acmid');
            $('#po_forward_acm_id').val(acmId);
            $('#po_forward_remarks').val('');
            
            const modal = new bootstrap.Modal(document.getElementById('poForwardModal'));
            modal.show();
        });

        // PO Supervisor: Forward to PO Officer Modal
        $('#matrixTable').on('click', '.btn-po-forward-to-officer', function() {
            const acmId  = $(this).attr('data-acmid');
            const poCode = $(this).attr('data-pocode');

            $('#po_forward_officer_acm_id').val(acmId);
            $('#po_forward_officer_remarks').val('');

            // Look up PO CO by po_code
            let foundCO = null;
            $('#poCOData span').each(function() {
                if ($(this).attr('data-pocode') === poCode) {
                    foundCO = { name: $(this).attr('data-name') };
                    return false;
                }
            });

            if (foundCO) {
                $('#poCONameDisplay').text(foundCO.name);
                $('#poCOInitial').replaceWith(
                    `<span id="poCOInitial" class="fw-bold">${foundCO.name.charAt(0).toUpperCase()}</span>`
                );
                $('#poCOInfoCard').show();
                $('#poCONotFoundAlert').hide();
                $('#btnForwardToOfficer').prop('disabled', false);
            } else {
                $('#poCONameDisplay').text('No officer found');
                $('#poCOInfoCard').show();
                $('#poCONotFoundAlert').show();
                $('#btnForwardToOfficer').prop('disabled', true);
            }

            const modal = new bootstrap.Modal(document.getElementById('poForwardToOfficerModal'));
            modal.show();
        });

        // PO Supervisor Review Modal
        $('#matrixTable').on('click', '.btn-po-review', function() {
            const acmId = $(this).attr('data-acmid');
            $('#po_review_acm_id').val(acmId);
            const modal = new bootstrap.Modal(document.getElementById('poReviewModal'));
            modal.show();
        });

        $('#btnPoApprove').on('click', function() {
            const form = $('#poReviewForm');
            form.attr('action', "{{ route('action-matrix.po-approve') }}");
            form.submit();
        });

        $('#btnPoReject').on('click', function() {
            const form = $('#poReviewForm');
            form.attr('action', "{{ route('action-matrix.po-reject') }}");
            form.submit();
        });

        // PKSF Concern Officer Request Closure Modal
        $('#matrixTable').on('click', '.btn-pksf-request-closure', function() {
            const acmId = $(this).attr('data-acmid');
            $('#pksf_closure_acm_id').val(acmId);
            $('#pksf_closure_remarks').val('');
            
            const modal = new bootstrap.Modal(document.getElementById('pksfRequestClosureModal'));
            modal.show();
        });

        // PKSF Concern Officer Request Revision Modal
        let pksfRevRemovedIds = []; // tracks existing file_ids the user wants to delete
        let pksfFileCount     = 1;  // number of new-file input rows currently in the form

        // Show "Add Another File" only when ≥1 file is present AND cap not reached
        function pksfRevSyncAddMoreBtn() {
            const existingPills = $('#pksfRevExistingFiles .pksf-rev-existing-pill').length;
            let filledInputs = 0;
            $('#pksfRevisionAttachmentContainer input[type="file"]').each(function() {
                if (this.files && this.files.length > 0) filledInputs++;
            });
            const hasFile = (existingPills + filledInputs) >= 1;
            $('#btnPksfAddMoreRevisionFile').toggle(hasFile && pksfFileCount < 3);
        }

        $('#matrixTable').on('click', '.btn-pksf-request-revision', function() {
            const acmId  = $(this).attr('data-acmid');
            const status = $(this).data('status') || 'PO_APPROVED';

            // Reset all fields
            $('#pksf_revision_acm_id').val(acmId);
            $('#pksf_revision_prev_sl').val('');
            $('#pksf_revision_detail').val('');
            pksfRevRemovedIds = [];

            // Reset existing-files section
            $('#pksfRevExistingFiles').empty();
            $('#pksfRevExistingFilesSection').hide();

            // Reset new file inputs
            $('#pksfRevisionAttachmentContainer').html('<div class="col-12"><input type="file" name="attachments[]" class="form-control mb-2"></div>');
            pksfFileCount = 1;
            pksfRevSyncAddMoreBtn(); // evaluates to hidden (no files present yet)

            // Hide supervisor context until populated
            $('#pksfRevSupContext').hide();
            $('#pksfRevSupRemarkText').text('');
            $('#pksfRevSupName').text("Supervisor's Feedback");

            const modal = new bootstrap.Modal(document.getElementById('pksfRequestRevisionModal'));
            modal.show();

            // PKSF_REJECTED: pre-fill comment + show supervisor feedback + render existing files
            if (status === 'PKSF_REJECTED') {
                $.getJSON(`/action-matrix/${encodeURIComponent(acmId)}/pksf-comment`)
                    .done(function (data) {
                        // Pre-fill textarea with the existing PKSF CO comment
                        if (data.comment_detail) {
                            $('#pksf_revision_detail').val(data.comment_detail);
                        }

                        // Store the previous comment's sl so the backend can delete removed files
                        if (data.comment_sl) {
                            $('#pksf_revision_prev_sl').val(data.comment_sl);
                        }

                        // Show supervisor's rejection remark for context
                        if (data.supervisor_remark) {
                            const nameLabel = data.supervisor_name
                                ? `Supervisor's Feedback (${escHtml(data.supervisor_name)})`
                                : "Supervisor's Feedback";
                            $('#pksfRevSupName').text(nameLabel);
                            $('#pksfRevSupRemarkText').text(data.supervisor_remark);
                            $('#pksfRevSupContext').show();
                        }

                        // Render previously attached files as removable pills
                        if (data.attachments && data.attachments.length > 0) {
                            let html = '';
                            data.attachments.forEach(function(f) {
                                html += `<div class="d-flex align-items-center gap-2 mb-2 p-2 pksf-rev-existing-pill"
                                              style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;" data-fileid="${f.file_id}">
                                            <i class="bi bi-paperclip" style="color:#4a5568;font-size:1rem;flex:0 0 auto;"></i>
                                            <span style="flex:1;min-width:0;font-size:.83rem;font-weight:600;color:#2d3748;
                                                         overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                                                  title="${escAttr(f.file_name)}">${escHtml(f.file_name)}</span>
                                            <button type="button" class="pksf-rev-existing-remove"
                                                    style="background:#f7f8fa;border:1px solid #e2e8f0;border-radius:50%;
                                                           color:#a0aec0;cursor:pointer;width:24px;height:24px;
                                                           display:flex;align-items:center;justify-content:center;
                                                           padding:0;flex:0 0 auto;line-height:1;"
                                                    data-fileid="${f.file_id}" title="Remove file">
                                                <i class="bi bi-x" style="font-size:.8rem;"></i>
                                            </button>
                                        </div>`;
                            });
                            $('#pksfRevExistingFiles').html(html);
                            $('#pksfRevExistingFilesSection').show();
                            pksfRevSyncAddMoreBtn(); // files present → show the add-more button
                        }
                    })
                    .fail(function () {
                        // Non-critical — modal still works without pre-fill
                        console.warn('[RevisionModal] Could not load existing comment for PKSF_REJECTED.');
                    });
            }
        });

        // Remove an existing attached file pill (mark for deletion on submit)
        $(document).on('click', '.pksf-rev-existing-remove', function() {
            const fileId = parseInt($(this).data('fileid'));
            if (!pksfRevRemovedIds.includes(fileId)) {
                pksfRevRemovedIds.push(fileId);
            }
            $(this).closest('.pksf-rev-existing-pill').fadeOut(150, function() {
                $(this).remove();
                if ($('#pksfRevExistingFiles .pksf-rev-existing-pill').length === 0) {
                    $('#pksfRevExistingFilesSection').hide();
                }
                pksfRevSyncAddMoreBtn(); // re-evaluate after pill removed
            });
        });

        // Inject remove_file_ids[] hidden inputs just before the form submits
        $('#pksfRequestRevisionForm').on('submit', function() {
            $(this).find('.pksf-rev-remove-hidden').remove(); // clean stale injections
            pksfRevRemovedIds.forEach(function(fileId) {
                $('<input>').attr({
                    type:  'hidden',
                    name:  'remove_file_ids[]',
                    value: fileId,
                    class: 'pksf-rev-remove-hidden'
                }).appendTo('#pksfRequestRevisionForm');
            });
        });

        // Re-evaluate whenever a file is chosen or cleared in any new-file input
        $(document).on('change', '#pksfRevisionAttachmentContainer input[type="file"]', function() {
            pksfRevSyncAddMoreBtn();
        });

        $(document).on('click', '#btnPksfAddMoreRevisionFile', function() {
            if (pksfFileCount < 3) {
                $('#pksfRevisionAttachmentContainer').append('<div class="col-12"><input type="file" name="attachments[]" class="form-control mb-2"></div>');
                pksfFileCount++;
                pksfRevSyncAddMoreBtn();
            }
        });

        // PKSF Supervisor Review Closure Modal
        $('#matrixTable').on('click', '.btn-pksf-review-closure', function() {
            const acmId = $(this).attr('data-acmid');
            $('#pksf_review_closure_acm_id').val(acmId);
            $('#pksf_review_closure_remarks').val('');
            
            const modal = new bootstrap.Modal(document.getElementById('pksfReviewClosureModal'));
            modal.show();
        });

        $('#btnPksfApproveClosure').on('click', function() {
            const form = $('#pksfReviewClosureForm');
            form.attr('action', "{{ route('action-matrix.approve-closure') }}");
            form.submit();
        });

        $('#btnPksfRejectClosure').on('click', function() {
            const form = $('#pksfReviewClosureForm');
            form.attr('action', "{{ route('action-matrix.reject-closure') }}");
            form.submit();
        });

        // PKSF Supervisor Review Revision Modal
        $('#matrixTable').on('click', '.btn-pksf-review-revision', function() {
            const acmId = $(this).attr('data-acmid');
            $('#pksf_review_revision_acm_id').val(acmId);
            $('#pksf_review_revision_remarks').val('');
            
            const modal = new bootstrap.Modal(document.getElementById('pksfReviewRevisionModal'));
            modal.show();
        });

        $('#btnPksfApproveRevision').on('click', function() {
            const form = $('#pksfReviewRevisionForm');
            form.attr('action', "{{ route('action-matrix.approve-revision') }}");
            form.submit();
        });

        $('#btnPksfRejectRevision').on('click', function() {
            const form = $('#pksfReviewRevisionForm');
            form.attr('action', "{{ route('action-matrix.reject-revision') }}");
            form.submit();
        });

        // History / Discussion Fetching
        $('#matrixTable').on('click', '.btn-view-discussion', function() {
            const acmId = $(this).attr('data-acmid');
            const historyContent = $('#historyContent');
            
            historyContent.html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
            
            const modal = new bootstrap.Modal(document.getElementById('historyModal'));
            modal.show();

            $.get(`/action-matrix/${acmId}/history`, function(html) {
                historyContent.html(html);
            }).fail(function() {
                historyContent.html('<div class="alert alert-danger">Failed to load history.</div>');
            });
        });
    });
</script>
@endpush
@endsection
