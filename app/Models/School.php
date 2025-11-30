<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
    ];

    /**
     * The users that belong to the school.
     */
    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('is_admin')->withTimestamps();
    }

    /**
     * Get the classes in this school.
     */
    public function classes()
    {
        return $this->hasMany(SchoolClass::class);
    }

    /**
     * Get the login keys for this school.
     */
    public function loginKeys()
    {
        return $this->hasMany(LoginKey::class);
    }

        /**
         * Get the subjects for this school.
         */
        public function subjects()
        {
            return $this->hasMany(Subject::class);
        }

    /**
     * Get the rooms for this school.
     */
    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Get the timetables for this school.
     */
    public function timetables()
    {
        return $this->hasMany(Timetable::class);
    }
}
