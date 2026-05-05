<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <a href="{{ route('users.index') }}" class="btn btn-sm btn-light border me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="h4 fw-bold text-sl-primary mb-0">Edit User: {{ $user->name }}</h2>
        </div>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="sl-card">
                <div class="sl-card-header">
                    <h5 class="mb-0">Update Information</h5>
                </div>
                <div class="p-4">
                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-4">
                            <!-- Basic Info -->
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-bold">Full Name</label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label fw-bold">Email Address</label>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Organizational Info -->
                            <div class="col-md-4">
                                <label for="emp_id" class="form-label fw-bold">Employee ID</label>
                                <input type="text" name="emp_id" id="emp_id" class="form-control @error('emp_id') is-invalid @enderror" value="{{ old('emp_id', $user->emp_id) }}" required>
                                @error('emp_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="emp_type" class="form-label fw-bold">User Type</label>
                                <select name="emp_type" id="emp_type" class="form-select @error('emp_type') is-invalid @enderror" required>
                                    <option value="PKSF" {{ old('emp_type', $user->emp_type) == 'PKSF' ? 'selected' : '' }}>PKSF Staff</option>
                                    <option value="PO" {{ old('emp_type', $user->emp_type) == 'PO' ? 'selected' : '' }}>PO User</option>
                                </select>
                                @error('emp_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4" id="poCodeWrapper" style="display: none;">
                                <label for="po_code" class="form-label fw-bold">Partner Organization</label>
                                <select name="po_code" id="po_code" class="form-select @error('po_code') is-invalid @enderror">
                                    <option value="">Select PO</option>
                                    @foreach($pos as $code => $name)
                                        <option value="{{ $code }}" {{ old('po_code', $user->po_code) == $code ? 'selected' : '' }}>{{ $name }} ({{ $code }})</option>
                                    @endforeach
                                </select>
                                @error('po_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="designation" class="form-label fw-bold">Designation</label>
                                <input type="text" name="designation" id="designation" class="form-control @error('designation') is-invalid @enderror" value="{{ old('designation', $user->designation) }}" required>
                                @error('designation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="dept_id" class="form-label fw-bold">Department</label>
                                <select name="dept_id" id="dept_id" class="form-select sl-select2 @error('dept_id') is-invalid @enderror" required data-placeholder="Select Department">
                                    @foreach($departments as $id => $name)
                                        <option value="{{ $id }}" {{ old('dept_id', $user->dept_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('dept_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="unit_id" class="form-label fw-bold">Unit</label>
                                <select name="unit_id" id="unit_id" class="form-select sl-select2 @error('unit_id') is-invalid @enderror" required data-placeholder="Select Unit">
                                    @foreach($units as $id => $name)
                                        <option value="{{ $id }}" {{ old('unit_id', $user->unit_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('unit_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="supervisor_emp_id" class="form-label fw-bold">Supervisor (Optional)</label>
                                <select name="supervisor_emp_id" id="supervisor_emp_id" class="form-select sl-select2 @error('supervisor_emp_id') is-invalid @enderror" data-placeholder="Search and Select Supervisor">
                                    <option value="">No Supervisor (Top Level)</option>
                                    @foreach($supervisors as $sup)
                                        <option value="{{ $sup->emp_id }}" {{ old('supervisor_emp_id', $user->supervisor_emp_id) == $sup->emp_id ? 'selected' : '' }}>
                                            [{{ $sup->emp_id }}] {{ $sup->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supervisor_emp_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr class="my-4">

                            <!-- Security -->
                            <div class="col-12 mb-2">
                                <div class="p-3 bg-light rounded border border-warning-subtle">
                                    <h6 class="fw-bold mb-1"><i class="bi bi-shield-lock me-2"></i>Change Password</h6>
                                    <p class="text-sl-muted small mb-0">Leave these fields blank if you do not want to change the password.</p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label fw-bold">New Password</label>
                                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label fw-bold">Confirm New Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                            </div>

                            <div class="col-12 text-end pt-3">
                                <a href="{{ route('users.index') }}" class="btn btn-light border px-4 me-2">Cancel</a>
                                <button type="submit" class="btn btn-sl-primary px-4">Update User</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const empTypeSelect = document.getElementById('emp_type');
            const poCodeWrapper = document.getElementById('poCodeWrapper');
            const poCodeSelect = document.getElementById('po_code');

            function togglePoField() {
                if (empTypeSelect.value === 'PO') {
                    poCodeWrapper.style.display = 'block';
                    poCodeSelect.setAttribute('required', 'required');
                } else {
                    poCodeWrapper.style.display = 'none';
                    poCodeSelect.removeAttribute('required');
                }
            }

            // Initial check
            togglePoField();

            // Listen for changes
            empTypeSelect.addEventListener('change', togglePoField);
        });
    </script>
</x-app-layout>
