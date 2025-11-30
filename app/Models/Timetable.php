<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Timetable extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'is_public',
        'copied_from_id',
        'max_lessons_monday',
        'max_lessons_tuesday',
        'max_lessons_wednesday',
        'max_lessons_thursday',
        'max_lessons_friday',
        'max_same_subject_per_day',
        'generation_status',
        'generation_progress',
        'generation_started_at',
        'generation_finished_at',
        'generation_report',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'max_lessons_monday' => 'integer',
        'max_lessons_tuesday' => 'integer',
        'max_lessons_wednesday' => 'integer',
        'max_lessons_thursday' => 'integer',
        'max_lessons_friday' => 'integer',
        'max_same_subject_per_day' => 'integer',
        'generation_progress' => 'integer',
        'generation_started_at' => 'datetime',
        'generation_finished_at' => 'datetime',
        'generation_report' => 'array',
    ];
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(TimetableGroup::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(TimetableSlot::class);
    }
}
