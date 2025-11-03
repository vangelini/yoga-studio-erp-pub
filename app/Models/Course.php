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
        'monthly_price',
        'quarterly_price',
        'annual_price',
        'speciality_description',
        'gallery',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'price' => 'float',
        'monthly_price' => 'float',
        'quarterly_price' => 'float',
        'annual_price' => 'float',
        'gallery' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public const PLAN_MONTHS = [
        'monthly' => 1,
        'quarterly' => 3,
        'annual' => 12,
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

    public function getMonthlyPriceAttribute($value): ?float
    {
        if (!is_null($value)) {
            return (float) $value;
        }

        return isset($this->attributes['price']) ? (float) $this->attributes['price'] : null;
    }

    public function getPlanPrice(string $planType): ?float
    {
        return match ($planType) {
            'monthly' => $this->monthly_price,
            'quarterly' => $this->quarterly_price,
            'annual' => $this->annual_price,
            default => null,
        };
    }

    public function availablePlans(): array
    {
        $plans = [];

        foreach (self::PLAN_MONTHS as $plan => $months) {
            $amount = $this->getPlanPrice($plan);

            if (is_null($amount) || $amount <= 0) {
                continue;
            }

            $plans[] = [
                'type' => $plan,
                'months' => $months,
                'amount' => round((float) $amount, 2),
                'amount_formatted' => number_format((float) $amount, 2, '.', ''),
                'label' => match ($plan) {
                    'monthly' => __('Mensile'),
                    'quarterly' => __('Trimestrale'),
                    'annual' => __('Annuale'),
                    default => ucfirst($plan),
                },
            ];
        }

        return $plans;
    }
}
