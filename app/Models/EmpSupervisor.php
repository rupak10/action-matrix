<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['emp_id', 'supervisor_id', 'created_by'])]
class EmpSupervisor extends Model
{
    /**
     * Get the employee user.
     */
    public function employee()
    {
        return $this->belongsTo(User::class, 'emp_id', 'emp_id');
    }

    /**
     * Get the supervisor user.
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id', 'emp_id');
    }
}
