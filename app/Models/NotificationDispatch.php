<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDispatch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'notification_job_id',
        'notification_id',
        'user_id',
        'channel',
        'status',
        'sent_at',
        'error_message',
        'payload',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
        'deleted_at' => 'datetime',
        'is_read' => 'bool',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(NotificationJob::class, 'notification_job_id');
    }

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
