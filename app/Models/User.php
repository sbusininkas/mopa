<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'activated_by_key',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is teacher.
     */
    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    /**
     * Check if user is student.
     */
    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    /**
     * Check if user is priziuretojas (system supervisor who can assign schools).
     */
    public function isPriziuretojas(): bool
    {
        return $this->isSupervisor();
    }

    /**
     * Check if user is supervisor (system supervisor who can assign schools).
     */
    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    /**
     * The schools that belong to the user.
     */
    public function schools()
    {
        return $this->belongsToMany(\App\Models\School::class)->withPivot('is_admin')->withTimestamps();
    }

    /**
     * Check if user is an admin for a specific school.
     */
    public function isSchoolAdmin(int $schoolId): bool
    {
        return $this->schools()->where('schools.id', $schoolId)->wherePivot('is_admin', 1)->exists();
    }

    /**
     * Get IDs of schools where the user is a school admin.
     *
     * @return \Illuminate\Support\Collection
     */
    public function adminSchoolIds()
    {
        return $this->schools()->wherePivot('is_admin', 1)->pluck('schools.id');
    }

    /**
     * Get login keys associated with this user.
     */
    public function loginKeys()
    {
        return $this->hasMany(LoginKey::class);
    }

    /**
     * Get the login key that activated this account.
     */
    public function activatedByKey()
    {
        return $this->belongsTo(LoginKey::class, 'activated_by_key');
    }
}
