<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payable_type',
        'payable_id',
        'type',
        'amount',
        'status',
        'due_date',
        'paid_at',
        'method',
        'meta',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function markAsPaid(?string $method = null): void
    {
        $this->forceFill([
            'status' => 'paid',
            'paid_at' => now(),
            'method' => $method,
        ])->save();
    }
}
