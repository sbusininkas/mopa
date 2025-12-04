<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimetableGroupCopy extends Model
{
    protected $fillable = [
        'original_group_id',
        'copy_group_id',
    ];

    public function originalGroup()
    {
        return $this->belongsTo(TimetableGroup::class, 'original_group_id');
    }

    public function copyGroup()
    {
        return $this->belongsTo(TimetableGroup::class, 'copy_group_id');
    }
}
