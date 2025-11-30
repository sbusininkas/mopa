<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'school_id',
        'name',
        'description',
        'teacher_id',
        'school_year',
    ];

    /**
     * Get the school that owns this class.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the teacher (login key) for this class.
     */
    public function teacher()
    {
        return $this->belongsTo(LoginKey::class, 'teacher_id');
    }

    /**
     * Get the login keys for this class.
     */
    public function loginKeys()
    {
        return $this->hasMany(LoginKey::class, 'class_id');
    }

    /**
     * Get students in this class.
     */
    public function students()
    {
        return $this->hasMany(LoginKey::class, 'class_id')
            ->where('type', 'student')
            ->where('used', true);
    }
}
