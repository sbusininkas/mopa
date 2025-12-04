<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LoginKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'key',
        'type',
        'first_name',
        'last_name',
        'email',
        'user_id',
        'class_id',
        'used',
        'school_year',
    ];

    protected $casts = [
        'used' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->key) {
                $model->key = Str::random(12);
            }
        });
    }

    /**
     * Get the school that owns this login key.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the user who registered with this key.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the class this key belongs to (if student).
     */
    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the classes where this teacher is the class leader.
     */
    public function leadingClasses()
    {
        return $this->hasMany(SchoolClass::class, 'teacher_id');
    }

    /**
     * Get the teacher's working days for timetables.
     */
    public function teacherWorkingDays()
    {
        return $this->hasMany(TimetableTeacherWorkingDay::class, 'teacher_login_key_id');
    }

    /**
     * Generate a new unique key.
     */
    public static function generateKey(): string
    {
        do {
            $key = Str::random(12);
        } while (self::where('key', $key)->exists());

        return $key;
    }

    /**
     * Get full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
