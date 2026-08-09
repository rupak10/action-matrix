<div class="modal fade" id="modal-edit-obs" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:12px;border:none;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.12)">
            {{-- No _method hidden input — the route is POST, no method spoofing needed --}}
            <form id="edit-obs-form" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- ── Header ──────────────────────────────────────────────── --}}
                <div style="background:#f8fafc;padding:1.25rem 1.5rem;border-bottom:1px solid #e2e8f0">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:34px;height:34px;border-radius:8px;background:var(--sl-primary);display:flex;align-items:center;justify-content:center">
                                <i class="bi bi-pencil" style="font-size:.95rem;color:#fff"></i>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:.95rem;color:var(--sl-text)">Edit Observation</div>
                                <div style="font-size:.75rem;color:var(--sl-muted)">
                                    {{ $visit->visit_code }} &mdash; {{ $visit->poInfo?->po_short_name ?? $visit->po_code }}
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                </div>

                <div class="modal-body" style="padding:1.5rem;max-height:70vh;overflow-y:auto">

                    {{-- Inline error box --}}
                    <div id="edit-obs-error-box" class="alert alert-danger border-0 mb-3 d-none" style="border-radius:8px;font-size:.83rem">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <span id="edit-obs-error-text"></span>
                    </div>

                    {{-- Loading indicator --}}
                    <div id="edit-obs-loading" class="text-center py-4">
                        <div class="spinner-border spinner-border-sm text-secondary"></div>
                        <div class="text-muted mt-2" style="font-size:.82rem">Loading…</div>
                    </div>

                    <div id="edit-obs-fields" style="display:none">
                        <div class="row g-3">

                            {{-- PKSF Observation --}}
                            <div class="col-12">
                                <label class="form-label" style="font-size:.82rem;font-weight:600">PKSF Observation <span class="text-danger">*</span></label>
                                <textarea name="pksf_observation" id="edit-obs-observation" class="form-control" rows="4"
                                          style="font-size:.875rem;border-radius:8px;resize:none" required></textarea>
                            </div>

                            {{-- Direction to PO --}}
                            <div class="col-12">
                                <label class="form-label" style="font-size:.82rem;font-weight:600">Direction to PO <span class="text-danger">*</span></label>
                                <textarea name="direction_to_po" id="edit-obs-direction" class="form-control" rows="4"
                                          style="font-size:.875rem;border-radius:8px;resize:none" required></textarea>
                            </div>

                            {{-- Category --}}
                            <div class="col-md-6">
                                <label class="form-label" style="font-size:.82rem;font-weight:600">Category <span class="text-danger">*</span></label>
                                <select name="observation_category" id="edit-obs-category" class="form-select" style="font-size:.875rem;border-radius:8px" required>
                                    @foreach(['FINANCIAL','OPERATIONAL','COMPLIANCE','GOVERNANCE','HR','OTHER'] as $cat)
                                    <option value="{{ $cat }}">{{ ucfirst(strtolower($cat)) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Action Matrix --}}
                            <div class="col-md-3">
                                <label class="form-label" style="font-size:.82rem;font-weight:600">Action Matrix</label>
                                <select name="action_matrix" id="edit-obs-action-matrix" class="form-select" style="font-size:.875rem;border-radius:8px">
                                    <option value="N">No</option>
                                    <option value="Y">Yes</option>
                                </select>
                            </div>

                            {{-- Priority --}}
                            <div class="col-md-3">
                                <label class="form-label" style="font-size:.82rem;font-weight:600">Priority</label>
                                <select name="priority" id="edit-obs-priority" class="form-select" style="font-size:.875rem;border-radius:8px">
                                    <option value="LOW">Low</option>
                                    <option value="MEDIUM">Medium</option>
                                    <option value="HIGH">High</option>
                                </select>
                            </div>

                            {{-- Existing attachments --}}
                            <div class="col-12" id="edit-obs-existing-atts-wrap" style="display:none">
                                <label class="form-label" style="font-size:.82rem;font-weight:600">Current Attachments</label>
                                <div id="edit-obs-existing-atts" class="d-flex flex-column gap-1"></div>
                            </div>

                            {{-- New file attachment (same design as add modal) --}}
                            <div class="col-12">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <label class="form-label mb-0" style="font-size:.82rem;font-weight:600">Add New Files</label>
                                    <span id="edit-obs-file-counter" style="font-size:.75rem;color:var(--sl-muted)">0 of 3 files</span>
                                </div>

                                {{-- Hidden real input --}}
                                <input type="file" id="edit-obs-file-input" name="attachments[]" multiple
                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" style="display:none">

                                {{-- Styled trigger button --}}
                                <button type="button" id="edit-obs-attach-btn"
                                        style="width:100%;border:1.5px dashed var(--sl-border);border-radius:8px;
                                               background:#fafafa;padding:.6rem 1rem;font-size:.82rem;font-weight:600;
                                               color:var(--sl-primary);cursor:pointer;transition:all .15s;
                                               display:flex;align-items:center;justify-content:center;gap:.5rem">
                                    <i class="bi bi-paperclip" style="font-size:.95rem"></i>
                                    Attach Files
                                    <span style="font-weight:400;color:var(--sl-muted);font-size:.75rem">· PDF, Word, Excel, Images · max 30MB</span>
                                </button>

                                {{-- New files rendered as badge rows --}}
                                <div id="edit-obs-file-list" class="mt-2 d-flex flex-column gap-1"></div>
                            </div>

                        </div>
                    </div>

                </div>

                <div class="modal-footer" style="padding:.75rem 1.5rem 1.25rem;border-top:1px solid var(--sl-border);gap:.5rem">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                            style="border-radius:8px;font-size:.875rem;font-weight:600;min-width:90px">
                        Cancel
                    </button>
                    <button type="submit" class="btn" id="btn-save-edit-obs"
                            style="background:var(--sl-primary);color:#fff;border-radius:8px;font-size:.875rem;font-weight:600;min-width:160px">
                        <i class="bi bi-save me-1"></i>Update Observation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function initEditObsForm(visitId) {
    let removedAttIds  = [];
    let editNewFiles   = [];  // newly selected files (tracked manually like add modal)

    const fileInput  = document.getElementById('edit-obs-file-input');
    const attachBtn  = document.getElementById('edit-obs-attach-btn');

    // ── Open modal: load data via AJAX ──────────────────────────────────────
    $(document).on('click', '.btn-edit-obs', function () {
        const obsId = $(this).data('obs-id');

        // Reset state
        removedAttIds = [];
        editNewFiles  = [];
        renderEditFileList();

        $('#edit-obs-fields').hide();
        $('#edit-obs-loading').show();
        $('#edit-obs-error-box').addClass('d-none');

        // Route is POST: /visits/{visit}/observations/{obs}
        $('#edit-obs-form').attr('action', `/visits/${visitId}/observations/${obsId}`);

        const modal = new bootstrap.Modal(document.getElementById('modal-edit-obs'));
        modal.show();

        $.get(`/visits/${visitId}/observations/${obsId}/edit-data`, function (r) {
            const obs = r.observation;

            $('#edit-obs-category').val(obs.observation_category);
            $('#edit-obs-priority').val(obs.priority ?? 'MEDIUM');
            $('#edit-obs-action-matrix').val(obs.action_matrix ?? 'N');
            $('#edit-obs-observation').val(obs.pksf_observation);
            $('#edit-obs-direction').val(obs.direction_to_po);

            // Render existing attachments as removable chips
            const $wrap = $('#edit-obs-existing-atts').empty();
            if (r.attachments && r.attachments.length) {
                r.attachments.forEach(function (att) {
                    $wrap.append(`
                        <div class="badge bg-light text-dark border p-2 d-flex align-items-center justify-content-between w-100 shadow-sm" id="edit-att-${att.id}">
                            <div class="d-flex align-items-center text-truncate pe-2">
                                <i class="bi bi-paperclip me-2 text-primary"></i>
                                <a href="${att.url}" target="_blank"
                                   class="text-truncate fw-normal text-decoration-none text-dark"
                                   style="max-width:260px">${att.file_name}</a>
                                <span class="text-muted small ms-2 fw-light" style="white-space:nowrap">${att.file_size}</span>
                            </div>
                            <button type="button" class="btn-close edit-remove-existing-att ms-1"
                                    data-att-id="${att.id}" style="font-size:.65rem" title="Remove"></button>
                        </div>
                    `);
                });
                $('#edit-obs-existing-atts-wrap').show();
            } else {
                $('#edit-obs-existing-atts-wrap').hide();
            }

            $('#edit-obs-loading').hide();
            $('#edit-obs-fields').show();
        }).fail(function () {
            modal.hide();
            alert('Failed to load observation details.');
        });
    });

    // ── Remove existing attachment ───────────────────────────────────────────
    $(document).on('click', '.edit-remove-existing-att', function () {
        const attId = $(this).data('att-id');
        removedAttIds.push(attId);
        $(`#edit-att-${attId}`).remove();
        if ($('#edit-obs-existing-atts').children().length === 0) {
            $('#edit-obs-existing-atts-wrap').hide();
        }
    });

    // ── New file picker ──────────────────────────────────────────────────────
    attachBtn.addEventListener('click', () => fileInput.click());

    attachBtn.addEventListener('mouseenter', () => {
        if (!attachBtn.disabled) attachBtn.style.borderColor = 'var(--sl-primary)';
    });
    attachBtn.addEventListener('mouseleave', () => {
        if (!attachBtn.disabled) attachBtn.style.borderColor = 'var(--sl-border)';
    });

    fileInput.addEventListener('change', function () {
        const incoming = [...this.files];
        if (editNewFiles.length + incoming.length > 3) {
            showEditError('You can only attach a maximum of 3 new files.');
            this.value = '';
            return;
        }
        incoming.forEach(f => editNewFiles.push(f));
        this.value = '';
        renderEditFileList();
    });

    function renderEditFileList() {
        const list = document.getElementById('edit-obs-file-list');
        list.innerHTML = '';

        editNewFiles.forEach((file, idx) => {
            const size = (file.size / 1024 / 1024).toFixed(2);
            const row  = document.createElement('div');
            row.className = 'badge bg-light text-dark border p-2 d-flex align-items-center justify-content-between w-100 shadow-sm';
            row.innerHTML = `
                <div class="d-flex align-items-center text-truncate pe-2">
                    <i class="bi bi-paperclip me-2 text-primary"></i>
                    <span class="text-truncate fw-normal" style="max-width:260px">${file.name}</span>
                    <span class="text-muted small ms-2 fw-light" style="white-space:nowrap">(${size} MB)</span>
                </div>
                <button type="button" class="btn-close edit-remove-new-file" data-idx="${idx}"
                        style="font-size:.65rem" title="Remove"></button>
            `;
            list.appendChild(row);
        });

        list.querySelectorAll('.edit-remove-new-file').forEach(btn => {
            btn.addEventListener('click', function () {
                editNewFiles.splice(parseInt(this.dataset.idx), 1);
                renderEditFileList();
            });
        });

        const count   = editNewFiles.length;
        const counter = document.getElementById('edit-obs-file-counter');
        counter.textContent = `${count} of 3 files`;
        counter.style.color = count === 3 ? '#dc2626' : 'var(--sl-muted)';

        const atLimit           = count >= 3;
        attachBtn.disabled      = atLimit;
        attachBtn.style.opacity = atLimit ? '.45' : '1';
        attachBtn.style.cursor  = atLimit ? 'not-allowed' : 'pointer';
    }

    // ── Reset when modal closes ──────────────────────────────────────────────
    $('#modal-edit-obs').on('hidden.bs.modal', function () {
        editNewFiles  = [];
        removedAttIds = [];
        renderEditFileList();
        $('#edit-obs-error-box').addClass('d-none');
        $('#btn-save-edit-obs').prop('disabled', false)
            .html('<i class="bi bi-save me-1"></i>Update Observation');
    });

    // ── AJAX submit ──────────────────────────────────────────────────────────
    $('#edit-obs-form').on('submit', function (e) {
        e.preventDefault();
        hideEditError();

        const fd = new FormData(this);

        // Inject manually tracked new files (the hidden input is empty)
        fd.delete('attachments[]');
        editNewFiles.forEach(f => fd.append('attachments[]', f));

        // Tell the server which existing attachments to remove
        removedAttIds.forEach(id => fd.append('remove_attachments[]', id));

        const $btn = $('#btn-save-edit-obs').prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-1"></span>Saving…');

        $.ajax({
            url:         $(this).attr('action'),
            type:        'POST',   // route is POST — no method spoofing needed
            data:        fd,
            processData: false,
            contentType: false,
            success: function (r) {
                if (r.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modal-edit-obs')).hide();
                    window.flashAndReload('Observation updated successfully.');
                } else {
                    showEditError(r.message ?? 'Failed to update observation.');
                    $btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Update Observation');
                }
            },
            error: function (xhr) {
                const errs = xhr.responseJSON?.errors;
                const msg  = errs
                    ? Object.values(errs).flat().join(' ')
                    : (xhr.responseJSON?.message ?? 'An error occurred. Please try again.');
                showEditError(msg);
                $btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Update Observation');
            }
        });
    });

    function showEditError(msg) {
        $('#edit-obs-error-text').text(msg);
        $('#edit-obs-error-box').removeClass('d-none');
        $('#modal-edit-obs .modal-body').scrollTop(0);
    }

    function hideEditError() {
        $('#edit-obs-error-box').addClass('d-none');
    }
}
</script>
