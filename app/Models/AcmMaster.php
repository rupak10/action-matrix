<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'acm_id', 
    'po_code', 
    'visiting_date',      // from date
    'visiting_date_to',   // to date
    'observation_dept', 
    'observation_category', 
    'visit_type', 
    'visit_category', 
    'letter_issue_date', 
    'letter_response_date', 
    'pksf_observation', 
    'direction_to_po', 
    'action_matrix', 
    'po_inbox', 
    'is_editable_by_po', 
    'priority', 
    'status', 
    'resolution_status', 
    'created_by', 
    'updated_by',
    'current_desk_emp_id'
])]
class AcmMaster extends Model
{
    protected $table = 'acm_master';
    protected $primaryKey = 'acm_id';
    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get the file attachments for the matrix.
     */
    public function attachments()
    {
        return $this->hasMany(AcmMasterFileAttachment::class, 'acm_id', 'acm_id');
    }

    /**
     * Get the comments for this matrix.
     */
    public function comments()
    {
        return $this->hasMany(AcmComment::class, 'acm_id', 'acm_id')->orderBy('sl', 'asc');
    }

    /**
     * Get the PKSF movements for the matrix.
     */
    public function pksfMovements()
    {
        return $this->hasMany(AcmPksfMovement::class, 'acm_id', 'acm_id')->orderBy('sl');
    }

    /**
     * Get the PO movements for the matrix.
     */
    public function poMovements()
    {
        return $this->hasMany(AcmPoMovement::class, 'acm_id', 'acm_id')->orderBy('sl');
    }

    /**
     * Get the discussion thread for the matrix.
     */
    public function discussions()
    {
        return $this->hasMany(AcmDiscussion::class, 'acm_id', 'acm_id')->orderBy('sl');
    }

    /**
     * Check if the matrix has any comments.
     */
    public function hasComments()
    {
        return $this->comments()->exists();
    }
}
