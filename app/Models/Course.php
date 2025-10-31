<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'teacher_id',
        'price',
        'speciality_description',
        'gallery',
    ];

    protected $casts = [
        'price' => 'float',
        'gallery' => 'array',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function schedule()
    {
        return $this->hasMany(CourseSchedule::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
