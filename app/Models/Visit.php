<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Visit extends Model
{
    protected $table = 'acm_visits';

    protected $fillable = [
        'visit_code', 'po_code', 'visit_from_date', 'visit_to_date',
        'visit_type', 'visit_category', 'letter_issue_date', 'letter_response_date',
        'observation_dept', 'status', 'current_desk_emp_id', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'visit_from_date'      => 'date',
        'visit_to_date'        => 'date',
        'letter_issue_date'    => 'date',
        'letter_response_date' => 'date',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function observations()
    {
        return $this->hasMany(Observation::class, 'visit_id');
    }

    public function movements()
    {
        return $this->hasMany(VisitMovement::class, 'visit_id')->orderBy('created_at');
    }

    public function remarks()
    {
        return $this->hasMany(VisitRemark::class, 'visit_id')->orderBy('created_at');
    }

    public function latestRemark()
    {
        return $this->hasOne(VisitRemark::class, 'visit_id')->latestOfMany('created_at');
    }

    public function smComments()
    {
        return $this->hasMany(AcmSmComment::class, 'visit_id')->orderBy('created_at');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'emp_id');
    }

    public function currentDesk()
    {
        return $this->belongsTo(User::class, 'current_desk_emp_id', 'emp_id');
    }

    public function poInfo()
    {
        return $this->belongsTo(PoInfo::class, 'po_code', 'po_code');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeAtStatus(Builder $query, string|array $status): Builder
    {
        return is_array($status)
            ? $query->whereIn('status', $status)
            : $query->where('status', $status);
    }

    public function scopeForPo(Builder $query, string $poCode): Builder
    {
        return $query->where('po_code', $poCode);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isEditableByPksfCo(): bool
    {
        return in_array($this->status, ['SAVED', 'REJECTED'], true);
    }

    public function isOnPoSide(): bool
    {
        return in_array($this->status, ['PO_SO_REVIEW', 'PO_REVIEW', 'PO_SUBMITTED'], true);
    }

    public function isOnPksfSide(): bool
    {
        return in_array($this->status, ['SAVED', 'SUBMITTED', 'REJECTED', 'PO_APPROVED'], true);
    }

    public function getResolutionSummary(): array
    {
        $counts = \Illuminate\Support\Facades\DB::table('acm_observations')
            ->where('visit_id', $this->id)
            ->selectRaw('resolution_status, count(*) as total')
            ->groupBy('resolution_status')
            ->pluck('total', 'resolution_status')
            ->toArray();

        return [
            'open'             => (int) ($counts['OPEN'] ?? 0),
            'pending_resolved' => (int) ($counts['PENDING_RESOLVED'] ?? 0),
            'resolved'         => (int) ($counts['RESOLVED'] ?? 0),
            'total'            => array_sum($counts),
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'SAVED'         => 'Saved',
            'SUBMITTED'     => 'Submitted',
            'REJECTED'      => 'Rejected',
            'PO_SO_REVIEW'  => 'PO SO Review',
            'PO_REVIEW'     => 'PO Review',
            'PO_SUBMITTED'  => 'PO Submitted',
            'PO_APPROVED'   => 'PO Approved',
            default         => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'SAVED'         => 'secondary',
            'SUBMITTED'     => 'info',
            'REJECTED'      => 'danger',
            'PO_SO_REVIEW'  => 'warning',
            'PO_REVIEW'     => 'primary',
            'PO_SUBMITTED'  => 'warning',
            'PO_APPROVED'   => 'success',
            default         => 'secondary',
        };
    }
}
