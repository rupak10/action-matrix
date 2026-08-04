@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@section('content')
<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    {{-- <h3 class="fw-bold text-gradient mb-0">Create Follow-up Action Matrix</h3> --}}
                    <h3 class="fw-bold text-gradient mb-0">New Follow-up Observation</h3>
                    <p class="text-muted small mb-0">Initiate a new observation record.</p>
                </div>
                <a href="{{ route('action-matrix.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden glass-card">
                <div class="card-body p-4">
                    <form action="{{ route('action-matrix.store') }}" method="POST" id="acmForm" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row g-3">
                            <!-- Row 1: PO | Visit Type | Visit From | Visit To -->
                            <div class="col-md-4">
                                <label class="form-label small fw-bold mb-1">Partner Organization (PO) <span class="text-danger">*</span></label>
                                <select name="po_code" class="form-select form-select-sm sl-select2" required>
                                    <option value="">Select PO</option>
                                    @foreach($poList as $po)
                                        <option value="{{ $po['code'] }}" {{ old('po_code') == $po['code'] ? 'selected' : '' }}>
                                            [{{ $po['code'] }}] {{ $po['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold mb-1">Visit Type <span class="text-danger">*</span></label>
                                <select name="visit_type" class="form-select form-select-sm" required>
                                    <option value="">Select Type</option>
                                    @foreach($visitTypes as $type)
                                        <option value="{{ $type }}" {{ old('visit_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                {{-- visiting_date: used as "Visit From" (ONSITE) or "Addressing Date From" (OFFSITE) --}}
                                <label id="label_date_from" class="form-label small fw-bold mb-1"><span id="label_date_from_text">Visit From</span> <span class="text-danger">*</span></label>
                                <div class="premium-date-field">
                                    <i class="bi bi-calendar3"></i>
                                    <input type="text" name="visiting_date" class="form-control form-control-sm js-premium-date" value="{{ old('visiting_date') }}" placeholder="From date" required>
                                </div>
                            </div>

                            <div class="col-md-3">
                                {{-- visiting_date_to: used as "Visit To" (ONSITE) or "Addressing Date To" (OFFSITE) --}}
                                <label id="label_date_to" class="form-label small fw-bold mb-1"><span id="label_date_to_text">Visit To</span> <span class="text-danger">*</span></label>
                                <div class="premium-date-field">
                                    <i class="bi bi-calendar3"></i>
                                    <input type="text" name="visiting_date_to" class="form-control form-control-sm js-premium-date" value="{{ old('visiting_date_to') }}" placeholder="To date" required>
                                </div>
                            </div>

                            <!-- Row 2: Category | Visit Category | Letter Issue | Letter Response -->
                            <div class="col-md-3">
                                <label class="form-label small fw-bold mb-1">Observation Category <span class="text-danger">*</span></label>
                                <select name="observation_category" class="form-select form-select-sm" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}" {{ old('observation_category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold mb-1">Visit Category <span class="text-danger">*</span></label>
                                <select name="visit_category" class="form-select form-select-sm" required>
                                    <option value="">Select Visit Category</option>
                                    @foreach($visitCategories as $vcat)
                                        <option value="{{ $vcat }}" {{ old('visit_category') == $vcat ? 'selected' : '' }}>{{ $vcat }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold mb-1">Letter Issue Date</label>
                                <div class="premium-date-field">
                                    <i class="bi bi-calendar3"></i>
                                    <input type="text" name="letter_issue_date" class="form-control form-control-sm js-premium-date" value="{{ old('letter_issue_date') }}" placeholder="Select issue date">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold mb-1">Letter Response Date (Expected)</label>
                                <div class="premium-date-field">
                                    <i class="bi bi-calendar3"></i>
                                    <input type="text" name="letter_response_date" class="form-control form-control-sm js-premium-date" value="{{ old('letter_response_date') }}" placeholder="Select response date">
                                </div>
                            </div>

                            <!-- Observation Areas -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold mb-1">PKSF Observation <span class="text-danger">*</span></label>
                                <textarea name="pksf_observation" class="form-control form-control-sm" rows="5" placeholder="Enter observation..." required>{{ old('pksf_observation') }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold mb-1">Direction to PO <span class="text-danger">*</span></label>
                                <textarea name="direction_to_po" class="form-control form-control-sm" rows="5" placeholder="Enter instructions..." required>{{ old('direction_to_po') }}</textarea>
                            </div>
                            
                            <!-- Attachments -->
                            <div class="col-12">
                                <label class="form-label small fw-bold mb-1">Attachments (Max 3 files)</label>
                                <input type="file" name="attachments[]" id="attachmentInput" class="form-control form-control-sm" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,image/*">
                                <div class="form-text small text-muted">Allowed types: PDF, Word, Excel, Text, Images. (Max 30MB per file)</div>
                                <div id="fileListContainer" class="mt-2 d-flex flex-column gap-1"></div>
                                @error('attachments') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                @error('attachments.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <!-- Configuration Row -->
                            <div class="col-md-4">
                                <label class="form-label small fw-bold mb-1">Action Matrix <span class="text-danger">*</span></label>
                                <select name="action_matrix" class="form-select form-select-sm" required>
                                    <option value="Y" {{ old('action_matrix', 'Y') == 'Y' ? 'selected' : '' }}>YES</option>
                                    <option value="N" {{ old('action_matrix') == 'N' ? 'selected' : '' }}>NO</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold mb-1">Priority <span class="text-danger">*</span></label>
                                <select name="priority" class="form-select form-select-sm" required>
                                    @foreach($priorities as $prio)
                                        <option value="{{ $prio }}" {{ old('priority', 'MEDIUM') == $prio ? 'selected' : '' }}>{{ $prio }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 d-flex align-items-end">
                                <div class="d-flex w-100 gap-2">
                                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1 py-2 shadow-sm">
                                        <i class="bi bi-save me-1"></i> Save Observation
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .text-gradient {
        background: linear-gradient(45deg, #1b3a3a, #2a5a5a);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .glass-card {
        background: #ffffff;
        border: 1px solid #e9eff1;
    }

    .form-label { color: #555; }

    .form-control-sm, .form-select-sm {
        border-radius: 8px;
        padding: 8px 12px;
        border: 1px solid #dee6e9;
    }

    .form-control-sm:focus, .form-select-sm:focus {
        box-shadow: 0 0 0 3px rgba(27, 58, 58, 0.05);
        border-color: #1b3a3a;
    }

    .premium-date-field {
        position: relative;
    }

    .premium-date-field i {
        color: #1b3a3a;
        font-size: 0.95rem;
        left: 12px;
        pointer-events: none;
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 2;
    }

    .premium-date-field .form-control-sm {
        background: linear-gradient(180deg, #ffffff 0%, #f9fbfb 100%);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.75);
        cursor: pointer;
        padding-left: 38px;
    }

    .premium-date-field .form-control-sm:hover {
        border-color: #b8caca;
        background: #ffffff;
    }

    .flatpickr-calendar {
        border: 1px solid #dfe8e8;
        border-radius: 14px;
        box-shadow: 0 18px 48px rgba(27, 58, 58, 0.14);
        font-family: 'Public Sans', sans-serif;
        overflow: hidden;
    }

    .flatpickr-months {
        background: #1b3a3a;
    }

    .flatpickr-current-month,
    .flatpickr-months .flatpickr-month,
    .flatpickr-months .flatpickr-prev-month,
    .flatpickr-months .flatpickr-next-month {
        color: #ffffff;
        fill: #ffffff;
    }

    .flatpickr-weekdays {
        background: #f4f7f7;
    }

    span.flatpickr-weekday {
        color: #5f7070;
        font-weight: 700;
    }

    .flatpickr-day {
        border-radius: 8px;
        color: #1c2929;
    }

    .flatpickr-day.today {
        border-color: #1b3a3a;
    }

    .flatpickr-day.selected,
    .flatpickr-day.startRange,
    .flatpickr-day.endRange,
    .flatpickr-day.selected:hover {
        background: #1b3a3a;
        border-color: #1b3a3a;
        color: #ffffff;
    }

    .btn-primary {
        background: #1b3a3a;
        border: none;
        border-radius: 8px;
    }

    .btn-primary:hover {
        background: #2a5a5a;
    }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr('.js-premium-date', {
            altInput: true,
            altFormat: 'd M, Y',
            dateFormat: 'Y-m-d',
            allowInput: true,
            disableMobile: true,
        });

        // Update date field labels based on visit type
        const visitTypeSelect  = document.querySelector('select[name="visit_type"]');
        const labelDateFromText = document.getElementById('label_date_from_text');
        const labelDateToText   = document.getElementById('label_date_to_text');

        function updateDateLabels() {
            const isOffsite = visitTypeSelect.value === 'OFFSITE';
            labelDateFromText.textContent = isOffsite ? 'Addressing Date From' : 'Visit From';
            labelDateToText.textContent   = isOffsite ? 'Addressing Date To'   : 'Visit To';
        }

        visitTypeSelect.addEventListener('change', updateDateLabels);
        updateDateLabels(); // run on load in case old() restores a value

        const fileInput = document.getElementById('attachmentInput');
        const fileListContainer = document.getElementById('fileListContainer');
        let accumulatedFiles = [];

        fileInput.addEventListener('change', function() {
            const newFiles = Array.from(this.files);
            
            // Check if adding new files exceeds the limit of 3
            if (accumulatedFiles.length + newFiles.length > 3) {
                alert('You can only select a maximum of 3 files total.');
                // Revert the input to what it was before this invalid selection
                updateFileInput(); 
                return;
            }

            // Append new files to our memory array
            accumulatedFiles = accumulatedFiles.concat(newFiles);
            
            renderFiles();
            updateFileInput();
        });

        function renderFiles() {
            fileListContainer.innerHTML = ''; // Clear container

            accumulatedFiles.forEach((file, index) => {
                const fileSize = (file.size / 1024 / 1024).toFixed(2); // Size in MB
                
                const fileItem = document.createElement('div');
                fileItem.className = 'badge bg-light text-dark border p-2 d-flex align-items-center justify-content-between w-100 shadow-sm';
                
                fileItem.innerHTML = `
                    <div class="d-flex align-items-center text-truncate pe-2">
                        <i class="bi bi-paperclip me-2 text-primary"></i>
                        <span class="text-truncate fw-normal" style="max-width: 250px;">${file.name}</span>
                        <span class="text-muted small ms-2 fw-light" style="white-space: nowrap;">(${fileSize} MB)</span>
                    </div>
                    <button type="button" class="btn-close ms-2 remove-file-btn" data-index="${index}" style="font-size: 0.65rem;" title="Remove this file"></button>
                `;
                
                fileListContainer.appendChild(fileItem);
            });

            // Attach listeners to all the new 'Remove' buttons
            document.querySelectorAll('.remove-file-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const indexToRemove = parseInt(this.getAttribute('data-index'));
                    // Remove the file from our memory array
                    accumulatedFiles.splice(indexToRemove, 1);
                    // Re-render and update the actual hidden input
                    renderFiles();
                    updateFileInput();
                });
            });
        }

        function updateFileInput() {
            // DataTransfer is a modern API that allows us to construct a new FileList
            // We need this because standard <input type="file"> is strictly read-only
            const dataTransfer = new DataTransfer();
            accumulatedFiles.forEach(file => {
                dataTransfer.items.add(file);
            });
            // Overwrite the input's files with our accumulated list
            fileInput.files = dataTransfer.files;
        }
    });
</script>
@endpush
@endsection
