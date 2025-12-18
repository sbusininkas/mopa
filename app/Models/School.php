<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'admin_key',
        'lesson_times',
    ];

    protected $hidden = [
        'admin_key',
    ];

    protected $casts = [
        'lesson_times' => 'array',
    ];
    
    /**
     * Get default lesson times
     */
    public static function getDefaultLessonTimes(): array
    {
        return [
            ['slot' => 1, 'start' => '08:00', 'end' => '08:45'],
            ['slot' => 2, 'start' => '08:55', 'end' => '09:40'],
            ['slot' => 3, 'start' => '09:50', 'end' => '10:35'],
            ['slot' => 4, 'start' => '11:05', 'end' => '11:50'],
            ['slot' => 5, 'start' => '12:15', 'end' => '13:00'],
            ['slot' => 6, 'start' => '13:10', 'end' => '13:55'],
            ['slot' => 7, 'start' => '14:05', 'end' => '14:50'],
            ['slot' => 8, 'start' => '14:55', 'end' => '15:40'],
            ['slot' => 9, 'start' => '15:45', 'end' => '16:30'],
        ];
    }
    
    /**
     * Get lesson times (with defaults if not set)
     */
    public function getLessonTimesAttribute($value): array
    {
        $times = $value ? json_decode($value, true) : null;
        return $times ?: self::getDefaultLessonTimes();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->admin_key) {
                $model->admin_key = Str::random(12);
            }
        });
    }

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
