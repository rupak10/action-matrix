<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <a href="{{ route('user-roles.index') }}" class="btn btn-sm btn-light border me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="h4 fw-bold text-sl-primary mb-0">Manage Roles for: {{ $user->name }}</h2>
        </div>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="row g-4">
                <!-- User Info Card -->
                <div class="col-md-4">
                    <div class="sl-card h-100">
                        <div class="p-4 text-center">
                            <div class="avatar-lg mx-auto mb-3 bg-sl-primary text-white rounded-circle d-flex align-items-center justify-content-center display-6 fw-bold">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <h5 class="mb-1 fw-bold text-sl-primary">{{ $user->name }}</h5>
                            <p class="text-sl-muted mb-3">{{ $user->email }}</p>
                            
                            <hr>
                            
                            <div class="text-start mt-4">
                                <div class="mb-3">
                                    <label class="smaller text-sl-muted d-block">Employee ID</label>
                                    <span class="fw-600">{{ $user->emp_id }}</span>
                                </div>
                                <div class="mb-3">
                                    <label class="smaller text-sl-muted d-block">Designation</label>
                                    <span class="fw-600">{{ $user->designation }}</span>
                                </div>
                                <div class="mb-3">
                                    <label class="smaller text-sl-muted d-block">Department</label>
                                    <span class="fw-600">{{ $user->dept_name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Roles Selection Card -->
                <div class="col-md-8">
                    <div class="sl-card h-100">
                        <div class="sl-card-header">
                            <h5 class="mb-0">Select Roles</h5>
                        </div>
                        <div class="p-4">
                            <form action="{{ route('user-roles.update', $user->emp_id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <p class="text-sl-muted small mb-4">Select the roles you wish to assign to this user. Unselecting a role will remove their access.</p>
                                
                                <div class="row g-3">
                                    @foreach($roles as $role)
                                        <div class="col-md-6">
                                            <div class="role-selection-item p-3 rounded border h-100 transition-all">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" 
                                                           id="role_{{ $role->id }}"
                                                           {{ $user->roles->contains($role->id) ? 'checked' : '' }}>
                                                    <label class="form-check-label ms-2 d-block cursor-pointer" for="role_{{ $role->id }}">
                                                        <span class="d-block fw-bold text-sl-primary">{{ $role->name }}</span>
                                                        <span class="d-block smaller text-sl-muted">{{ $role->role_group }}</span>
                                                        <span class="d-block smaller text-sl-muted mt-1 fst-italic">{{ Str::limit($role->description, 60) }}</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-5 text-end pt-3 border-top">
                                    <a href="{{ route('user-roles.index') }}" class="btn btn-light border px-4 me-2">Cancel</a>
                                    <button type="submit" class="btn btn-sl-primary px-5">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .fw-600 { font-weight: 600; }
        .smaller { font-size: 0.75rem; }
        .avatar-lg { width: 80px; height: 80px; }
        .cursor-pointer { cursor: pointer; }
        .role-selection-item:hover {
            border-color: var(--sl-primary) !important;
            background-color: rgba(27, 58, 58, 0.02);
        }
        .form-check-input:checked + .form-check-label {
            /* color: var(--sl-primary); */
        }
        .transition-all { transition: all 0.2s; }
    </style>
</x-app-layout>
