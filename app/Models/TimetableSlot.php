<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimetableSlot extends Model
{
    protected $fillable = [
        'timetable_id',
        'timetable_group_id',
        'day',
        'slot',
    ];

    public function timetable(): BelongsTo
    {
        return $this->belongsTo(Timetable::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(TimetableGroup::class, 'timetable_group_id');
    }
}
