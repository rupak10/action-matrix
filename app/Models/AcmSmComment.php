<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcmSmComment extends Model
{
    protected $table = 'acm_sm_comments';

    protected $fillable = ['visit_id', 'emp_id', 'comment'];

    public function visit()
    {
        return $this->belongsTo(Visit::class, 'visit_id');
    }

    public function commenter()
    {
        return $this->belongsTo(User::class, 'emp_id', 'emp_id');
    }
}
