<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VisitRemarkAttachment extends Model
{
    protected $table = 'acm_visit_remark_attachments';
    public $timestamps = false;

    protected $fillable = [
        'remark_id', 'file_name', 'file_path', 'file_type', 'file_size', 'created_by',
    ];

    public function remark()
    {
        return $this->belongsTo(VisitRemark::class, 'remark_id');
    }

    public function getDownloadUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size ?? 0;
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}
