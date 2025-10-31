<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'course_id',
        'auto_renew',
    ];

    protected $casts = [
        'auto_renew' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
