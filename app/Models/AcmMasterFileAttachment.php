<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['acm_id', 'sl', 'file_name', 'file_path', 'file_type', 'file_size', 'created_by'])]
class AcmMasterFileAttachment extends Model
{
    protected $table = 'acm_master_file_attachment';
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
