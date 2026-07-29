<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-bold text-sl-primary mb-0">User Management</h2>
            <a href="{{ route('users.create') }}" class="btn btn-sl-primary">
                <i class="bi bi-person-plus me-2"></i>Add New User
            </a>
        </div>
    </x-slot>



    <div class="sl-card">
        <div class="sl-card-header d-flex justify-content-end align-items-center bg-white border-bottom-0 pb-0">
            <div class="search-box-wrapper" style="width: 360px;">
                <div class="input-group input-group-navbar shadow-sm rounded-pill overflow-hidden border">
                    <span class="input-group-text bg-white border-0 ps-3">
                        <i class="bi bi-search text-sl-muted"></i>
                    </span>
                    <input type="text" id="userSearch" class="form-control border-0 ps-2 py-2" placeholder="Search here" style="font-size: 0.9rem; outline: none !important; box-shadow: none !important;">
                </div>
            </div>
        </div>
        <div class="table-responsive p-3 pt-0">
            <table id="userTable" class="table table-hover align-middle mb-0 w-100">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Employee Name</th>
                        <th>Emp ID</th>
                        <th>Type</th>
                        <th>Designation</th>
                        <th>Department</th>
                        <th>Unit</th>
                        <th>Supervisor</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3 bg-sl-primary-soft text-sl-primary rounded-circle d-flex align-items-center justify-content-center fw-bold">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-600 text-sl-primary">{{ $user->name }}</div>
                                        <div class="text-sl-muted smaller">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $user->emp_id }}</span></td>
                            <td>
                                @if($user->isPksf())
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">PKSF</span>
                                @else
                                    <span class="badge bg-warning text-dark border">PO</span>
                                @endif
                            </td>
                            <td><span class="smaller fw-bold text-sl-primary">{{ $user->designation }}</span></td>
                            <td><span class="smaller text-sl-muted">{{ $user->dept_name }}</span></td>
                            <td><span class="smaller text-sl-muted">{{ $user->unit_name }}</span></td>
                            <td>
                                @php $primarySup = $user->supervisors->where('is_primary', true)->first(); @endphp
                                @if($primarySup && $primarySup->supervisor)
                                    <span class="badge bg-sl-primary-soft text-sl-primary border">{{ $primarySup->supervisor->name }}</span>
                                @elseif($user->supervisors->count() > 0)
                                    <span class="badge bg-sl-primary-soft text-sl-primary border">{{ $user->supervisors->first()->supervisor->name ?? '-' }}</span>
                                @else
                                    <span class="text-muted fw-bold">-</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-light border-0 text-sl-primary" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form id="delete-form-{{ $user->id }}" action="{{ route('users.destroy', $user->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <button type="button"
                                            class="btn btn-sm btn-light border-0 text-danger btn-delete"
                                            title="Delete"
                                            data-form-id="delete-form-{{ $user->id }}"
                                            data-user-name="{{ $user->name }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Delete confirmation modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger-subtle" style="width:56px;height:56px;">
                            <i class="bi bi-trash3 text-danger fs-4"></i>
                        </span>
                    </div>
                    <h6 class="fw-bold mb-1">Delete User</h6>
                    <p class="text-muted small mb-0">Are you sure you want to delete <strong id="deleteUserName"></strong>? This action cannot be undone.</p>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex gap-2">
                    <button type="button" class="btn btn-light border flex-fill" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmDeleteBtn" class="btn btn-danger flex-fill">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = $('#userTable').DataTable({
                dom: '<"d-flex justify-content-between align-items-center mb-3"l>rt<"d-flex justify-content-end mt-3"p>',
                pageLength: 10,
                ordering: true,
                info: false,
                language: {
                    search: "",
                    lengthMenu: "_MENU_ entries per page",
                }
            });

            // Link custom search box
            $('#userSearch').on('keyup', function() {
                table.search(this.value).draw();
            });

            // Delete modal
            const deleteModal   = new bootstrap.Modal(document.getElementById('deleteModal'));
            const confirmBtn    = document.getElementById('confirmDeleteBtn');
            const deleteUserName = document.getElementById('deleteUserName');
            let pendingFormId   = null;

            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.btn-delete');
                if (!btn) return;
                pendingFormId = btn.dataset.formId;
                deleteUserName.textContent = btn.dataset.userName;
                deleteModal.show();
            });

            confirmBtn.addEventListener('click', function() {
                if (pendingFormId) {
                    document.getElementById(pendingFormId).submit();
                }
            });
        });
    </script>

    <style>
        .fw-600 { font-weight: 600; }
        .smaller { font-size: 0.75rem; }
        .avatar-sm { width: 32px; height: 32px; font-size: 0.875rem; }
        .bg-sl-primary-soft { background-color: rgba(27, 58, 58, 0.08); }
        
        /* Premium Search Box Enhancement */
        .search-box-wrapper .input-group {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-color: #e9eff1 !important;
        }
        .search-box-wrapper .input-group:focus-within {
            border-color: var(--sl-primary) !important;
            box-shadow: 0 4px 12px rgba(27, 58, 58, 0.12) !important;
            transform: translateY(-1px);
        }
        .search-box-wrapper .input-group-text {
            color: #748181;
            transition: color 0.3s ease;
        }
        .search-box-wrapper .input-group:focus-within .input-group-text {
            color: var(--sl-primary);
        }
        
        /* DataTable Custom Styling */
        .dt-search { display: none; }
        .dataTables_length select {
            padding: 0.25rem 2rem 0.25rem 0.75rem;
            border-radius: 6px;
            border: 1px solid var(--sl-border);
            font-size: 0.875rem;
        }
        .dataTables_info {
            font-size: 0.825rem;
            color: var(--sl-muted);
        }
        .pagination {
            margin-bottom: 0;
            gap: 4px;
        }
        .page-link {
            border-radius: 6px !important;
            padding: 0.4rem 0.75rem;
            font-size: 0.875rem;
            color: var(--sl-primary);
            border-color: var(--sl-border);
        }
        .page-item.active .page-link {
            background-color: var(--sl-primary);
            border-color: var(--sl-primary);
        }
        .bg-warning-soft { background-color: rgba(255, 193, 7, 0.08); }
    </style>
</x-app-layout>
