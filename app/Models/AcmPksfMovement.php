<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['acm_id', 'sl', 'from_emp_id', 'to_emp_id', 'action_type', 'remarks', 'created_by'])]
class AcmPksfMovement extends Model
{
    protected $table = 'acm_pksf_movements';
    public $timestamps = false;
    protected $primaryKey = ['acm_id', 'sl'];
    public $incrementing = false;

    /**
     * Get the master matrix record.
     */
    public function master()
    {
        return $this->belongsTo(AcmMaster::class, 'acm_id', 'acm_id');
    }
}
