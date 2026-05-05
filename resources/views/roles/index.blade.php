<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-bold text-sl-primary mb-0">Role Management</h2>
            <a href="{{ route('roles.create') }}" class="btn btn-sl-primary">
                <i class="bi bi-plus-lg me-2"></i>Create New Role
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
                    <input type="text" id="roleSearch" class="form-control border-0 ps-2 py-2" placeholder="Search here" style="font-size: 0.9rem; outline: none !important; box-shadow: none !important;">
                </div>
            </div>
        </div>
        <div class="table-responsive p-3 pt-0">
            <table id="roleTable" class="table table-hover align-middle mb-0 w-100">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Role Name</th>
                        <th>Role Group</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-600 text-sl-primary">{{ $role->name }}</span>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $role->role_group }}</span></td>
                            <td><span class="text-sl-muted small">{{ Str::limit($role->description, 50) }}</span></td>
                            <td>
                                @if($role->is_active)
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                @endif
                            </td>
                            <td><span class="smaller text-sl-muted">{{ $role->created_by }}</span></td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-light border-0 text-sl-primary" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this role?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border-0 text-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = $('#roleTable').DataTable({
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
            $('#roleSearch').on('keyup', function() {
                table.search(this.value).draw();
            });
        });
    </script>

    <style>
        .fw-600 { font-weight: 600; }
        .smaller { font-size: 0.75rem; }
        .bg-success-subtle { background-color: rgba(25, 135, 84, 0.1) !important; }
        .bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1) !important; }

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
    </style>
</x-app-layout>
