<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'notification_id',
        'status',
        'trigger_type',
        'scheduled_at',
        'started_at',
        'completed_at',
        'target_count',
        'sent_count',
        'failed_count',
        'summary',
        'error_message',
        'initiated_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'summary' => 'array',
    ];

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function dispatches(): HasMany
    {
        return $this->hasMany(NotificationDispatch::class);
    }
}
