<div class="modal fade" id="modal-view-obs" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:12px;border:none;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.12)">

            {{-- ── Header ──────────────────────────────────────────────── --}}
            <div style="background:#f8fafc;padding:1.25rem 1.5rem;border-bottom:1px solid #e2e8f0">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:34px;height:34px;border-radius:8px;background:var(--sl-primary);display:flex;align-items:center;justify-content:center">
                            <i class="bi bi-eye" style="font-size:1rem;color:#fff"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size:.95rem;color:var(--sl-text)">
                                Observation <span id="view-obs-idx"></span>
                            </div>
                            <div style="font-size:.75rem;color:var(--sl-muted)">
                                {{ $visit->visit_code }} &mdash; {{ $visit->poInfo?->po_short_name ?? $visit->po_code }}
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>

            {{-- ── Body ────────────────────────────────────────────────── --}}
            <div class="modal-body" style="padding:1.5rem;max-height:72vh;overflow-y:auto">

                {{-- Loading spinner --}}
                <div id="view-obs-loading" class="text-center py-5">
                    <div class="spinner-border spinner-border-sm text-secondary"></div>
                    <div class="text-muted mt-2" style="font-size:.82rem">Loading…</div>
                </div>

                {{-- Content (shown after AJAX load) --}}
                <div id="view-obs-content" style="display:none">

                    {{-- Meta badges row --}}
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <span id="view-obs-res-badge" class="res-badge"></span>
                        <span id="view-obs-priority-badge" class="priority-pill"></span>
                        <span id="view-obs-category-badge" class="badge rounded-pill"
                              style="background:#f1f5f9;color:#475569;font-size:.72rem"></span>
                        {{-- Action matrix shown as a distinct badge in the meta row --}}
                        <span id="view-obs-am-yes" class="badge rounded-pill d-none"
                              style="background:#fef3c7;color:#92400e;font-size:.72rem">
                            <i class="bi bi-check-circle-fill me-1"></i>Action Matrix
                        </span>
                        <span id="view-obs-am-no" class="badge rounded-pill d-none"
                              style="background:#f1f5f9;color:#94a3b8;font-size:.72rem">
                            Action Matrix: NO
                        </span>
                    </div>

                    {{-- PKSF Observation --}}
                    <div class="mb-3">
                        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--sl-muted);margin-bottom:.5rem">
                            PKSF Observation
                        </div>
                        <div id="view-obs-pksf"
                             style="font-size:.875rem;line-height:1.8;white-space:pre-wrap;
                                    background:#fff;border:1px solid var(--sl-border);
                                    border-left:4px solid var(--sl-primary);
                                    border-radius:0 8px 8px 0;
                                    padding:.9rem 1.1rem;color:var(--sl-text)">
                        </div>
                    </div>

                    {{-- Direction to PO --}}
                    <div class="mb-4">
                        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--sl-muted);margin-bottom:.5rem">
                            Direction to PO
                        </div>
                        <div id="view-obs-direction"
                             style="font-size:.875rem;line-height:1.8;white-space:pre-wrap;
                                    background:#fff;border:1px solid var(--sl-border);
                                    border-left:4px solid #f59e0b;
                                    border-radius:0 8px 8px 0;
                                    padding:.9rem 1.1rem;color:var(--sl-text)">
                        </div>
                    </div>

                    {{-- Attachments --}}
                    <div id="view-obs-atts-wrap" class="d-none">
                        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--sl-muted);margin-bottom:.5rem">
                            Attachments
                        </div>
                        <div id="view-obs-atts" class="d-flex flex-column gap-1"></div>
                    </div>

                </div>
            </div>

            {{-- ── Footer ───────────────────────────────────────────────── --}}
            <div class="modal-footer" style="padding:.75rem 1.5rem 1.25rem;border-top:1px solid var(--sl-border)">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                        style="border-radius:8px;font-size:.875rem;font-weight:600;min-width:90px">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>

<script>
function initViewObsModal(visitId) {
    $(document).on('click', '.btn-view-obs', function () {
        const obsId = $(this).data('obs-id');
        const idx   = $(this).data('idx');

        // Reset to loading state before showing
        $('#view-obs-idx').text(`#${idx}`);
        $('#view-obs-content').hide();
        $('#view-obs-loading').show();

        new bootstrap.Modal(document.getElementById('modal-view-obs')).show();

        // Reuse the edit-data endpoint — returns all fields + attachments
        $.get(`/visits/${visitId}/observations/${obsId}/edit-data`, function (r) {
            const obs = r.observation;

            // ── Meta badges ────────────────────────────────────────────────
            const resMap = { OPEN: 'OPEN', PENDING_RESOLVED: 'PENDING RESOLUTION', RESOLVED: 'RESOLVED' };
            $('#view-obs-res-badge')
                .text(resMap[obs.resolution_status] ?? obs.resolution_status)
                .attr('class', `res-badge res-${obs.resolution_status}`);

            $('#view-obs-priority-badge')
                .text(obs.priority)
                .attr('class', `priority-pill priority-${obs.priority}`);

            $('#view-obs-category-badge').text(obs.observation_category);

            // Action matrix: show appropriate badge, hide the other
            if (obs.action_matrix === 'Y') {
                $('#view-obs-am-yes').removeClass('d-none');
                $('#view-obs-am-no').addClass('d-none');
            } else {
                $('#view-obs-am-yes').addClass('d-none');
                $('#view-obs-am-no').removeClass('d-none');
            }

            // ── Long text fields ───────────────────────────────────────────
            $('#view-obs-pksf').text(obs.pksf_observation);
            $('#view-obs-direction').text(obs.direction_to_po);

            // ── Attachments (as downloadable badge rows) ───────────────────
            const atts = r.attachments ?? [];
            if (atts.length) {
                const $list = $('#view-obs-atts').empty();
                atts.forEach(att => {
                    const size = att.file_size ?? '';
                    $list.append(`
                        <a href="${att.url}" download="${att.file_name}" target="_blank"
                           class="badge bg-light text-dark border p-2 d-flex align-items-center gap-2 w-100 shadow-sm"
                           style="text-decoration:none;cursor:pointer;transition:background .12s"
                           onmouseover="this.style.background='#e2e8f0'"
                           onmouseout="this.style.background=''">
                            <i class="bi bi-paperclip text-primary"></i>
                            <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:400;flex:1">
                                ${att.file_name}
                            </span>
                            <span class="text-muted fw-light" style="white-space:nowrap;font-size:.78rem">${size}</span>
                            <i class="bi bi-download text-muted ms-1" style="font-size:.8rem;flex-shrink:0"></i>
                        </a>
                    `);
                });
                $('#view-obs-atts-wrap').removeClass('d-none');
            } else {
                $('#view-obs-atts-wrap').addClass('d-none');
            }

            $('#view-obs-loading').hide();
            $('#view-obs-content').show();

        }).fail(function () {
            bootstrap.Modal.getInstance(document.getElementById('modal-view-obs')).hide();
            alert('Failed to load observation details.');
        });
    });

    // Format YYYY-MM-DD → "DD Mon YYYY"
    function formatDate(dateStr) {
        if (!dateStr) return '—';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }
}
</script>
