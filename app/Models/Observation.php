<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Observation extends Model
{
    protected $table = 'acm_observations';

    protected $fillable = [
        'visit_id', 'observation_category', 'pksf_observation', 'direction_to_po',
        'priority', 'action_matrix', 'resolution_status', 'created_by', 'updated_by',
    ];

    protected $casts = [];

    // ── Relationships ──────────────────────────────────────────────────────

    public function visit()
    {
        return $this->belongsTo(Visit::class, 'visit_id');
    }

    public function comments()
    {
        return $this->hasMany(ObservationComment::class, 'observation_id')->orderBy('created_at');
    }

    public function attachments()
    {
        return $this->hasMany(ObservationAttachment::class, 'observation_id')->orderBy('id');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isOpen(): bool
    {
        return $this->resolution_status === 'OPEN';
    }

    public function isPendingResolved(): bool
    {
        return $this->resolution_status === 'PENDING_RESOLVED';
    }

    public function isResolved(): bool
    {
        return $this->resolution_status === 'RESOLVED';
    }

    public function getResolutionStatusLabelAttribute(): string
    {
        return match ($this->resolution_status) {
            'OPEN'             => 'Open',
            'PENDING_RESOLVED' => 'Pending Resolution',
            'RESOLVED'         => 'Resolved',
            default            => $this->resolution_status,
        };
    }

    public function getResolutionStatusColorAttribute(): string
    {
        return match ($this->resolution_status) {
            'OPEN'             => 'danger',
            'PENDING_RESOLVED' => 'warning',
            'RESOLVED'         => 'success',
            default            => 'secondary',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'HIGH'   => 'danger',
            'MEDIUM' => 'warning',
            'LOW'    => 'success',
            default  => 'secondary',
        };
    }
}
