<div class="modal fade" id="modal-add-obs" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:12px;border:none;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.12)">
            <form id="add-obs-form" method="POST" action="{{ route('visits.observations.store', $visit->id) }}"
                  enctype="multipart/form-data" novalidate>
                @csrf

                {{-- ── Header ──────────────────────────────────────────────── --}}
                <div style="background:#f8fafc;padding:1.25rem 1.5rem;border-bottom:1px solid #e2e8f0">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:34px;height:34px;border-radius:8px;background:var(--sl-primary);display:flex;align-items:center;justify-content:center">
                                <i class="bi bi-plus-lg" style="font-size:1rem;color:#fff"></i>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:.95rem;color:var(--sl-text)">Add Observation</div>
                                <div style="font-size:.75rem;color:var(--sl-muted)">{{ $visit->visit_code }} &mdash; {{ $visit->poInfo?->po_short_name ?? $visit->po_code }}</div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                </div>

                <div class="modal-body" style="padding:1.5rem;max-height:70vh;overflow-y:auto">

                    {{-- ── Inline error box (hidden by default) ──────────────── --}}
                    <div id="obs-error-box" class="alert alert-danger border-0 mb-3 d-none" style="border-radius:8px;font-size:.83rem">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <span id="obs-error-text"></span>
                    </div>

                    {{-- ── Section 1: Observation Text ─────────────────────── --}}
                    <div class="mb-4">

                        {{-- PKSF Observation --}}
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-baseline mb-1">
                                <label class="form-label mb-0" style="font-size:.82rem;font-weight:600">
                                    PKSF Observation <span class="text-danger">*</span>
                                </label>
                                <span class="obs-char-count" style="font-size:.72rem;color:var(--sl-muted)">0 / 2000</span>
                            </div>
                            <textarea name="pksf_observation" id="obs-pksf-text" class="form-control auto-expand"
                                      rows="4" maxlength="2000"
                                      style="font-size:.875rem;border-radius:8px;resize:none"
                                      placeholder="Describe the observation in detail…" required></textarea>
                        </div>

                        {{-- Direction to PO --}}
                        <div>
                            <label class="form-label" style="font-size:.82rem;font-weight:600">
                                Direction to PO <span class="text-danger">*</span>
                            </label>
                            <textarea name="direction_to_po" class="form-control auto-expand"
                                      rows="4" maxlength="2000"
                                      style="font-size:.875rem;border-radius:8px;resize:none"
                                      placeholder="Specific direction or action required from the PO…" required></textarea>
                        </div>
                    </div>

                    {{-- ── Section 2: Meta & Settings ──────────────────────── --}}
                    <div>
                        <div class="row g-3">

                            {{-- Category --}}
                            <div class="col-md-6">
                                <label class="form-label" style="font-size:.82rem;font-weight:600">
                                    Category <span class="text-danger">*</span>
                                </label>
                                <select name="observation_category" class="form-select" style="font-size:.875rem;border-radius:8px" required>
                                    <option value="">— Select Category —</option>
                                    @foreach(['FINANCIAL','OPERATIONAL','COMPLIANCE','GOVERNANCE','HR','OTHER'] as $cat)
                                    <option value="{{ $cat }}">{{ ucfirst(strtolower($cat)) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Action Matrix --}}
                            <div class="col-md-6">
                                <label class="form-label" style="font-size:.82rem;font-weight:600">Action Matrix</label>
                                <select name="action_matrix" class="form-select" style="font-size:.875rem;border-radius:8px">
                                    <option value="N" selected>No</option>
                                    <option value="Y">Yes</option>
                                </select>
                            </div>

                            {{-- Priority pills --}}
                            <div class="col-12">
                                <label class="form-label" style="font-size:.82rem;font-weight:600">Priority</label>
                                <div class="d-flex gap-2">
                                    @foreach([
                                        ['value'=>'LOW',    'label'=>'Low',    'color'=>'#16a34a', 'bg'=>'#f0fdf4', 'active_bg'=>'#16a34a'],
                                        ['value'=>'MEDIUM', 'label'=>'Medium', 'color'=>'#d97706', 'bg'=>'#fffbeb', 'active_bg'=>'#d97706'],
                                        ['value'=>'HIGH',   'label'=>'High',   'color'=>'#dc2626', 'bg'=>'#fef2f2', 'active_bg'=>'#dc2626'],
                                    ] as $p)
                                    <label class="priority-pill-label {{ $p['value'] === 'MEDIUM' ? 'active' : '' }}"
                                           data-color="{{ $p['color'] }}" data-bg="{{ $p['bg'] }}" data-active-bg="{{ $p['active_bg'] }}"
                                           style="cursor:pointer;border:2px solid {{ $p['value'] === 'MEDIUM' ? $p['color'] : '#e2e8f0' }};
                                                  border-radius:8px;padding:.4rem 1rem;font-size:.82rem;font-weight:600;
                                                  background:{{ $p['value'] === 'MEDIUM' ? $p['bg'] : 'transparent' }};
                                                  color:{{ $p['value'] === 'MEDIUM' ? $p['color'] : 'var(--sl-muted)' }};
                                                  transition:all .15s;user-select:none">
                                        <input type="radio" name="priority" value="{{ $p['value'] }}"
                                               {{ $p['value'] === 'MEDIUM' ? 'checked' : '' }}
                                               style="display:none">
                                        {{ $p['label'] }}
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- File attachment --}}
                            <div class="col-12">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <label class="form-label mb-0" style="font-size:.82rem;font-weight:600">Attachments</label>
                                    {{-- Slot counter updates dynamically via JS --}}
                                    <span id="obs-file-counter" style="font-size:.75rem;color:var(--sl-muted)">0 of 3 files</span>
                                </div>

                                {{-- Hidden real input --}}
                                <input type="file" id="obs-file-input" name="attachments[]" multiple
                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" style="display:none">

                                {{-- Styled trigger button --}}
                                <button type="button" id="obs-attach-btn"
                                        style="width:100%;border:1.5px dashed var(--sl-border);border-radius:8px;
                                               background:#fafafa;padding:.6rem 1rem;font-size:.82rem;font-weight:600;
                                               color:var(--sl-primary);cursor:pointer;transition:all .15s;
                                               display:flex;align-items:center;justify-content:center;gap:.5rem">
                                    <i class="bi bi-paperclip" style="font-size:.95rem"></i>
                                    Attach Files
                                    <span style="font-weight:400;color:var(--sl-muted);font-size:.75rem">· PDF, Word, Excel, Images · max 30MB</span>
                                </button>

                                {{-- Selected files rendered as badge rows --}}
                                <div id="obs-file-list" class="mt-2 d-flex flex-column gap-1"></div>
                            </div>

                        </div>
                    </div>

                </div>

                {{-- ── Footer ───────────────────────────────────────────────── --}}
                <div class="modal-footer" style="padding:.75rem 1.5rem 1.25rem;border-top:1px solid var(--sl-border);gap:.5rem">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                            style="border-radius:8px;font-size:.875rem;font-weight:600;min-width:90px">
                        Cancel
                    </button>
                    <button type="submit" class="btn" id="btn-save-obs"
                            style="background:var(--sl-primary);color:#fff;border-radius:8px;font-size:.875rem;font-weight:600;min-width:150px">
                        <i class="bi bi-plus-circle me-1"></i>Add Observation
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
function initAddObsForm(visitId) {

    // ── Date pickers (init when modal opens to avoid hidden-element issues) ──
    $('#modal-add-obs').on('shown.bs.modal', function () {
        $(this).find('.datepicker-obs').each(function () {
            if (!this._flatpickr) {
                flatpickr(this, { dateFormat: 'Y-m-d', altInput: true, altFormat: 'd-m-Y' });
            }
        });
    });

    // Reset the form fully when the modal is hidden
    $('#modal-add-obs').on('hidden.bs.modal', function () {
        document.getElementById('add-obs-form').reset();
        selectedFiles = [];
        $('#obs-file-list').empty();
        $('#obs-file-counter').text('0 of 3 files').css('color', 'var(--sl-muted)');
        attachBtn.disabled      = false;
        attachBtn.style.opacity = '1';
        attachBtn.style.cursor  = 'pointer';
        $('#obs-error-box').addClass('d-none');
        // Reset priority pills to Medium
        resetPriorityPills();
        // Reset action matrix label
        $('#action-matrix-label').text('Not included').css('color', 'var(--sl-muted)');
        // Reset textareas height
        $('#modal-add-obs .auto-expand').css('height', '');
        // Reset char counter
        $('.obs-char-count').text('0 / 2000');
        // Re-enable submit button in case it was disabled
        $('#btn-save-obs').prop('disabled', false).html('<i class="bi bi-plus-circle me-1"></i>Add Observation');
    });

    // Open modal
    $('#btn-add-obs').on('click', function () {
        new bootstrap.Modal(document.getElementById('modal-add-obs')).show();
    });

    // ── Priority pill toggle ─────────────────────────────────────────────────
    function resetPriorityPills() {
        $('.priority-pill-label').each(function () {
            const isDefault = $(this).find('input').val() === 'MEDIUM';
            const color     = $(this).data('color');
            const bg        = $(this).data('bg');
            $(this).css({
                border:     isDefault ? `2px solid ${color}` : '2px solid #e2e8f0',
                background: isDefault ? bg : 'transparent',
                color:      isDefault ? color : 'var(--sl-muted)',
            }).toggleClass('active', isDefault);
            $(this).find('input').prop('checked', isDefault);
        });
    }

    $(document).on('click', '.priority-pill-label', function () {
        // Deactivate all pills first
        $('.priority-pill-label').css({ border: '2px solid #e2e8f0', background: 'transparent', color: 'var(--sl-muted)' })
                                 .removeClass('active');
        // Activate clicked pill
        const color = $(this).data('color');
        const bg    = $(this).data('bg');
        $(this).css({ border: `2px solid ${color}`, background: bg, color: color })
               .addClass('active');
        $(this).find('input').prop('checked', true);
    });

    // ── Character counter for PKSF observation ──────────────────────────────
    $('#obs-pksf-text').on('input', function () {
        const len = $(this).val().length;
        $('.obs-char-count').text(`${len} / 2000`)
            .css('color', len > 1800 ? '#dc2626' : 'var(--sl-muted)');
    });

    // ── Auto-expand textareas as user types ──────────────────────────────────
    $(document).on('input', '#modal-add-obs .auto-expand', function () {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // ── File attachment ──────────────────────────────────────────────────────
    const fileInput   = document.getElementById('obs-file-input');
    const attachBtn   = document.getElementById('obs-attach-btn');
    let selectedFiles = [];  // plain array — FileList is immutable

    // Open file picker when the styled button is clicked
    attachBtn.addEventListener('click', () => fileInput.click());

    // Hover effect on attach button
    attachBtn.addEventListener('mouseenter', () => {
        if (!attachBtn.disabled) attachBtn.style.borderColor = 'var(--sl-primary)';
    });
    attachBtn.addEventListener('mouseleave', () => {
        if (!attachBtn.disabled) attachBtn.style.borderColor = 'var(--sl-border)';
    });

    fileInput.addEventListener('change', function () {
        const incoming = [...this.files];

        if (selectedFiles.length + incoming.length > 3) {
            showError('You can only attach a maximum of 3 files.');
            this.value = '';
            return;
        }

        incoming.forEach(f => selectedFiles.push(f));
        this.value = '';  // reset so the same file can be re-selected after removal
        renderFileList();
    });

    function renderFileList() {
        const list = document.getElementById('obs-file-list');
        list.innerHTML = '';

        selectedFiles.forEach((file, idx) => {
            const size = (file.size / 1024 / 1024).toFixed(2);
            const row  = document.createElement('div');
            row.className = 'badge bg-light text-dark border p-2 d-flex align-items-center justify-content-between w-100 shadow-sm';
            row.innerHTML = `
                <div class="d-flex align-items-center text-truncate pe-2">
                    <i class="bi bi-paperclip me-2 text-primary"></i>
                    <span class="text-truncate fw-normal" style="max-width:260px">${file.name}</span>
                    <span class="text-muted small ms-2 fw-light" style="white-space:nowrap">(${size} MB)</span>
                </div>
                <button type="button" class="btn-close obs-remove-file" data-idx="${idx}"
                        style="font-size:.65rem" title="Remove"></button>
            `;
            list.appendChild(row);
        });

        // Wire remove buttons after rendering
        list.querySelectorAll('.obs-remove-file').forEach(btn => {
            btn.addEventListener('click', function () {
                selectedFiles.splice(parseInt(this.dataset.idx), 1);
                renderFileList();
            });
        });

        // Update slot counter and disable button when limit is reached
        const count   = selectedFiles.length;
        const counter = document.getElementById('obs-file-counter');
        counter.textContent = `${count} of 3 files`;
        counter.style.color = count === 3 ? '#dc2626' : 'var(--sl-muted)';

        const atLimit            = count >= 3;
        attachBtn.disabled       = atLimit;
        attachBtn.style.opacity  = atLimit ? '.45' : '1';
        attachBtn.style.cursor   = atLimit ? 'not-allowed' : 'pointer';
    }

    // ── AJAX submit ──────────────────────────────────────────────────────────
    $('#add-obs-form').on('submit', function (e) {
        e.preventDefault();
        hideError();

        const fd = new FormData(this);

        // Attach the manually tracked files (replacing the empty file input)
        fd.delete('attachments[]');
        selectedFiles.forEach(f => fd.append('attachments[]', f));

        const btn = $('#btn-save-obs').prop('disabled', true)
                       .html('<span class="spinner-border spinner-border-sm me-1"></span>Saving…');

        $.ajax({
            url:         $(this).attr('action'),
            type:        'POST',
            data:        fd,
            processData: false,
            contentType: false,
            success: function (r) {
                if (r.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modal-add-obs')).hide();
                    window.flashAndReload('Observation added successfully.');
                } else {
                    showError(r.message ?? 'Failed to add observation.');
                    btn.prop('disabled', false).html('<i class="bi bi-plus-circle me-1"></i>Add Observation');
                }
            },
            error: function (xhr) {
                // Laravel validation returns errors as {field: [messages]}
                const errs = xhr.responseJSON?.errors;
                const msg  = errs
                    ? Object.values(errs).flat().join(' ')
                    : (xhr.responseJSON?.message ?? 'An error occurred. Please try again.');
                showError(msg);
                btn.prop('disabled', false).html('<i class="bi bi-plus-circle me-1"></i>Add Observation');
            }
        });
    });

    function showError(msg) {
        $('#obs-error-text').text(msg);
        $('#obs-error-box').removeClass('d-none');
        // Scroll to top of modal body so the error is visible
        $('#modal-add-obs .modal-body').scrollTop(0);
    }

    function hideError() {
        $('#obs-error-box').addClass('d-none');
    }
}
</script>
