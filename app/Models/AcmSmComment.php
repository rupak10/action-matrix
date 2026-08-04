<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcmSmComment extends Model
{
    protected $fillable = ['acm_id', 'emp_id', 'comment', 'attachments'];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function commenter()
    {
        return $this->belongsTo(User::class, 'emp_id', 'emp_id');
    }
}
