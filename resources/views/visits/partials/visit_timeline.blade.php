@php
    $actionLabels = [
        'FORWARDED_TO_SUPERVISOR'   => ['label' => 'Forwarded to Supervisor', 'icon' => 'bi-send', 'color' => '#3b82f6', 'bg' => '#eff6ff'],
        'SENT_TO_PO'                => ['label' => 'Sent to PO',              'icon' => 'bi-arrow-right-circle', 'color' => '#0891b2', 'bg' => '#ecfeff'],
        'REJECTED_TO_CO'            => ['label' => 'Rejected to Officer',      'icon' => 'bi-arrow-counterclockwise', 'color' => '#e11d48', 'bg' => '#fff1f2'],
        'FORWARDED_TO_PO_OFFICER'   => ['label' => 'Sent to PO Officer',       'icon' => 'bi-person-check', 'color' => '#d97706', 'bg' => '#fffbeb'],
        'SUBMITTED_TO_PO_SUPERVISOR'=> ['label' => 'Submitted to PO Supervisor','icon' => 'bi-send-check', 'color' => '#059669', 'bg' => '#ecfdf5'],
        'APPROVED_AND_SENT_TO_PKSF' => ['label' => 'Approved — Sent to PKSF',  'icon' => 'bi-check-circle', 'color' => '#16a34a', 'bg' => '#dcfce7'],
    ];

    $movements = isset($visit->movements) ? $visit->movements : collect();

    // PO users only see PO-side movements
    if (auth()->user()->isPo()) {
        $movements = $movements->where('movement_side', 'PO');
    }
@endphp

@if($movements->isEmpty())
<div class="text-center text-muted py-3" style="font-size:.82rem">
    <i class="bi bi-clock-history fs-4 d-block mb-1"></i>No movement history yet
</div>
@else
<div class="d-flex flex-column gap-0">
    @foreach($movements->sortByDesc('id') as $mov)
    @php
        $meta     = $actionLabels[$mov->action_type] ?? ['label' => $mov->action_type, 'icon' => 'bi-arrow-right', 'color' => '#64748b', 'bg' => '#f1f5f9'];
        $fromUser = isset($usersByEmpId) ? ($usersByEmpId->get($mov->from_emp_id)?->name ?? $mov->from_emp_id) : ($mov->fromUser?->name ?? $mov->from_emp_id);
        $toUser   = isset($usersByEmpId) ? ($usersByEmpId->get($mov->to_emp_id)?->name ?? $mov->to_emp_id)   : ($mov->toUser?->name ?? $mov->to_emp_id);
    @endphp
    <div class="timeline-item">
        <div class="timeline-dot" style="background:{{ $meta['bg'] }};color:{{ $meta['color'] }}">
            <i class="bi {{ $meta['icon'] }}" style="font-size:.8rem"></i>
        </div>
        <div class="timeline-content">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="fw-semibold" style="font-size:.82rem;color:var(--sl-text)">{{ $meta['label'] }}</span>
                @if($mov->movement_side === 'PKSF')
                <span style="font-size:.65rem;font-weight:700;padding:.15em .55em;border-radius:4px;background:#dcfce7;color:#15803d;letter-spacing:.04em">PKSF</span>
                @else
                <span style="font-size:.65rem;font-weight:700;padding:.15em .55em;border-radius:4px;background:#fff7ed;color:#c2410c;letter-spacing:.04em">PO</span>
                @endif
            </div>
            <div class="text-muted" style="font-size:.75rem">
                <span>{{ $fromUser }}</span>
                @if($toUser && $toUser !== $fromUser)
                <span class="mx-1">→</span>
                <span>{{ $toUser }}</span>
                @endif
            </div>
            @if(!empty($mov->remarks))
            <div class="mt-1 p-1 rounded" style="font-size:.75rem;background:#f8fafc;border:1px solid var(--sl-border)">
                {{ Str::limit($mov->remarks, 120) }}
            </div>
            @endif
            @if($mov->remark ?? null)
            @if($mov->remark->remarks)
            <div class="mt-1 p-1 rounded" style="font-size:.75rem;background:#fffbeb;border:1px solid #fde68a">
                <i class="bi bi-chat-quote me-1 text-warning"></i>{{ Str::limit($mov->remark->remarks, 120) }}
            </div>
            @endif
            @if($mov->remark->attachments?->count())
            <div class="mt-1 d-flex flex-wrap gap-1">
                @foreach($mov->remark->attachments as $att)
                <a href="{{ $att->download_url }}" target="_blank" class="att-chip" style="font-size:.68rem">
                    <i class="bi bi-paperclip"></i>{{ Str::limit($att->file_name, 20) }}
                </a>
                @endforeach
            </div>
            @endif
            @endif
            <div class="text-muted mt-1" style="font-size:.7rem">
                {{ \Carbon\Carbon::parse($mov->created_at)->format('d M Y, h:i A') }}
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
