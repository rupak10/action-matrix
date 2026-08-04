@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="fw-bold mb-0">PO Assignments</h3>
                    <p class="text-muted small mb-0">Manage which POs each PKSF employee can oversee. MD has full access and does not need assignments.</p>
                </div>
            </div>


            {{-- Add Assignment Form --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body pt-3">
                    <form action="{{ route('admin.po-assignments.store') }}" method="POST">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label small fw-bold mb-1">PKSF Employee <span class="text-danger">*</span></label>
                                <select name="emp_id" class="form-select form-select-sm sl-select2" data-placeholder="Search employee..." required>
                                    <option value="">Select user</option>
                                    @foreach($smUsers as $smUser)
                                        @php
                                            $roleNames = $smUser->roles->pluck('name')->join(', ');
                                        @endphp
                                        @unless($smUser->isSmMd())
                                            <option value="{{ $smUser->emp_id }}" {{ old('emp_id') == $smUser->emp_id ? 'selected' : '' }}>
                                                [{{ $smUser->emp_id }}] {{ $smUser->name }} ({{ $roleNames }})
                                            </option>
                                        @endunless
                                    @endforeach
                                </select>
                                @error('emp_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-bold mb-1">Partner Organization (PO) <span class="text-danger">*</span></label>
                                <select name="po_code" class="form-select form-select-sm sl-select2" data-placeholder="Search PO..." required>
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
                                <button type="submit" class="btn btn-sm btn-primary w-100">
                                    <i class="bi bi-plus-lg me-1"></i> Assign
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Current Assignments --}}
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom-0 d-flex justify-content-end align-items-center pt-3 pb-0">
                    <div style="width:320px;">
                        <div class="input-group shadow-sm rounded-pill overflow-hidden border" style="border-color:#e9eff1!important;">
                            <span class="input-group-text bg-white border-0 ps-3">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" id="assignmentSearch" class="form-control border-0 ps-2 py-2"
                                   placeholder="Search here"
                                   style="font-size:0.9rem;outline:none!important;box-shadow:none!important;">
                        </div>
                    </div>
                </div>
                <div class="table-responsive p-3 pt-2">
                    <table id="assignmentTable" class="table table-hover align-middle mb-0 w-100">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center">Emp ID</th>
                                <th>Name</th>
                                <th>Role(s)</th>
                                <th>Assigned POs</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($smUsers as $smUser)
                                <tr>
                                    <td class="text-center">
                                        <span class="fw-600 text-sl-primary small">{{ $smUser->emp_id }}</span>
                                    </td>
                                    <td>{{ $smUser->name }}</td>
                                    <td>
                                        @foreach($smUser->roles as $role)
                                            <span class="badge rounded-pill me-1" style="background:#3d2b6b;color:#fff;font-size:.7rem;">{{ $role->name }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if($smUser->isSmMd())
                                            <span class="text-muted small">
                                                <i class="bi bi-check-circle-fill text-success me-1"></i>Full access to all POs
                                            </span>
                                        @elseif($smUser->poAssignments->isEmpty())
                                            <span class="text-muted small">—</span>
                                        @else
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($smUser->poAssignments as $assignment)
                                                    <form action="{{ route('admin.po-assignments.destroy', $assignment->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 px-2 py-1">
                                                            {{ $assignment->po_code }}
                                                            <button type="submit" class="btn-close ms-1" style="font-size:.55rem;" title="Remove"></button>
                                                        </span>
                                                    </form>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
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
            pageLength: 10,
            ordering: true,
            info: false,
            columnDefs: [
                { orderable: false, targets: [2, 3] }
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
