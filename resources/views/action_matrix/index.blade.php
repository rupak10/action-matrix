@extends('layouts.app')

@section('content')
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
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="display-6 fw-bold text-gradient mb-1">Action Matrix List</h2>
            @if(auth()->user()->isPksf())
                <p class="text-muted">Manage and track all observations and PO responses.</p>
            @else
                <p class="text-muted">View observations assigned to you and submit your responses.</p>
            @endif
        </div>
        @if(auth()->user()->isPksf())
            <a href="{{ route('action-matrix.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="bi bi-plus-lg me-2"></i> Create New Matrix
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
            <!-- Filter Bar -->
            <div class="p-3 bg-light border-bottom d-flex flex-wrap align-items-center gap-2">
                {{-- Dropdown 1: View --}}
                <select id="filterView" class="form-select form-select-sm rounded-pill border-0 shadow-sm" style="width:auto; min-width:170px;">
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
                            <th class="border-0">PO Code</th>
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
                        <label class="form-label fw-bold text-primary">Assigned PO Officer</label>
                        <div class="p-3 bg-light-primary rounded-3 border border-primary-subtle d-flex align-items-center">
                            <div class="avatar-xs bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 32px; height: 32px;">
                                <i class="bi bi-person"></i>
                            </div>
                            <div>
                                <div id="po_officer_name_display" class="fw-bold text-dark">Searching for officer...</div>
                                <div class="smaller text-muted">Role: PO Concern Officer</div>
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
                            <i class="bi bi-info-circle me-1"></i>System automatically assigns this to the PO Concern Officer (PO_CO).
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

