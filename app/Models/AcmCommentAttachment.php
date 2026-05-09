<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcmCommentAttachment extends Model
{
    protected $table = 'acm_comments_file_attachment';
    
    public $incrementing = false;
    protected $primaryKey = ['acm_id', 'sl', 'file_id'];

    protected $fillable = [
        'acm_id',
        'sl',
        'file_id',
        'file_upload_by',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'created_by'
    ];

    public $timestamps = false;

    /**
     * Get the comment that owns the attachment.
     */
    public function comment()
    {
        return $this->belongsTo(AcmComment::class, 'acm_id', 'acm_id')
                    ->where('sl', $this->sl);
    }
}
