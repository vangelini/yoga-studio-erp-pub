<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionLesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'course_schedule_id',
        'day_of_week',
        'time',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'time' => 'datetime:H:i',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function schedule()
    {
        return $this->belongsTo(CourseSchedule::class, 'course_schedule_id');
    }
}
