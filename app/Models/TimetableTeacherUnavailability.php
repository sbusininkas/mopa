<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimetableTeacherUnavailability extends Model
{
    protected $fillable = [
        'timetable_id',
        'teacher_login_key_id',
        'day_of_week',
        'start_time',
        'end_time',
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
}
