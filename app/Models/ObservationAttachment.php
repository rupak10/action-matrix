<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ObservationAttachment extends Model
{
    protected $table = 'acm_observation_attachments';
    public $timestamps = false;

    protected $fillable = [
        'observation_id', 'file_name', 'file_path', 'file_type', 'file_size', 'created_by',
    ];

    public function observation()
    {
        return $this->belongsTo(Observation::class, 'observation_id');
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
