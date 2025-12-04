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

    // Group copies relationships
    public function copies()
    {
        return $this->hasMany(TimetableGroupCopy::class, 'original_group_id');
    }

    public function originalOf()
    {
        return $this->hasMany(TimetableGroupCopy::class, 'copy_group_id');
    }

    /**
     * Get all related group IDs (original + all copies)
     */
    public function getAllRelatedGroupIds(): array
    {
        $ids = [$this->id];
        
        // If this is a copy, get the original and all its copies
        $original = TimetableGroupCopy::where('copy_group_id', $this->id)->first();
        if ($original) {
            $ids[] = $original->original_group_id;
            $allCopies = TimetableGroupCopy::where('original_group_id', $original->original_group_id)->pluck('copy_group_id')->toArray();
            $ids = array_merge($ids, $allCopies);
        } else {
            // This is an original, get all its copies
            $allCopies = TimetableGroupCopy::where('original_group_id', $this->id)->pluck('copy_group_id')->toArray();
            $ids = array_merge($ids, $allCopies);
        }
        
        return array_unique($ids);
    }
}