<!-- Comment Modal (PO Officer) — Redesigned -->
<div class="modal fade" id="commentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable po-comment-dialog">
        <div class="modal-content border-0 overflow-hidden po-comment-modal">

            <!-- Header -->
            <div class="po-cm-header p-3 border-0">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div class="flex-grow-1 min-w-0">
                        <span class="po-cm-kicker">
                            <i class="bi bi-building me-1"></i>PO Official Response
                        </span>
                        <h4 class="po-cm-title mt-1 mb-2" id="cm_acm_id_display">—</h4>
                        <div class="d-flex flex-wrap gap-2" id="cm_meta_row">
                            {{-- populated by JS --}}
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white mt-1 flex-shrink-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <form id="commentForm" method="POST" action="{{ route('action-matrix.comment') }}" enctype="multipart/form-data"
                  style="display:flex; flex-direction:column; flex:1 1 auto; min-height:0; overflow:hidden;">
                @csrf
                <input type="hidden" name="acm_id" id="comment_acm_id">
                <input type="hidden" name="forward_to_supervisor" id="cm_forward_flag" value="0">
                <input type="hidden" name="comment_sl" id="comment_sl" value="">

                <div class="modal-body p-0">

                    <!-- Context: PKSF Observation -->
                    <div class="po-cm-context px-3 py-2">
                        <div class="po-cm-context-label">
                            <i class="bi bi-file-earmark-text me-1"></i>Observation You Are Responding To
                        </div>
                        <div class="po-cm-obs-text" id="cm_observation_text">—</div>
                    </div>

                    <!-- Main Body -->
                    <div class="p-3">

                        <!-- Response Textarea -->
                        <div class="mb-3">
                            <label for="comment_detail" class="po-cm-label">
                                <i class="bi bi-pencil-square me-1"></i>Your Official Response
                            </label>
                            <textarea
                                name="comment_detail"
                                id="comment_detail"
                                rows="4"
                                class="form-control po-cm-textarea"
                                placeholder="Write your formal response to the observation above…"
                                required
                                maxlength="5000"></textarea>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="po-cm-field-hint">Be clear and specific. Your supervisor will review this before it reaches PKSF.</span>
                                <span class="po-cm-charcount"><span id="cm_char_count">0</span>&thinsp;/&thinsp;5000</span>
                            </div>
                        </div>

                        <!-- File Attachments -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="po-cm-label mb-0">
                                    <i class="bi bi-paperclip me-1"></i>Attachments
                                </label>
                                <span class="po-cm-file-count-badge" id="cmFileCountBadge">0 / 3</span>
                            </div>
                            <span class="po-cm-label-hint d-block mb-2">PDF, Word, Excel, Images &middot; Max 30 MB each</span>

                            <div class="po-cm-upload-zone" id="cmUploadZone">

                                <!-- Empty-state prompt -->
                                <div class="po-cm-upload-prompt" id="cmUploadPrompt">
                                    <div class="po-cm-upload-icon-wrap">
                                        <i class="bi bi-cloud-arrow-up-fill"></i>
                                    </div>
                                    <div>
                                        <span class="po-cm-upload-action">Click to browse</span>
                                        <span class="po-cm-upload-or"> or drag &amp; drop files here</span>
                                    </div>
                                    <span class="po-cm-upload-types">PDF &middot; Word &middot; Excel &middot; Images</span>
                                </div>

                                <!-- File Pills -->
                                <div id="cmFileList" style="display:none;"></div>

                                <!-- Add another button (1–2 files selected) -->
                                <div id="cmAddMoreWrap" class="mt-3 text-center" style="display:none;">
                                    <button type="button" class="po-cm-btn-add-file" id="cmAddMoreBtn">
                                        <i class="bi bi-plus-circle me-1"></i>Add another file
                                    </button>
                                </div>

                            </div>

                            <!-- Actual file input (hidden, driven by DataTransfer) -->
                            <input type="file" name="attachments[]" id="cmActualFileInput" multiple style="display:none;"
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.txt">
                        </div>

                        <!-- Routing Card -->
                        <div class="po-cm-routing-card" id="po_routing_card">
                            <div class="po-cm-routing-inner">
                                <div class="po-cm-supervisor-avatar">
                                    {{ auth()->user()->supervisor ? strtoupper(substr(auth()->user()->supervisor->name, 0, 1)) : '?' }}
                                </div>
                                <div class="po-cm-routing-info">
                                    <div class="po-cm-routing-name">
                                        {{ auth()->user()->supervisor ? auth()->user()->supervisor->name : 'No Supervisor Assigned' }}
                                    </div>
                                    <div class="po-cm-routing-role">PO Supervisor &middot; Will review your draft before it reaches PKSF</div>
                                </div>
                                @if(auth()->user()->supervisor_emp_id)
                                    <span class="po-cm-routing-status-badge po-cm-badge-assigned">
                                        <i class="bi bi-shield-check me-1"></i>Assigned
                                    </span>
                                @else
                                    <span class="po-cm-routing-status-badge po-cm-badge-warn">
                                        <i class="bi bi-exclamation-triangle me-1"></i>Not Set
                                    </span>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer po-cm-footer border-0 p-3">
                    <button type="button" class="btn po-cm-btn-cancel rounded-pill px-4" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <div class="d-flex gap-2 ms-auto">
                        <button type="submit" id="btnSaveDraft" class="btn po-cm-btn-draft rounded-pill px-4">
                            <i class="bi bi-floppy2 me-2"></i>Save Draft
                        </button>
                        <button type="submit" id="btnSaveForward" class="btn po-cm-btn-forward rounded-pill px-4"
                                {{ !auth()->user()->supervisor_emp_id ? 'disabled' : '' }}>
                            <i class="bi bi-send-fill me-2"></i>Submit to Supervisor
                        </button>
                    </div>
                </div>

            </form>
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
                            {{ auth()->user()->supervisor ? substr(auth()->user()->supervisor->name, 0, 1) : '?' }}
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">{{ auth()->user()->supervisor ? auth()->user()->supervisor->name : 'No Supervisor Assigned' }}</h6>
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
    <div class="modal-dialog modal-dialog-centered">
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
                <div class="modal-footer bg-light p-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <div class="d-flex gap-2">
                        <button type="button" id="btnPoReject" class="btn btn-outline-danger px-4 rounded-pill">
                            <i class="bi bi-x-circle me-1"></i>Send Back to Officer
                        </button>
                        <button type="button" id="btnPoApprove" class="btn btn-success px-4 rounded-pill">
                            <i class="bi bi-check-all me-1"></i>Approve & Send to PKSF
                        </button>
                    </div>
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
                                {{ auth()->user()->supervisor ? substr(auth()->user()->supervisor->name, 0, 1) : '?' }}
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-primary">{{ auth()->user()->supervisor ? auth()->user()->supervisor->name : 'No Supervisor Assigned' }}</h6>
                                <small class="text-muted">{{ auth()->user()->supervisor_emp_id ?? 'Please assign a supervisor in your profile' }}</small>
                            </div>
                        </div>
                        @if(!auth()->user()->supervisor_emp_id)
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
                    <button type="submit" class="btn btn-success px-4 rounded-pill shadow-sm" {{ !auth()->user()->supervisor_emp_id ? 'disabled' : '' }}>
                        <i class="bi bi-send me-2"></i>Send to Supervisor
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
                                {{ auth()->user()->supervisor ? substr(auth()->user()->supervisor->name, 0, 1) : '?' }}
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark">{{ auth()->user()->supervisor ? auth()->user()->supervisor->name : 'No Supervisor Assigned' }}</h6>
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
                    <button type="submit" class="btn btn-success px-4 rounded-pill shadow-sm" {{ !auth()->user()->supervisor_emp_id ? 'disabled' : '' }}>
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
                    
                    <div class="alert alert-warning border-0 shadow-none smaller mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>You are not satisfied with the PO response. Please detail what requires revision. Your request will be forwarded to your supervisor for review before going to the PO.
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-1">Forwarding to Supervisor</label>
                        <div class="d-flex align-items-center bg-light p-3 rounded-3 border">
                            <div class="avatar-xs bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 32px; height: 32px;">
                                {{ auth()->user()->supervisor ? substr(auth()->user()->supervisor->name, 0, 1) : '?' }}
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark">{{ auth()->user()->supervisor ? auth()->user()->supervisor->name : 'No Supervisor Assigned' }}</h6>
                                <small class="text-muted">PKSF Supervisor (Automatic Forward)</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="pksf_revision_detail" class="form-label fw-bold">Detail Comments for PO</label>
                        <textarea name="comment_detail" id="pksf_revision_detail" rows="5" class="form-control" placeholder="Specify clearly what is missing or needs improvement..." required></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Attachments (Max 3 files)</label>
                        <div class="row g-2" id="pksfRevisionAttachmentContainer">
                            <div class="col-12">
                                <input type="file" name="attachments[]" class="form-control mb-2">
                            </div>
                        </div>
                        <button type="button" id="btnPksfAddMoreRevisionFile" class="btn btn-sm btn-outline-secondary rounded-pill">
                            <i class="bi bi-plus-circle me-1"></i>Add Another File
                        </button>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 p-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning px-4 rounded-pill shadow-sm" {{ !auth()->user()->supervisor_emp_id ? 'disabled' : '' }}>
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
    <div class="modal-dialog modal-dialog-centered">
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
                <div class="modal-footer bg-light p-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <div class="d-flex gap-2">
                        <button type="button" id="btnPksfRejectRevision" class="btn btn-outline-danger px-4 rounded-pill">
                            <i class="bi bi-arrow-left-circle me-1"></i>Send Back to Officer
                        </button>
                        <button type="button" id="btnPksfApproveRevision" class="btn btn-warning px-4 rounded-pill">
                            <i class="bi bi-send-check me-1"></i>Approve & Forward to PO
                        </button>
                    </div>
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

    /* =====================================================
       PO Comment Modal — Scoped Styles
    ====================================================== */
    .po-comment-dialog { max-width: 580px; }
    .po-comment-modal {
        box-shadow: 0 24px 60px rgba(27, 58, 58, 0.18) !important;
        border-radius: 16px !important;
    }

    /* Header */
    .po-cm-header {
        background: linear-gradient(135deg, #1b3a3a 0%, #2e5454 100%);
    }
    .po-cm-kicker {
        font-size: 0.68rem;
        font-weight: 900;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: rgba(255,255,255,0.6);
        display: block;
    }
    .po-cm-title {
        font-size: 1.2rem;
        font-weight: 800;
        color: #fff;
        letter-spacing: -0.2px;
        line-height: 1.2;
    }
    .po-cm-meta-chip {
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 999px;
        color: rgba(255,255,255,0.88);
        font-size: 0.73rem;
        font-weight: 700;
        padding: 4px 10px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .po-cm-chip-high   { background: rgba(220,53,69,0.35)  !important; border-color: rgba(220,53,69,0.5) !important; }
    .po-cm-chip-medium { background: rgba(255,193,7,0.28)  !important; border-color: rgba(255,193,7,0.45) !important; color: #ffd85c !important; }
    .po-cm-chip-low    { background: rgba(13,202,240,0.22) !important; border-color: rgba(13,202,240,0.4) !important; }

    /* Context section */
    .po-cm-context {
        background: #f2f7f6;
        border-bottom: 1px solid #dce8e6;
    }
    .po-cm-context-label {
        font-size: 0.68rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #1b3a3a;
        opacity: 0.55;
        margin-bottom: 8px;
    }
    .po-cm-obs-text {
        font-size: 0.88rem;
        color: #2a3e3e;
        line-height: 1.65;
        border-left: 3px solid #1b3a3a;
        padding-left: 14px;
        max-height: 90px;
        overflow-y: auto;
        white-space: pre-line;
        scrollbar-width: thin;
    }
    .po-cm-obs-text::-webkit-scrollbar { width: 4px; }
    .po-cm-obs-text::-webkit-scrollbar-thumb { background: #b0c8c4; border-radius: 4px; }

    /* Labels */
    .po-cm-label {
        font-weight: 800;
        font-size: 0.85rem;
        color: #1c2929;
        display: flex;
        align-items: center;
        margin-bottom: 8px;
    }
    .po-cm-label-hint {
        font-size: 0.76rem;
        color: #7d9494;
    }

    /* Textarea */
    .po-cm-textarea {
        border: 1.5px solid #cfdede;
        border-radius: 10px;
        font-size: 0.92rem;
        line-height: 1.65;
        resize: vertical;
        color: #1c2929;
        transition: border-color 0.18s, box-shadow 0.18s;
    }
    .po-cm-textarea:focus {
        border-color: #1b3a3a;
        box-shadow: 0 0 0 3px rgba(27,58,58,0.1);
    }
    .po-cm-textarea.is-invalid { border-color: #dc3545 !important; }
    .po-cm-field-hint {
        font-size: 0.75rem;
        color: #7d9494;
        flex: 1;
    }
    .po-cm-charcount {
        font-size: 0.75rem;
        color: #9aadad;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
        margin-left: 10px;
    }

    /* File count badge */
    .po-cm-file-count-badge {
        font-size: 0.76rem;
        font-weight: 800;
        color: #1b3a3a;
        background: #e8f2f0;
        border: 1px solid #c4dbd8;
        border-radius: 999px;
        padding: 3px 10px;
    }

    /* Upload zone */
    .po-cm-upload-zone {
        border: 2px dashed #bdd2d0;
        border-radius: 12px;
        background: #f8fbfa;
        padding: 20px;
        cursor: pointer;
        transition: border-color 0.18s, background 0.18s;
        min-height: 72px;
    }
    .po-cm-upload-zone:hover:not(.cm-zone-full),
    .po-cm-upload-zone.cm-drag-over {
        border-color: #1b3a3a;
        background: rgba(27,58,58,0.04);
    }
    .po-cm-upload-zone.cm-zone-full {
        cursor: default;
        border-style: solid;
        border-color: #a8c8c4;
        background: #eef6f4;
    }

    /* Upload prompt */
    .po-cm-upload-prompt {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        text-align: center;
        padding: 6px 0;
        pointer-events: none;
    }
    .po-cm-upload-icon-wrap {
        width: 46px;
        height: 46px;
        background: rgba(27,58,58,0.09);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.45rem;
        color: #1b3a3a;
        margin-bottom: 2px;
    }
    .po-cm-upload-action {
        font-weight: 700;
        color: #1b3a3a;
        text-decoration: underline;
        text-underline-offset: 2px;
    }
    .po-cm-upload-or { color: #5a7474; }
    .po-cm-upload-types {
        font-size: 0.74rem;
        color: #8ea6a6;
    }

    /* File pills */
    .po-cm-file-pill {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #fff;
        border: 1px solid #d2e2e0;
        border-radius: 10px;
        padding: 10px 14px;
        margin-bottom: 8px;
        transition: border-color 0.15s;
    }
    .po-cm-file-pill:last-child { margin-bottom: 0; }
    .po-cm-file-pill-icon {
        color: #1b3a3a;
        font-size: 1.05rem;
        flex: 0 0 auto;
    }
    .po-cm-file-pill-name {
        flex: 1;
        min-width: 0;
        font-size: 0.84rem;
        font-weight: 600;
        color: #1c2929;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .po-cm-file-pill-size {
        font-size: 0.73rem;
        color: #7d9090;
        flex: 0 0 auto;
        white-space: nowrap;
    }
    .po-cm-file-pill-remove {
        background: #f0f5f5;
        border: 1px solid #d2e2e0;
        border-radius: 50%;
        color: #5a7070;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        flex: 0 0 auto;
        padding: 0;
        line-height: 1;
        transition: background 0.15s, color 0.15s, border-color 0.15s;
    }
    .po-cm-file-pill-remove:hover {
        background: #fde8e8;
        border-color: #f5c6c6;
        color: #c0392b;
    }

    /* Add another file button */
    .po-cm-btn-add-file {
        font-size: 0.8rem;
        font-weight: 600;
        color: #1b3a3a;
        background: transparent;
        border: 1.5px dashed #8ab4b0;
        border-radius: 999px;
        padding: 5px 16px;
        transition: all 0.15s;
        cursor: pointer;
    }
    .po-cm-btn-add-file:hover {
        background: rgba(27,58,58,0.06);
        border-color: #1b3a3a;
        border-style: solid;
        color: #1b3a3a;
    }

    /* Routing card */
    .po-cm-routing-card {
        border: 1.5px solid #d0e0de;
        border-radius: 12px;
        background: #f6faf9;
        padding: 16px;
    }
    .po-cm-routing-inner {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .po-cm-supervisor-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1b3a3a, #2e5454);
        color: #fff;
        font-size: 0.9rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        box-shadow: 0 3px 8px rgba(27,58,58,0.25);
    }
    .po-cm-routing-info {
        flex: 1;
        min-width: 0;
    }
    .po-cm-routing-name {
        font-weight: 700;
        font-size: 0.9rem;
        color: #1c2929;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .po-cm-routing-role {
        font-size: 0.75rem;
        color: #6d8484;
        margin-top: 2px;
    }
    .po-cm-routing-status-badge {
        flex: 0 0 auto;
        font-size: 0.73rem;
        font-weight: 700;
        border-radius: 999px;
        padding: 5px 11px;
        display: inline-flex;
        align-items: center;
    }
    .po-cm-badge-assigned {
        background: rgba(25,135,84,0.1);
        color: #19a065;
        border: 1px solid rgba(25,135,84,0.25);
    }
    .po-cm-badge-warn {
        background: rgba(220,53,69,0.08);
        color: #c0392b;
        border: 1px solid rgba(220,53,69,0.2);
    }

    /* Footer */
    .po-cm-footer {
        background: #f4f8f7;
        border-top: 1px solid #dce8e6 !important;
    }
    .po-cm-btn-cancel {
        color: #5a7070;
        background: #fff;
        border: 1px solid #cddcda;
        font-weight: 600;
    }
    .po-cm-btn-cancel:hover { background: #eef4f3; border-color: #b0cbc8; }

    .po-cm-btn-draft {
        color: #1b3a3a;
        background: #fff;
        border: 1.5px solid #1b3a3a;
        font-weight: 700;
    }
    .po-cm-btn-draft:hover { background: rgba(27,58,58,0.06); color: #1b3a3a; }

    .po-cm-btn-forward {
        background: linear-gradient(135deg, #1b3a3a 0%, #2e5454 100%);
        color: #fff;
        border: none;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(27,58,58,0.28);
        transition: opacity 0.15s, box-shadow 0.15s;
    }
    .po-cm-btn-forward:hover:not(:disabled) {
        opacity: 0.9;
        color: #fff;
        box-shadow: 0 6px 16px rgba(27,58,58,0.35);
    }
    .po-cm-btn-forward:disabled { opacity: 0.45; cursor: not-allowed; }
</style>

@push('scripts')
<script>
    $(document).ready(function() {
        /* ── Auth context (passed from PHP) ──────────────────────────── */
        const AUTH = {
            empId:  '{{ auth()->user()->emp_id }}',
            isPksf: {{ auth()->user()->isPksf() ? 'true' : 'false' }},
            isPo:   {{ auth()->user()->isPo()   ? 'true' : 'false' }},
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
            const obs      = escAttr(row._pksf_observation || '');

            let btns = '';

            /* ── PKSF-side buttons ── */
            if (AUTH.isPksf) {
                // Edit draft / returned matrix
                if (myMatrix && myDesk && ['SAVED','REJECTED','PKSF_REJECTED'].includes(s)) {
                    btns += `<a href="/action-matrix/${id}/edit"
                                class="btn btn-sm btn-outline-secondary rounded-pill me-1"
                                title="Edit"><i class="bi bi-pencil me-1"></i>Edit</a>`;
                }
                // Forward to PKSF supervisor
                if (myMatrix && myDesk && s === 'SAVED') {
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
                                     data-acmid="${id}" title="Request Revision">
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
                // PO CO: comment + optional forward
                if (myDesk && ['PO_REVIEW','PO_REJECTED'].includes(s)) {
                    btns += `<button class="btn btn-sm btn-outline-primary rounded-pill me-1 btn-comment-matrix"
                                     data-acmid="${id}" data-pocode="${pc}"
                                     data-category="${cat}" data-priority="${pri}"
                                     data-visitdate="${vd}" data-observation="${obs}"
                                     title="Write / Edit Response">
                                <i class="bi bi-chat-dots me-1"></i>Comment</button>`;
                    if (row._has_comments) {
                        btns += `<button class="btn btn-sm btn-success rounded-pill me-1 btn-po-forward"
                                         data-acmid="${id}" title="Forward to Supervisor">
                                    <i class="bi bi-send-fill me-1"></i>Forward</button>`;
                    }
                }
                // PO Supervisor: review officer's response
                if (myDesk && s === 'PO_SUBMITTED') {
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
                    d.view     = $('#filterView').val();
                    d.po_code  = $('#filterPo').val();
                    d.priority = $('#filterPriority').val();
                }
            },
            columns: [
                { data: 'acm_id',               orderable: true  },
                { data: 'po_code',              orderable: true  },
                { data: 'visiting_date',        orderable: true  },
                { data: 'observation_category', orderable: true  },
                { data: 'priority',  orderable: true,  render: function(d)          { return renderPriority(d);  } },
                { data: 'status',    orderable: true,  render: function(d)          { return renderStatus(d);    } },
                { data: 'incoming_html', orderable: false, render: function(d)      { return d || '<span class="text-muted">—</span>'; } },
                { data: null,        orderable: false, render: function(d, t, row)  { return renderActions(row); } },
            ],
            order: [[0, 'desc']],
            drawCallback: function () {
                const anyFilter = $('#filterView').val()     !== 'all'
                               || ($('#filterPo').length && $('#filterPo').val() !== '')
                               || $('#filterPriority').val() !== '';
                $('#btnClearFilters').toggle(anyFilter);
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
            // Reset kicker to default (new response mode)
            $('.po-cm-kicker').html('<i class="bi bi-building me-1"></i>PO Official Response');
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
            const obs      = $(this).data('observation') || 'No observation text available.';

            $('#comment_acm_id').val(acmId);
            $('#cm_acm_id_display').text(acmId);
            $('#cm_observation_text').text(obs);

            const pColors = { HIGH: 'po-cm-chip-high', MEDIUM: 'po-cm-chip-medium', LOW: 'po-cm-chip-low' };
            const pClass  = pColors[priority] || '';
            $('#cm_meta_row').html(`
                <span class="po-cm-meta-chip"><i class="bi bi-building"></i>${cmEscape(poCode)}</span>
                <span class="po-cm-meta-chip"><i class="bi bi-tag"></i>${cmEscape(category)}</span>
                ${priority ? `<span class="po-cm-meta-chip ${pClass}"><i class="bi bi-flag"></i>${cmEscape(priority)} Priority</span>` : ''}
                <span class="po-cm-meta-chip"><i class="bi bi-calendar3"></i>${cmEscape(visitDate)}</span>
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
        $('#matrixTable').on('click', '.btn-pksf-request-revision', function() {
            const acmId = $(this).attr('data-acmid');
            $('#pksf_revision_acm_id').val(acmId);
            $('#pksf_revision_detail').val('');
            
            // reset file inputs
            $('#pksfRevisionAttachmentContainer').html('<div class="col-12"><input type="file" name="attachments[]" class="form-control mb-2"></div>');
            pksfFileCount = 1;
            $('#btnPksfAddMoreRevisionFile').show();

            const modal = new bootstrap.Modal(document.getElementById('pksfRequestRevisionModal'));
            modal.show();
        });

        // PKSF Revision Add More File Logic
        let pksfFileCount = 1;
        $(document).on('click', '#btnPksfAddMoreRevisionFile', function() {
            if (pksfFileCount < 3) {
                $('#pksfRevisionAttachmentContainer').append('<div class="col-12"><input type="file" name="attachments[]" class="form-control mb-2"></div>');
                pksfFileCount++;
                if (pksfFileCount === 3) $(this).hide();
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
