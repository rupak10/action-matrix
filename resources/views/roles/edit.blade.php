<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <a href="{{ route('roles.index') }}" class="btn btn-sm btn-light border me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="h4 fw-bold text-sl-primary mb-0">Edit Role: {{ $role->name }}</h2>
        </div>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="sl-card">
                <div class="sl-card-header">
                    <h5 class="mb-0">Update Role Details</h5>
                </div>
                <div class="p-4">
                    <form action="{{ route('roles.update', $role->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-bold">Role Name</label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $role->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="role_group" class="form-label fw-bold">Role Group</label>
                                <input type="text" name="role_group" id="role_group" class="form-control @error('role_group') is-invalid @enderror" value="{{ old('role_group', $role->role_group) }}" required>
                                @error('role_group')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="description" class="form-label fw-bold">Description</label>
                                <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $role->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch p-3 bg-light rounded border">
                                    <input class="form-check-input ms-0 me-3" type="checkbox" name="is_active" id="is_active" value="1" {{ $role->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label fw-600" for="is_active">Active Status</label>
                                    <p class="text-sl-muted small mb-0 ms-0">Determine if this role is currently available for assignment.</p>
                                </div>
                            </div>

                            <div class="col-12 text-end pt-3">
                                <a href="{{ route('roles.index') }}" class="btn btn-light border px-4 me-2">Cancel</a>
                                <button type="submit" class="btn btn-sl-primary px-4">Update Role</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="mt-4 p-3 border rounded bg-white small text-sl-muted">
                <div class="d-flex justify-content-between">
                    <span><strong>Created By:</strong> {{ $role->created_by }} ({{ $role->created_at->format('d M Y, h:i A') }})</span>
                    @if($role->updated_by)
                        <span><strong>Last Updated:</strong> {{ $role->updated_by }} ({{ $role->updated_at->format('d M Y, h:i A') }})</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .fw-600 { font-weight: 600; }
    </style>
</x-app-layout>
