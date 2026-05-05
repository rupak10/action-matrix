<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['acm_id', 'sl', 'party_type', 'emp_id', 'comments', 'attachment_path', 'visiting_date', 'created_by'])]
class AcmDiscussion extends Model
{
    protected $table = 'acm_discussions';
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
