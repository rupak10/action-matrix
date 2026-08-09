<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitMovement extends Model
{
    protected $table = 'acm_visit_movements';
    public $timestamps = false;

    protected $fillable = [
        'visit_id', 'movement_side', 'from_emp_id', 'to_emp_id',
        'action_type', 'remarks', 'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function visit()
    {
        return $this->belongsTo(Visit::class, 'visit_id');
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_emp_id', 'emp_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_emp_id', 'emp_id');
    }

    public function remark()
    {
        return $this->hasOne(VisitRemark::class, 'movement_id');
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action_type) {
            'FORWARDED_TO_SUPERVISOR'          => 'Forwarded to Supervisor',
            'SENT_TO_PO'                       => 'Sent to PO',
            'REJECTED_TO_CO'                   => 'Rejected to CO',
            'FORWARDED_TO_PO_OFFICER'          => 'Forwarded to PO Officer',
            'SUBMITTED_TO_PO_SUPERVISOR'       => 'Submitted to PO Supervisor',
            'APPROVED_AND_SENT_TO_PKSF'        => 'Approved & Sent to PKSF',
            default                            => str_replace('_', ' ', $this->action_type),
        };
    }
}
