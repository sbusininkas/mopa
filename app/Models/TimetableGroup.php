<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TimetableGroup extends Model
{
    protected $fillable = [
        'timetable_id',
        'name',
        'subject_id',
        'teacher_login_key_id',
        'room_id',
        'week_type',
        'lessons_per_week',
        'is_priority',
        'priority',
    ];
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function timetable(): BelongsTo
    {
        return $this->belongsTo(Timetable::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacherLoginKey(): BelongsTo
    {
        return $this->belongsTo(LoginKey::class, 'teacher_login_key_id');
    }

    // Alias for easier access
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(LoginKey::class, 'teacher_login_key_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(LoginKey::class, 'timetable_group_student', 'timetable_group_id', 'login_key_id');
    }
}
