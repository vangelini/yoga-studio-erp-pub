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
        'start_date',
        'end_date',
        'plan_type',
        'plan_amount',
        'status',
        'cancelled_at',
    ];

    protected $casts = [
        'auto_renew' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'plan_amount' => 'float',
        'cancelled_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function planMonths(): int
    {
        return Course::PLAN_MONTHS[$this->plan_type] ?? 1;
    }

    public function getPlanLabelAttribute(): string
    {
        return match ($this->plan_type) {
            'quarterly' => __('Trimestrale'),
            'annual' => __('Annuale'),
            default => __('Mensile'),
        };
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
