<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <a href="{{ route('users.index') }}" class="btn btn-sm btn-light border me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="h4 fw-bold text-sl-primary mb-0">Create New User</h2>
        </div>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="sl-card">
<div class="p-4">
                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf
                        
                        <div class="row g-4">
                            <!-- Basic Info -->
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-bold">Full Name</label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. John Doe" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label fw-bold">Email Address</label>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="e.g. john@company.com" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Organizational Info -->
                            <div class="col-md-4">
                                <label for="emp_id" class="form-label fw-bold">Employee ID</label>
                                <input type="text" name="emp_id" id="emp_id" class="form-control @error('emp_id') is-invalid @enderror" value="{{ old('emp_id') }}" placeholder="e.g. 105257" required>
                                @error('emp_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="emp_type" class="form-label fw-bold">User Type</label>
                                <select name="emp_type" id="emp_type" class="form-select @error('emp_type') is-invalid @enderror" required>
                                    <option value="PKSF" {{ old('emp_type') == 'PKSF' ? 'selected' : '' }}>PKSF Staff</option>
                                    <option value="PO" {{ old('emp_type') == 'PO' ? 'selected' : '' }}>PO User</option>
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
                                        <option value="{{ $code }}" {{ old('po_code') == $code ? 'selected' : '' }}>{{ $name }} ({{ $code }})</option>
                                    @endforeach
                                </select>
                                @error('po_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="designation" class="form-label fw-bold">Designation</label>
                                <input type="text" name="designation" id="designation" class="form-control @error('designation') is-invalid @enderror" value="{{ old('designation') }}" placeholder="e.g. Assistant Manager" required>
                                @error('designation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="dept_id" class="form-label fw-bold">Department</label>
                                <select name="dept_id" id="dept_id" class="form-select sl-select2 @error('dept_id') is-invalid @enderror" required data-placeholder="Select Department">
                                    <option value="">Select Department</option>
                                    @foreach($departments as $id => $name)
                                        <option value="{{ $id }}" {{ old('dept_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('dept_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="unit_id" class="form-label fw-bold">Unit</label>
                                <select name="unit_id" id="unit_id" class="form-select sl-select2 @error('unit_id') is-invalid @enderror" required data-placeholder="Select Unit">
                                    <option value="">Select Unit</option>
                                    @foreach($units as $id => $name)
                                        <option value="{{ $id }}" {{ old('unit_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('unit_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Supervisors (Optional)</label>
                                <select name="supervisor_emp_ids[]" id="supervisor_emp_ids" class="form-select sl-select2 @error('supervisor_emp_ids') is-invalid @enderror" multiple data-placeholder="Search and select one or more supervisors">
                                    @foreach($supervisors as $sup)
                                        <option value="{{ $sup->emp_id }}"
                                            {{ in_array($sup->emp_id, old('supervisor_emp_ids', [])) ? 'selected' : '' }}>
                                            [{{ $sup->emp_id }}] {{ $sup->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supervisor_emp_ids')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12" id="primarySupervisorWrapper" style="display:none;">
                                <label class="form-label fw-bold">Primary Supervisor</label>
                                <select name="primary_supervisor_emp_id" id="primary_supervisor_emp_id" class="form-select @error('primary_supervisor_emp_id') is-invalid @enderror">
                                    <option value="">— Select primary —</option>
                                </select>
                                <div class="form-text text-muted">The primary supervisor receives forwarded matrices by default.</div>
                                @error('primary_supervisor_emp_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Security -->
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-bold">Password</label>
                                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label fw-bold">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                            </div>

                            <div class="col-12 text-end pt-3">
                                <button type="reset" class="btn btn-light border px-4 me-2">Reset</button>
                                <button type="submit" class="btn btn-sl-primary px-4">Save User</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // PO code toggle
            const empTypeSelect = document.getElementById('emp_type');
            const poCodeWrapper = document.getElementById('poCodeWrapper');
            const poCodeSelect  = document.getElementById('po_code');

            function togglePoField() {
                if (empTypeSelect.value === 'PO') {
                    poCodeWrapper.style.display = 'block';
                    poCodeSelect.setAttribute('required', 'required');
                } else {
                    poCodeWrapper.style.display = 'none';
                    poCodeSelect.removeAttribute('required');
                    poCodeSelect.value = '';
                }
            }
            togglePoField();
            empTypeSelect.addEventListener('change', togglePoField);

            // Primary supervisor dropdown — populated from the multi-select choices
            const supMulti       = document.getElementById('supervisor_emp_ids');
            const primaryWrapper = document.getElementById('primarySupervisorWrapper');
            const primarySelect  = document.getElementById('primary_supervisor_emp_id');

            function syncPrimaryOptions() {
                const selected = Array.from(supMulti.selectedOptions);
                const currentPrimary = primarySelect.value;

                primarySelect.innerHTML = '<option value="">— Select primary —</option>';
                selected.forEach(opt => {
                    const o = document.createElement('option');
                    o.value = opt.value;
                    o.textContent = opt.textContent.trim();
                    if (opt.value === currentPrimary) o.selected = true;
                    primarySelect.appendChild(o);
                });

                primaryWrapper.style.display = selected.length > 0 ? 'block' : 'none';

                // Auto-select if only one supervisor chosen
                if (selected.length === 1) primarySelect.value = selected[0].value;
            }

            // Select2 fires a custom 'change' event
            $(supMulti).on('change', syncPrimaryOptions);
            syncPrimaryOptions();
        });
    </script>
</x-app-layout>
