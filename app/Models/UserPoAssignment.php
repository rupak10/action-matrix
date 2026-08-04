<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPoAssignment extends Model
{
    public $timestamps = false;

    protected $fillable = ['emp_id', 'po_code'];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    public function user()
    {
        return $this->belongsTo(User::class, 'emp_id', 'emp_id');
    }
}
