<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'emp_id', 'emp_type', 'designation', 'dept_id', 'dept_name', 'unit_id', 'unit_name', 'po_code', 'supervisor_emp_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    /**
     * The roles that belong to the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'emp_id', 'role_id', 'emp_id', 'id');
    }

    /**
     * Check if the user has any of the given roles.
     *
     * @param array $roles
     * @return bool
     */
    public function hasAnyRole(array $roles)
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    /**
     * Check if the user is a PKSF staff member.
     */
    public function isPksf()
    {
        return strtoupper($this->emp_type ?? '') === 'PKSF';
    }

    /**
     * Check if the user is a Partner Organization (PO) user.
     */
    public function isPo()
    {
        return strtoupper($this->emp_type ?? '') === 'PO';
    }

    /**
     * Get the supervisor user.
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_emp_id', 'emp_id');
    }
}
