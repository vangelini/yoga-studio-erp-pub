<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherAvailability extends Model
{
    use HasFactory;

    protected $table = 'teacher_availability';

    protected $fillable = [
        'teacher_id',
        'slot_date',
        'slot_time',
        'is_booked',
        'booked_by_client_id',
    ];

    protected $casts = [
        'slot_date' => 'date:Y-m-d',
        'slot_time' => 'datetime:H:i',
        'is_booked' => 'boolean',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function bookedBy()
    {
        return $this->belongsTo(User::class, 'booked_by_client_id');
    }

    public function booking()
    {
        return $this->hasOne(Booking::class, 'availability_id');
    }
}
