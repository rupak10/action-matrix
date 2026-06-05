<div class="timeline-wrapper">
    <h6 class="fw-bold mb-4 text-primary"><i class="bi bi-chat-square-text me-2"></i>Discussion & Comments</h6>
    
    @forelse($master->comments as $comment)
        <div class="card border-0 shadow-sm mb-4 rounded-3 {{ $comment->comment_source === 'PKSF' ? 'bg-white ms-0 me-5' : 'bg-light-primary ms-5 me-0' }}">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <div class="avatar-xs bg-{{ $comment->comment_source === 'PKSF' ? 'primary' : 'warning' }} text-white rounded-circle d-flex align-items-center justify-content-center me-2 fw-bold" style="width: 24px; height: 24px; font-size: 0.7rem;">
                            {{ substr($comment->created_by, 0, 1) }}
                        </div>
                        <span class="fw-bold small">{{ $comment->created_by }}</span>
                        <span class="badge {{ $comment->comment_source === 'PKSF' ? 'bg-primary' : 'bg-warning text-dark' }} ms-2" style="font-size: 0.6rem;">{{ $comment->comment_source }}</span>
                    </div>
                    <small class="text-muted" style="font-size: 0.7rem;">{{ \Carbon\Carbon::parse($comment->updated_at)->format('d M, h:i A') }}</small>
                </div>
                
                @if($comment->comment_short)
                    <div class="mb-2">
                        <span class="badge bg-white text-dark border smaller fw-bold">{{ $comment->comment_short }}</span>
                    </div>
                @endif
                
                <p class="mb-2 smaller text-dark" style="white-space: pre-line;">{{ $comment->comment_detail }}</p>
                
                @if($comment->attachments->count() > 0)
                    <div class="border-top pt-2 mt-2">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($comment->attachments as $att)
                                <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="btn btn-sm btn-light border py-1 px-2 smaller rounded-pill">
                                    <i class="bi bi-file-earmark-arrow-down me-1"></i>{{ $att->file_name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="text-center py-4 text-muted italic smaller">
            No discussion history available.
        </div>
    @endforelse

    <hr class="my-5">

    <h6 class="fw-bold mb-4 text-secondary"><i class="bi bi-clock-history me-2"></i>Movement Log</h6>
    <div class="list-group list-group-flush shadow-sm rounded-3 overflow-hidden border">
        @php
            $allMovements = collect($master->pksfMovements)->concat($master->poMovements)->sortBy('created_at');
        @endphp
        
        @foreach($allMovements as $move)
            <div class="list-group-item list-group-item-action border-0 border-bottom p-3">
                <div class="d-flex w-100 justify-content-between align-items-center">
                    <h6 class="mb-1 fw-bold smaller text-primary">
                        <i class="bi bi-arrow-right-short me-1"></i>{{ str_replace('_', ' ', $move->action_type) }}
                    </h6>
                    <small class="text-muted" style="font-size: 0.65rem;">{{ \Carbon\Carbon::parse($move->created_at)->format('d M Y, h:i A') }}</small>
                </div>
                <div class="d-flex align-items-center mb-1">
                    <span class="smaller fw-bold text-dark">{{ $move->from_emp_id }}</span>
                    <i class="bi bi-arrow-right mx-2 text-muted" style="font-size: 0.7rem;"></i>
                    <span class="smaller fw-bold text-dark">{{ $move->to_emp_id }}</span>
                </div>
                @if($move->remarks)
                    <p class="mb-0 smaller text-muted italic bg-light p-2 rounded mt-1">"{{ $move->remarks }}"</p>
                @endif
            </div>
        @endforeach
    </div>
</div>

<style>
    .bg-light-primary { background-color: #f0f7ff; }
    .italic { font-style: italic; }
    .smaller { font-size: 0.85rem; }
</style>
