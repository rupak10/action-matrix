@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@section('content')
<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="fw-bold text-gradient mb-0">Edit Action Matrix</h3>
                    <p class="text-muted small mb-0">Update {{ $master->acm_id }} before forwarding it for supervisor review.</p>
                </div>
                <a href="{{ route('action-matrix.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden glass-card">
                <div class="card-body p-4">
                    <div class="edit-context mb-4">
                        <div>
                            <span>ACM ID</span>
                            <strong>{{ $master->acm_id }}</strong>
                        </div>
                        <div>
                            <span>Status</span>
                            <strong>{{ $master->status }}</strong>
                        </div>
                        <div>
                            <span>PO Code</span>
                            <strong>{{ $master->po_code }}</strong>
                        </div>
                    </div>

                    <form action="{{ route('action-matrix.update', $master->acm_id) }}" method="POST" id="acmEditForm" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold mb-1">Partner Organization (PO)</label>
                                <input type="text" class="form-control form-control-sm bg-light" value="{{ $master->po_code }}" readonly>
                                <div class="form-text small text-muted">PO code is fixed because it is part of the generated ACM ID.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold mb-1">Visiting Date</label>
                                <div class="premium-date-field">
                                    <i class="bi bi-calendar3"></i>
                                    <input type="text" name="visiting_date" class="form-control form-control-sm js-premium-date" value="{{ old('visiting_date', $master->visiting_date ? \Carbon\Carbon::parse($master->visiting_date)->format('Y-m-d') : '') }}" placeholder="Select visiting date" required>
                                </div>
                                @error('visiting_date') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold mb-1">Observation Category</label>
                                <select name="observation_category" class="form-select form-select-sm" required>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}" {{ old('observation_category', $master->observation_category) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                    @endforeach
                                </select>
                                @error('observation_category') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold mb-1">Visit Type</label>
                                <select name="visit_type" class="form-select form-select-sm" required>
                                    @foreach($visitTypes as $type)
                                        <option value="{{ $type }}" {{ old('visit_type', $master->visit_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                                    @endforeach
                                </select>
                                @error('visit_type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold mb-1">Visit Category</label>
                                <select name="visit_category" class="form-select form-select-sm" required>
                                    @foreach($visitCategories as $vcat)
                                        <option value="{{ $vcat }}" {{ old('visit_category', $master->visit_category) == $vcat ? 'selected' : '' }}>{{ $vcat }}</option>
                                    @endforeach
                                </select>
                                @error('visit_category') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold mb-1">Letter Issue Date</label>
                                <div class="premium-date-field">
                                    <i class="bi bi-calendar3"></i>
                                    <input type="text" name="letter_issue_date" class="form-control form-control-sm js-premium-date" value="{{ old('letter_issue_date', $master->letter_issue_date ? \Carbon\Carbon::parse($master->letter_issue_date)->format('Y-m-d') : '') }}" placeholder="Select issue date">
                                </div>
                                @error('letter_issue_date') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold mb-1">Letter Response Date</label>
                                <div class="premium-date-field">
                                    <i class="bi bi-calendar3"></i>
                                    <input type="text" name="letter_response_date" class="form-control form-control-sm js-premium-date" value="{{ old('letter_response_date', $master->letter_response_date ? \Carbon\Carbon::parse($master->letter_response_date)->format('Y-m-d') : '') }}" placeholder="Select response date">
                                </div>
                                @error('letter_response_date') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold mb-1">PKSF Observation</label>
                                <textarea name="pksf_observation" class="form-control form-control-sm" rows="5" placeholder="Enter observation..." required>{{ old('pksf_observation', $master->pksf_observation) }}</textarea>
                                @error('pksf_observation') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold mb-1">Direction to PO</label>
                                <textarea name="direction_to_po" class="form-control form-control-sm" rows="5" placeholder="Enter instructions..." required>{{ old('direction_to_po', $master->direction_to_po) }}</textarea>
                                @error('direction_to_po') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <div class="attachment-panel">
                                    <div class="d-flex justify-content-between gap-3 flex-wrap mb-3">
                                        <div>
                                            <label class="form-label small fw-bold mb-1">Attachments</label>
                                            <div class="form-text small text-muted">Keep up to 3 files total. Files marked for removal are deleted after saving.</div>
                                        </div>
                                        <span class="attachment-count" id="attachmentCount">{{ $master->attachments->count() }} / 3 files</span>
                                    </div>

                                    @if($master->attachments->isNotEmpty())
                                        <div class="existing-files mb-3">
                                            @foreach($master->attachments as $attachment)
                                                @php
                                                    $attachmentSl = (int) $attachment->sl;
                                                    $isMarkedForRemoval = in_array($attachmentSl, array_map('intval', old('remove_attachments', [])), true);
                                                @endphp
                                                <label class="existing-file-row {{ $isMarkedForRemoval ? 'is-marked-remove' : '' }}">
                                                    <input type="checkbox" name="remove_attachments[]" value="{{ $attachmentSl }}" class="remove-existing-file" {{ $isMarkedForRemoval ? 'checked' : '' }}>
                                                    <span class="file-icon"><i class="bi bi-file-earmark-text"></i></span>
                                                    <span class="file-meta">
                                                        <strong>{{ $attachment->file_name ?? 'Attachment ' . $attachment->sl }}</strong>
                                                        <small>{{ $attachment->file_type ?? 'Unknown type' }}</small>
                                                    </span>
                                                    <span class="remove-copy">{{ $isMarkedForRemoval ? 'Marked for removal' : 'Remove' }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="empty-attachments mb-3">
                                            <i class="bi bi-folder2-open"></i>
                                            <span>No attachments uploaded yet.</span>
                                        </div>
                                    @endif

                                    <input type="file" name="attachments[]" id="attachmentInput" class="form-control form-control-sm" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,image/*">
                                    <div id="fileListContainer" class="mt-2 d-flex flex-column gap-1"></div>
                                    @error('attachments') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    @error('attachments.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold mb-1">Action Matrix</label>
                                <select name="action_matrix" class="form-select form-select-sm" required>
                                    <option value="Y" {{ old('action_matrix', $master->action_matrix) == 'Y' ? 'selected' : '' }}>YES</option>
                                    <option value="N" {{ old('action_matrix', $master->action_matrix) == 'N' ? 'selected' : '' }}>NO</option>
                                </select>
                                @error('action_matrix') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold mb-1">Priority</label>
                                <select name="priority" class="form-select form-select-sm" required>
                                    @foreach($priorities as $prio)
                                        <option value="{{ $prio }}" {{ old('priority', $master->priority) == $prio ? 'selected' : '' }}>{{ $prio }}</option>
                                    @endforeach
                                </select>
                                @error('priority') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-sm btn-primary w-100 py-2 shadow-sm">
                                    <i class="bi bi-check2-circle me-1"></i> Save Changes
                                </button>
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

    .edit-context {
        background: #f7fafa;
        border: 1px solid #e7eeee;
        border-radius: 12px;
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        padding: 16px;
    }

    .edit-context span {
        color: #718080;
        display: block;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        margin-bottom: 4px;
        text-transform: uppercase;
    }

    .edit-context strong {
        color: #1b3a3a;
        display: block;
        overflow-wrap: anywhere;
    }

    .form-label { color: #555; }

    .form-control-sm, .form-select-sm {
        border: 1px solid #dee6e9;
        border-radius: 8px;
        padding: 8px 12px;
    }

    .form-control-sm:focus, .form-select-sm:focus {
        border-color: #1b3a3a;
        box-shadow: 0 0 0 3px rgba(27, 58, 58, 0.05);
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
        cursor: pointer;
        padding-left: 38px;
    }

    .attachment-panel {
        background: #f8fbfb;
        border: 1px solid #e5eeee;
        border-radius: 12px;
        padding: 16px;
    }

    .attachment-count {
        align-self: start;
        background: #edf4f4;
        border-radius: 999px;
        color: #1b3a3a;
        font-size: 0.78rem;
        font-weight: 800;
        padding: 7px 12px;
    }

    .existing-files {
        display: grid;
        gap: 10px;
    }

    .existing-file-row {
        align-items: center;
        background: #ffffff;
        border: 1px solid #e1ebeb;
        border-radius: 10px;
        cursor: pointer;
        display: grid;
        gap: 12px;
        grid-template-columns: auto auto 1fr auto;
        margin: 0;
        padding: 12px;
    }

    .existing-file-row.is-marked-remove {
        background: #fff7f7;
        border-color: #f1b7b7;
        opacity: 0.72;
    }

    .existing-file-row.is-marked-remove .file-meta strong,
    .existing-file-row.is-marked-remove .file-meta small {
        text-decoration: line-through;
    }

    .file-icon {
        align-items: center;
        background: #e8f1f1;
        border-radius: 10px;
        color: #1b3a3a;
        display: inline-flex;
        height: 38px;
        justify-content: center;
        width: 38px;
    }

    .file-meta {
        min-width: 0;
    }

    .file-meta strong,
    .file-meta small {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .file-meta small {
        color: #718080;
    }

    .remove-copy {
        color: #b42318;
        font-size: 0.78rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .empty-attachments {
        align-items: center;
        background: #ffffff;
        border: 1px dashed #cfdcdd;
        border-radius: 10px;
        color: #718080;
        display: flex;
        gap: 9px;
        justify-content: center;
        padding: 18px;
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

    @media (max-width: 767.98px) {
        .edit-context {
            grid-template-columns: 1fr;
        }

        .existing-file-row {
            grid-template-columns: auto auto 1fr;
        }

        .remove-copy {
            grid-column: 2 / -1;
        }
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

        const fileInput = document.getElementById('attachmentInput');
        const fileListContainer = document.getElementById('fileListContainer');
        const attachmentCount = document.getElementById('attachmentCount');
        const removeCheckboxes = Array.from(document.querySelectorAll('.remove-existing-file'));
        const existingCount = {{ $master->attachments->count() }};
        let accumulatedFiles = [];

        function refreshExistingFileRows() {
            removeCheckboxes.forEach(checkbox => {
                const row = checkbox.closest('.existing-file-row');
                const copy = row?.querySelector('.remove-copy');

                row?.classList.toggle('is-marked-remove', checkbox.checked);

                if (copy) {
                    copy.textContent = checkbox.checked ? 'Marked for removal' : 'Remove';
                }
            });
        }

        function remainingExistingCount() {
            return existingCount - removeCheckboxes.filter(checkbox => checkbox.checked).length;
        }

        function updateAttachmentCount() {
            attachmentCount.textContent = `${remainingExistingCount() + accumulatedFiles.length} / 3 files`;
        }

        function updateFileInput() {
            const dataTransfer = new DataTransfer();
            accumulatedFiles.forEach(file => dataTransfer.items.add(file));
            fileInput.files = dataTransfer.files;
        }

        function renderFiles() {
            fileListContainer.innerHTML = '';

            accumulatedFiles.forEach((file, index) => {
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                const fileItem = document.createElement('div');
                fileItem.className = 'badge bg-white text-dark border p-2 d-flex align-items-center justify-content-between w-100 shadow-sm';
                fileItem.innerHTML = `
                    <div class="d-flex align-items-center text-truncate pe-2">
                        <i class="bi bi-paperclip me-2 text-primary"></i>
                        <span class="text-truncate fw-normal" style="max-width: 280px;">${file.name}</span>
                        <span class="text-muted small ms-2 fw-light" style="white-space: nowrap;">(${fileSize} MB)</span>
                    </div>
                    <button type="button" class="btn-close ms-2 remove-file-btn" data-index="${index}" style="font-size: 0.65rem;" title="Remove this file"></button>
                `;

                fileListContainer.appendChild(fileItem);
            });

            document.querySelectorAll('.remove-file-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    accumulatedFiles.splice(parseInt(this.getAttribute('data-index')), 1);
                    renderFiles();
                    updateFileInput();
                    updateAttachmentCount();
                });
            });
        }

        fileInput.addEventListener('change', function() {
            const newFiles = Array.from(this.files);

            if (remainingExistingCount() + accumulatedFiles.length + newFiles.length > 3) {
                alert('You can keep a maximum of 3 files total.');
                updateFileInput();
                return;
            }

            accumulatedFiles = accumulatedFiles.concat(newFiles);
            renderFiles();
            updateFileInput();
            updateAttachmentCount();
        });

        removeCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (remainingExistingCount() + accumulatedFiles.length > 3) {
                    accumulatedFiles = accumulatedFiles.slice(0, 3 - remainingExistingCount());
                    renderFiles();
                    updateFileInput();
                }

                refreshExistingFileRows();
                updateAttachmentCount();
            });
        });

        refreshExistingFileRows();
        updateAttachmentCount();
    });
</script>
@endpush
@endsection
