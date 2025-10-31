<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'day_of_week',
        'time',
    ];

    protected $casts = [
        'time' => 'datetime:H:i',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
