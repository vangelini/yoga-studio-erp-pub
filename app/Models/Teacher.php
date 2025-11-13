<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile_picture_url',
        'bio',
        'specializations',
        'can_host_private',
        'can_manage_courses',
        'can_manage_payments',
        'can_manage_students',
    ];

    protected $casts = [
        'specializations' => 'array',
        'can_host_private' => 'boolean',
        'can_manage_courses' => 'boolean',
        'can_manage_payments' => 'boolean',
        'can_manage_students' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function availability()
    {
        return $this->hasMany(TeacherAvailability::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'teacher_id', 'user_id');
    }
}
