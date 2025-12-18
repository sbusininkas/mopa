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
        'use_priority_logic',
        'generation_status',
        'generation_progress',
        'generation_started_at',
        'generation_finished_at',
        'generation_report',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'use_priority_logic' => 'boolean',
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

    /**
     * Get teacher working days for this timetable.
     * Returns relationship to pivot table containing which days each teacher works.
     */
    public function teacherWorkingDays()
    {
        return $this->hasMany(TimetableTeacherWorkingDay::class);
    }

    /**
     * Teacher unavailability time ranges (per day of week and slot ranges).
     */
    public function teacherUnavailabilities()
    {
        return $this->hasMany(TimetableTeacherUnavailability::class);
    }

    /**
     * Get working days for a specific teacher in this timetable.
     * Returns array of day numbers (1-5) when teacher works.
     */
    public function getTeacherWorkingDays($teacherLoginKeyId): array
    {
        return $this->teacherWorkingDays()
            ->where('teacher_login_key_id', $teacherLoginKeyId)
            ->pluck('day_of_week')
            ->toArray();
    }

    /**
     * Check if teacher works on a specific day in this timetable.
     * If no working days are set, assume teacher works all days.
     */
    public function isTeacherWorkingOnDay($teacherLoginKeyId, $dayOfWeek): bool
    {
        $workingDays = $this->getTeacherWorkingDays($teacherLoginKeyId);
        
        // If no working days specified, teacher works all days (default behavior)
        if (empty($workingDays)) {
            return true;
        }
        
        return in_array($dayOfWeek, $workingDays);
    }

    /**
     * Check if teacher is unavailable for the given day and time.
     * Converts slot number to time (assuming slot duration and start time).
     * For simplicity: slot 1 = 08:00, slot 2 = 09:00, etc.
     */
    public function isTeacherUnavailableAtSlot($teacherLoginKeyId, int $dayOfWeek, int $slot): bool
    {
        // Map slot to approximate time (adjust as needed for your school schedule)
        $slotStartTime = sprintf('%02d:00:00', 7 + $slot); // e.g., slot 1 = 08:00
        $slotEndTime = sprintf('%02d:00:00', 8 + $slot);   // e.g., slot 1 ends at 09:00
        
        // Check if any unavailability range overlaps with this slot's time
        return $this->teacherUnavailabilities()
            ->where('teacher_login_key_id', $teacherLoginKeyId)
            ->where('day_of_week', $dayOfWeek)
            ->where(function($q) use ($slotStartTime, $slotEndTime) {
                // Range overlaps if: start_time < slotEnd AND end_time > slotStart
                $q->where('start_time', '<', $slotEndTime)
                  ->where('end_time', '>', $slotStartTime);
            })
            ->exists();
    }
}
