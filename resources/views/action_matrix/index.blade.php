@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="display-6 fw-bold text-gradient mb-1">Action Matrix List</h2>
            <p class="text-muted">Manage and track all observations and PO responses.</p>
        </div>
        <a href="{{ route('action-matrix.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="bi bi-plus-lg me-2"></i> Create New Matrix
        </a>
    </div>

    <!-- Stats Overview (Optional but looks premium) -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small opacity-75">Total Matrices</div>
                        <div class="h4 fw-bold mb-0">{{ $matrices->count() }}</div>
                    </div>
                    <i class="bi bi-layers h2 mb-0 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-warning">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-dark opacity-75">Pending Reviews</div>
                        <div class="h4 fw-bold mb-0 text-dark">{{ $matrices->where('status', 'DRAFT')->count() }}</div>
                    </div>
                    <i class="bi bi-clock-history h2 mb-0 opacity-50 text-dark"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden glass-card">
        <div class="card-body p-0">
            <!-- Custom Search Box -->
            <div class="p-4 bg-light border-bottom d-flex justify-content-end">
                <div class="search-wrapper">
                    <i class="bi bi-search"></i>
                    <input type="text" id="matrixSearch" class="form-control rounded-pill ps-5" placeholder="Search matrices..." style="width: 360px;">
                </div>
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
                            <th class="border-0 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="fw-medium">
                        @foreach($matrices as $matrix)
                        <tr>
                            <td class="fw-bold text-primary">{{ $matrix->acm_id }}</td>
                            <td><span class="badge bg-light text-dark border fw-bold">{{ $matrix->po_code }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($matrix->visiting_date)->format('d M, Y') }}</td>
                            <td>{{ $matrix->observation_category }}</td>
                            <td>
                                @php
                                    $prioClass = match($matrix->priority) {
                                        'HIGH' => 'danger',
                                        'MEDIUM' => 'warning',
                                        default => 'info'
                                    };
                                @endphp
                                <span class="badge rounded-pill bg-{{ $prioClass }} text-uppercase px-3" style="font-size: 0.7rem;">
                                    {{ $matrix->priority }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusClass = match($matrix->status) {
                                        'SUBMITTED' => 'primary',
                                        'APPROVED' => 'success',
                                        'REJECTED' => 'danger',
                                        'SAVED', 'DRAFT' => 'warning',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-soft-{{ $statusClass }} text-{{ $statusClass }} border border-{{ $statusClass }} px-3 rounded-pill">
                                    {{ $matrix->status }}
                                </span>
                            </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('action-matrix.show', $matrix->acm_id) }}" class="btn btn-outline-primary btn-sm px-3 fw-semibold rounded-pill">
                                                View
                                            </a>
                                            
                                            @if($matrix->current_desk_emp_id == auth()->user()->emp_id && $matrix->status === 'SUBMITTED')
                                                <button type="button" class="btn btn-primary btn-sm px-3 fw-semibold rounded-pill btn-review-matrix" 
                                                        data-acmid="{{ $matrix->acm_id }}">
                                                    Review
                                                </button>
                                            @elseif($matrix->status === 'SUBMITTED')
                                                <button type="button" class="btn btn-soft-success btn-sm px-3 fw-semibold rounded-pill cursor-not-allowed" disabled>
                                                    <i class="bi bi-check2-all me-1"></i> Forwarded
                                                </button>
                                            @elseif($matrix->status === 'APPROVED')
                                                <button type="button" class="btn btn-success btn-sm px-3 fw-semibold rounded-pill cursor-not-allowed" disabled>
                                                    <i class="bi bi-patch-check-fill me-1"></i> Approved
                                                </button>
                                            @elseif($matrix->status === 'REJECTED')
                                                <button type="button" class="btn btn-outline-success btn-sm px-3 fw-semibold rounded-pill btn-forward-matrix" data-acmid="{{ $matrix->acm_id }}">
                                                    Re-Forward
                                                </button>
                                            @else
                                                <a href="#" class="btn btn-outline-secondary btn-sm px-3 fw-semibold rounded-pill">
                                                    Edit
                                                </a>
                                                <button type="button" class="btn btn-outline-success btn-sm px-3 fw-semibold rounded-pill btn-forward-matrix" data-acmid="{{ $matrix->acm_id }}">
                                                    Forward
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Review Modal (Supervisor) -->
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

    .btn-white { background: #fff; border: 1px solid #f0f0f0; }
    .btn-white:hover { background: #f8f9fa; }
</style>

@push('scripts')
<script>
    $(document).ready(function() {
        const table = $('#matrixTable').DataTable({
            dom: 'rt<"d-flex justify-content-between align-items-center p-4 border-top"lp>',
            pageLength: 10,
            language: {
                paginate: {
                    previous: '<i class="bi bi-chevron-left"></i>',
                    next: '<i class="bi bi-chevron-right"></i>'
                }
            }
        });

        $('#matrixSearch').on('keyup', function() {
            table.search(this.value).draw();
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
            
            if (acmId) {
                $('#reviewAcmId').text('#' + acmId);
                $('#review_acm_id_hidden').val(acmId);
                
                const modalEl = document.getElementById('reviewModal');
                const myModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                myModal.show();
            }
        });

        // Handle Approval
        $('#btnApprove').on('click', function() {
            const form = $('#reviewForm');
            form.attr('action', "{{ route('action-matrix.approve') }}");
            form.submit();
        });

        // Handle Send Back (Reject)
        $('#btnSendBack').on('click', function() {
            const form = $('#reviewForm');
            form.attr('action', "{{ route('action-matrix.reject') }}");
            form.submit();
        });
    });
</script>
@endpush
@endsection
