<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPoAssignment extends Model
{
    public $timestamps = false;

    protected $fillable = ['emp_id', 'po_code', 'emp_role'];

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    public function user()
    {
        return $this->belongsTo(User::class, 'emp_id', 'emp_id');
    }

    public function po()
    {
        return $this->belongsTo(PoInfo::class, 'po_code', 'po_code');
    }
}
