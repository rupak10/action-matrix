<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold text-sl-primary mb-0">Assign Roles to Users</h2>
    </x-slot>



    <div class="sl-card">
        <div class="sl-card-header d-flex justify-content-end align-items-center bg-white border-bottom-0 pb-0">
            <div class="search-box-wrapper" style="width: 360px;">
                <div class="input-group input-group-navbar shadow-sm rounded-pill overflow-hidden border">
                    <span class="input-group-text bg-white border-0 ps-3">
                        <i class="bi bi-search text-sl-muted"></i>
                    </span>
                    <input type="text" id="userRoleSearch" class="form-control border-0 ps-2 py-2" placeholder="Search here" style="font-size: 0.9rem; outline: none !important; box-shadow: none !important;">
                </div>
            </div>
        </div>
        <div class="table-responsive p-3 pt-0">
            <table id="userRoleTable" class="table table-hover align-middle mb-0 w-100">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Employee</th>
                        <th>Emp ID</th>
                        <th>Department</th>
                        <th>Assigned Roles</th>
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
                                <div class="smaller fw-bold text-sl-primary">{{ $user->dept_name }}</div>
                                <div class="smaller text-sl-muted">{{ $user->designation }}</div>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @forelse($user->roles as $role)
                                        <span class="badge bg-sl-primary-soft text-sl-primary border border-sl-primary-soft" style="font-size: 0.7rem;">
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="text-sl-muted smaller italic">No roles assigned</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('user-roles.edit', $user->emp_id) }}" class="btn btn-sm btn-sl-primary px-3">
                                    <i class="bi bi-shield-check me-1"></i>Manage Roles
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = $('#userRoleTable').DataTable({
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
            $('#userRoleSearch').on('keyup', function() {
                table.search(this.value).draw();
            });
        });
    </script>

    <style>
        .fw-600 { font-weight: 600; }
        .smaller { font-size: 0.75rem; }
        .avatar-sm { width: 32px; height: 32px; font-size: 0.875rem; }
        .bg-sl-primary-soft { background-color: rgba(27, 58, 58, 0.08); }
        .border-sl-primary-soft { border-color: rgba(27, 58, 58, 0.2) !important; }
        .italic { font-style: italic; }

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
