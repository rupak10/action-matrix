<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitRemark extends Model
{
    protected $table = 'acm_visit_remarks';
    public $timestamps = false;

    protected $fillable = [
        'visit_id', 'movement_id', 'remarks', 'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function visit()
    {
        return $this->belongsTo(Visit::class, 'visit_id');
    }

    public function movement()
    {
        return $this->belongsTo(VisitMovement::class, 'movement_id');
    }

    public function attachments()
    {
        return $this->hasMany(VisitRemarkAttachment::class, 'remark_id')->orderBy('id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by', 'emp_id');
    }
}
