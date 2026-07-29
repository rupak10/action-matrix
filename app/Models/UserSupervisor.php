<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSupervisor extends Model
{
    protected $table = 'user_supervisors';

    protected $fillable = ['emp_id', 'supervisor_emp_id', 'is_primary'];

    protected $casts = ['is_primary' => 'boolean'];

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_emp_id', 'emp_id');
    }
}
