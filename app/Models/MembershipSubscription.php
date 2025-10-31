<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'season_start_year',
        'starts_at',
        'ends_at',
        'status',
        'amount',
        'due_date',
        'paid_at',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payment()
    {
        return $this->morphOne(Payment::class, 'payable');
    }
}
