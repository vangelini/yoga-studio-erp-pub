<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payable_type',
        'payable_id',
        'course_id',
        'type',
        'amount',
        'status',
        'due_date',
        'paid_at',
        'method',
        'meta',
        'status_reason',
        'receipt_number',
        'receipt_year',
        'receipt_path',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'meta' => 'array',
    ];

    protected $appends = [
        'receipt_url',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function markAsPaid(?string $method = null): void
    {
        $this->forceFill([
            'status' => 'paid',
            'paid_at' => now(),
            'method' => $method,
            'status_reason' => null,
        ])->save();
    }

    public function markAsWaived(?string $reason = null): void
    {
        $this->forceFill([
            'status' => 'waived',
            'status_reason' => $reason,
            'paid_at' => null,
            'method' => null,
        ])->save();
    }

    public function getReceiptUrlAttribute(): ?string
    {
        if (!$this->receipt_path) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->receipt_path);
    }
}
