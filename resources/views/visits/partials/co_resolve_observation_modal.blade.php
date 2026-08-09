<div class="modal fade" id="modal-co-resolve-obs" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width:560px">
        <div class="modal-content" style="border-radius:12px;border:none;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.15)">

            <div style="background:#f0fdf4;padding:1.25rem 1.5rem;border-bottom:1px solid #bbf7d0">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:40px;height:40px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="bi bi-check-circle" style="font-size:1.2rem;color:#16a34a"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:.9rem;color:#14532d">Resolve Observation</div>
                        <div style="font-size:.75rem;color:#16a34a;margin-top:.1rem">
                            Observation <span id="co-resolve-obs-idx"></span>
                        </div>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                </div>
            </div>

            <div class="modal-body" style="padding:1.25rem 1.5rem;font-size:.875rem;color:var(--sl-text)">
                <p class="mb-2">Are you sure you want to mark this observation as <strong>resolved</strong>?</p>
                <p class="mb-0" style="font-size:.82rem;color:var(--sl-muted)">
                    <i class="bi bi-info-circle me-1"></i>The supervisor will review and give final confirmation.
                </p>
            </div>

            <div class="modal-footer" style="padding:.75rem 1.5rem 1.25rem;border-top:1px solid var(--sl-border);gap:.5rem">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                        style="border-radius:8px;font-size:.875rem;font-weight:600;min-width:90px">
                    Cancel
                </button>
                <button type="button" id="btn-confirm-co-resolve-obs" class="btn"
                        style="background:#16a34a;color:#fff;border-radius:8px;font-size:.875rem;font-weight:600;min-width:140px">
                    <i class="bi bi-check-circle me-1"></i>Yes, Resolve
                </button>
            </div>

        </div>
    </div>
</div>
