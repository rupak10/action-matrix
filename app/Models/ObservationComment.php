<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObservationComment extends Model
{
    protected $table = 'acm_observation_comments';

    protected $fillable = [
        'observation_id', 'comment_source', 'comment_detail',
        'is_draft', 'created_by', 'updated_by',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function observation()
    {
        return $this->belongsTo(Observation::class, 'observation_id');
    }

    public function attachments()
    {
        return $this->hasMany(ObservationCommentAttachment::class, 'comment_id')->orderBy('id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by', 'emp_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeDraft($query)
    {
        return $query->where('is_draft', 0);
    }

    public function scopeFinalized($query)
    {
        return $query->where('is_draft', 1);
    }

    public function scopeByAuthor($query, string $empId)
    {
        return $query->where('created_by', $empId);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isEditable(): bool
    {
        return (int) $this->is_draft === 0;
    }

    public function isFinalized(): bool
    {
        return (int) $this->is_draft === 1;
    }
}
