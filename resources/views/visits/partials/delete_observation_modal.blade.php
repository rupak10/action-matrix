<div class="modal fade" id="modal-delete-obs" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px">
        <div class="modal-content" style="border-radius:12px;border:none;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.15)">

            {{-- ── Red accent header ───────────────────────────────────── --}}
            <div style="background:#fff1f2;padding:1.25rem 1.5rem;border-bottom:1px solid #fecdd3">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:40px;height:40px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="bi bi-trash3-fill" style="font-size:1.1rem;color:#dc2626"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:.9rem;color:#991b1b">Delete Observation</div>
                        <div style="font-size:.75rem;color:#b91c1c;margin-top:.1rem">
                            Observation <span id="delete-obs-idx"></span>
                        </div>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                </div>
            </div>

            {{-- ── Body ────────────────────────────────────────────────── --}}
            <div class="modal-body" style="padding:1.25rem 1.5rem;font-size:.875rem;color:var(--sl-text)">
                <p class="mb-2">This observation and all its attachments will be permanently deleted.</p>
                <p class="mb-0" style="font-size:.82rem;color:#dc2626;font-weight:600">
                    <i class="bi bi-exclamation-circle me-1"></i>This action cannot be undone.
                </p>
            </div>

            {{-- ── Footer ───────────────────────────────────────────────── --}}
            <div class="modal-footer" style="padding:.75rem 1.5rem 1.25rem;border-top:1px solid var(--sl-border);gap:.5rem">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                        style="border-radius:8px;font-size:.875rem;font-weight:600;min-width:90px">
                    Cancel
                </button>
                <button type="button" id="btn-confirm-delete-obs" class="btn btn-danger"
                        style="border-radius:8px;font-size:.875rem;font-weight:600;min-width:130px">
                    <i class="bi bi-trash3 me-1"></i>Delete
                </button>
            </div>

        </div>
    </div>
</div>
