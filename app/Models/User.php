<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

#[Fillable(['name', 'email', 'password', 'emp_id', 'emp_type', 'designation', 'dept_id', 'dept_name', 'unit_id', 'unit_name', 'po_code'])]
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

    public function supervisors()
    {
        return $this->hasMany(UserSupervisor::class, 'emp_id', 'emp_id');
    }

    public function primarySupervisorEmpId(): ?string
    {
        return $this->supervisors()->where('is_primary', true)->value('supervisor_emp_id');
    }

    public function isSeniorManagement(): bool
    {
        return $this->hasAnyRole(['SM_MD', 'SM_DMD', 'SM_SGM']);
    }

    public function isSmMd(): bool
    {
        return $this->hasAnyRole(['SM_MD']);
    }

    // Returns PO codes assigned to this SM user (DMD/SGM).
    // MD has full access and should never need to call this.
    public function assignedPoCodes(): array
    {
        return UserPoAssignment::where('emp_id', $this->emp_id)
            ->pluck('po_code')
            ->toArray();
    }

    public function poAssignments()
    {
        return $this->hasMany(UserPoAssignment::class, 'emp_id', 'emp_id');
    }

    public function createdVisits()
    {
        return $this->hasMany(Visit::class, 'created_by', 'emp_id');
    }

    public function deskVisits()
    {
        return $this->hasMany(Visit::class, 'current_desk_emp_id', 'emp_id');
    }

    public function isSupervisor(): bool
    {
        return \Illuminate\Support\Facades\DB::table('user_supervisors')
            ->where('supervisor_emp_id', $this->emp_id)
            ->exists();
    }
}
