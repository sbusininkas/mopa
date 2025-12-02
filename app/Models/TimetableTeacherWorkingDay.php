<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimetableTeacherWorkingDay extends Model
{
    protected $fillable = [
        'timetable_id',
        'teacher_login_key_id',
        'day_of_week',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
    ];

    public function timetable(): BelongsTo
    {
        return $this->belongsTo(Timetable::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(LoginKey::class, 'teacher_login_key_id');
    }

    /**
     * Get day name in Lithuanian
     */
    public function getDayNameAttribute(): string
    {
        return match($this->day_of_week) {
            1 => 'Pirmadienis',
            2 => 'Antradienis',
            3 => 'Trečiadienis',
            4 => 'Ketvirtadienis',
            5 => 'Penktadienis',
            default => 'Nežinoma',
        };
    }
}
