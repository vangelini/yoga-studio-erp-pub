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
    ];

    protected $casts = [
        'specializations' => 'array',
        'can_host_private' => 'boolean',
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
        return $this->hasMany(Course::class, 'teacher_id');
    }
}
