<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'school_id',
        'is_default', // ar dalykas yra numatytas (Lietuvos pagrindinis)
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
