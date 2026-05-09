<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcmComment extends Model
{
    protected $table = 'acm_comments';
    
    // Disable auto-incrementing as we use composite keys
    public $incrementing = false;
    protected $primaryKey = ['acm_id', 'sl'];

    protected $fillable = [
        'acm_id',
        'sl',
        'comment_source',
        'comment_short',
        'comment_detail',
        'created_by',
        'updated_by',
        'updated_at'
    ];

    public $timestamps = false; // We handle created_at/updated_at manually or via useCurrent()

    /**
     * Get the attachments for this comment.
     */
    public function attachments()
    {
        return $this->hasMany(AcmCommentAttachment::class, 'acm_id', 'acm_id')
                    ->where('sl', $this->sl);
    }

    /**
     * Get the master record.
     */
    public function master()
    {
        return $this->belongsTo(AcmMaster::class, 'acm_id', 'acm_id');
    }
}
