@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-lg-11">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="fw-bold mb-0">PO Assignments</h3>
                    <p class="text-muted small mb-0">Manage which POs each PKSF employee can oversee. MD has full access and does not need assignments.</p>
                </div>
            </div>

            {{-- ── Add Assignment Form ──────────────────────────────────────── --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom pt-3 pb-2 rounded-top-4">
                    <span class="fw-bold" style="font-size:.9rem"><i class="bi bi-plus-circle me-2 text-primary"></i>New Assignment</span>
                </div>
                <div class="card-body pt-3">
                    <form action="{{ route('admin.po-assignments.store') }}" method="POST">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold mb-1">PKSF Employee <span class="text-danger">*</span></label>
                                <select name="emp_id" class="form-select form-select-sm sl-select2" data-placeholder="Search employee…" required>
                                    <option value="">Select employee</option>
                                    @foreach($smUsers as $smUser)
                                    @php $roleNames = $smUser->roles->pluck('name')->join(', '); @endphp
                                    <option value="{{ $smUser->emp_id }}" {{ old('emp_id') == $smUser->emp_id ? 'selected' : '' }}>
                                        [{{ $smUser->emp_id }}] {{ $smUser->name }} — {{ $roleNames }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('emp_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold mb-1">Partner Organization (PO) <span class="text-danger">*</span></label>
                                <select name="po_code" class="form-select form-select-sm sl-select2" data-placeholder="Search PO…" required>
                                    <option value="">Select PO</option>
                                    @foreach($poList as $po)
                                    <option value="{{ $po->po_code }}" {{ old('po_code') == $po->po_code ? 'selected' : '' }}>
                                        [{{ $po->po_code }}] {{ $po->po_name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('po_code')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold mb-1">PO Role <span class="text-danger">*</span></label>
                                <select name="emp_role" class="form-select form-select-sm" required>
                                    <option value="">Select role</option>
                                    <option value="CO"  {{ old('emp_role') == 'CO'  ? 'selected' : '' }}>CO — Concern Officer</option>
                                    <option value="SO"  {{ old('emp_role') == 'SO'  ? 'selected' : '' }}>SO — Supervisor Officer</option>
                                    <option value="MGT" {{ old('emp_role') == 'MGT' ? 'selected' : '' }}>MGT — Management</option>
                                </select>
                                @error('emp_role')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-sm btn-primary w-100">
                                    <i class="bi bi-plus-lg me-1"></i>Assign
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ── Current Assignments Table ────────────────────────────────── --}}
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center pt-3 pb-2 rounded-top-4">
                    <span class="fw-bold" style="font-size:.9rem"><i class="bi bi-table me-2 text-primary"></i>Current Assignments</span>
                    <div style="width:280px">
                        <div class="input-group shadow-sm rounded-pill overflow-hidden border" style="border-color:#e9eff1!important">
                            <span class="input-group-text bg-white border-0 ps-3">
                                <i class="bi bi-search text-muted" style="font-size:.85rem"></i>
                            </span>
                            <input type="text" id="assignmentSearch" class="form-control border-0 ps-2 py-2"
                                   placeholder="Search…" style="font-size:.875rem;outline:none!important;box-shadow:none!important">
                        </div>
                    </div>
                </div>
                <div class="table-responsive p-3 pt-2">
                    <table id="assignmentTable" class="table table-hover align-middle mb-0 w-100" style="font-size:.875rem">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" style="width:90px">Emp ID</th>
                                <th>Name</th>
                                <th>System Role</th>
                                <th>PO Code</th>
                                <th>PO Name</th>
                                <th class="text-center">PO Role</th>
                                <th class="text-center" style="width:70px">Remove</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $assignment)
                            @php
                                $assignUser  = $assignment->user;
                                $assignPo    = $assignment->po;
                                $roleNames   = $assignUser?->roles->pluck('name') ?? collect();
                            @endphp
                            <tr>
                                <td class="text-center">
                                    <span class="fw-600 text-sl-primary small" style="font-family:monospace">{{ $assignment->emp_id }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold" style="font-size:.875rem">{{ $assignUser?->name ?? $assignment->emp_id }}</div>
                                    <div class="text-muted" style="font-size:.72rem">{{ $assignUser?->designation ?? '' }}</div>
                                </td>
                                <td>
                                    @foreach($roleNames as $rn)
                                    <span class="badge rounded-pill me-1" style="background:#3d2b6b;color:#fff;font-size:.68rem">{{ $rn }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <span style="font-family:monospace;font-size:.82rem;font-weight:600">{{ $assignment->po_code }}</span>
                                </td>
                                <td>
                                    <span style="font-size:.85rem">{{ $assignPo?->po_name ?? '—' }}</span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $roleColor = match($assignment->emp_role) {
                                            'CO'  => ['bg' => '#eff6ff', 'color' => '#1d4ed8', 'label' => 'CO'],
                                            'SO'  => ['bg' => '#faf5ff', 'color' => '#7c3aed', 'label' => 'SO'],
                                            'MGT' => ['bg' => '#f1f5f9', 'color' => '#475569', 'label' => 'MGT'],
                                            default => ['bg' => '#f1f5f9', 'color' => '#475569', 'label' => $assignment->emp_role],
                                        };
                                    @endphp
                                    <span class="badge rounded-pill"
                                          style="background:{{ $roleColor['bg'] }};color:{{ $roleColor['color'] }};font-size:.72rem;font-weight:700;padding:.3em .75em">
                                        {{ $roleColor['label'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('admin.po-assignments.destroy', $assignment->id) }}" method="POST"
                                          onsubmit="return confirm('Remove this assignment?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm"
                                                style="padding:.25rem .5rem;background:#fff1f2;color:#e11d48;border:none;border-radius:6px"
                                                title="Remove">
                                            <i class="bi bi-trash3" style="font-size:.8rem"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-4 d-block mb-2"></i>No assignments yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const table = $('#assignmentTable').DataTable({
            dom: '<"d-flex justify-content-between align-items-center mb-3"l>rt<"d-flex justify-content-end mt-3"p>',
            pageLength: 15,
            ordering: true,
            info: false,
            columnDefs: [
                { orderable: false, targets: [2, 6] }
            ],
            language: {
                search: '',
                lengthMenu: '_MENU_ entries per page',
            }
        });

        $('#assignmentSearch').on('keyup', function () {
            table.search(this.value).draw();
        });
    });
</script>
<style>
    .fw-600 { font-weight: 600; }
    .dt-search { display: none; }
    .dataTables_length select {
        padding: 0.25rem 2rem 0.25rem 0.75rem;
        border-radius: 6px;
        border: 1px solid #e9eff1;
        font-size: 0.875rem;
    }
    .pagination { margin-bottom: 0; gap: 4px; }
    .page-link {
        border-radius: 6px !important;
        padding: 0.4rem 0.75rem;
        font-size: 0.875rem;
        color: var(--sl-primary);
        border-color: #e9eff1;
    }
    .page-item.active .page-link {
        background-color: var(--sl-primary);
        border-color: var(--sl-primary);
    }
</style>
@endpush

@endsection
