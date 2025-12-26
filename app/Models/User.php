<?php

namespace App\Models;

use App\Models\Mark;
use App\Models\StudentParentInfo;
use App\Models\StudentAcademicInfo;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'gender',
        'nationality',
        'phone',
        'address',
        'address2',
        'city',
        'zip',
        'photo',
        'birthday',
        'religion',
        'blood_type',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // =========================
    // Relations
    // =========================

    public function parent_info()
    {
        return $this->hasOne(StudentParentInfo::class, 'student_id', 'id');
    }

    public function promotions()
    {
        return $this->hasMany(Promotion::class, 'student_id', 'id');
    }

    public function promotion()
    {
        return $this->hasOne(Promotion::class, 'student_id', 'id')->latestOfMany();
    }

    public function academic_info()
    {
        return $this->hasOne(StudentAcademicInfo::class, 'student_id', 'id');
    }

    public function marks()
    {
        return $this->hasMany(Mark::class, 'student_id', 'id');
    }

    public function teacherCourses()
    {
        return $this->hasMany(\App\Models\AssignedTeacher::class, 'teacher_id');
    }

    // =========================
    // Role helpers (Spatie + fallback column )
    // =========================

    /**
     * Example: "Super Admin" -> "super_admin"
     */
    public function roleSlugs(): array
    {
        $slugs = [];

        // Spatie roles
        foreach ($this->getRoleNames() as $name) {
            $slugs[] = strtolower(str_replace(' ', '_', trim($name)));
        }

        // fallback column role (بدون ! حتى ما يصير مشاكل)
        if (isset($this->role) && $this->role !== null && trim((string)$this->role) !== '') {
            $slugs[] = strtolower(str_replace(' ', '_', trim((string)$this->role)));
        }

        return array_values(array_unique($slugs));
    }

    public function primaryRoleSlug(): string
    {
        $slugs = $this->roleSlugs();
        return count($slugs) ? $slugs[0] : '';
    }

    public function isAdmin(): bool
    {
        $r = $this->roleSlugs();
        return in_array('super_admin', $r, true) || in_array('admin', $r, true);
    }

    public function isFinance(): bool
    {
        $r = $this->roleSlugs();
        return in_array('finance', $r, true)
            || in_array('accountant', $r, true)
            || in_array('accounting', $r, true)
            || in_array('accounts', $r, true);
    }

    public function isTeacher(): bool
    {
        $r = $this->roleSlugs();
        return in_array('teacher', $r, true) || in_array('instructor', $r, true);
    }

    public function isStudent(): bool
    {
        $r = $this->roleSlugs();
        return in_array('student', $r, true);
    }

    public function isParentRole(): bool
    {
        $r = $this->roleSlugs();
        return in_array('parent', $r, true) || in_array('guardian', $r, true);
    }

    public function isStaff(): bool
    {
        $r = $this->roleSlugs();
        return in_array('staff', $r, true)
            || in_array('employee', $r, true)
            || in_array('worker', $r, true);
    }

    // keep your old method name working
    public function primaryRoleName(): string
    {
        return $this->primaryRoleSlug();
    }
}
