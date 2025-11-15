<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'trigger_type',
        'event_type',
        'message_body',
        'channels',
        'target_all_teachers',
        'target_all_clients',
        'target_all_admins',
        'target_user_ids',
        'is_system',
        'is_active',
        'schedule_interval_unit',
        'schedule_interval_value',
        'schedule_time',
        'schedule_next_run_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_system' => 'bool',
        'is_active' => 'bool',
        'target_all_teachers' => 'bool',
        'target_all_clients' => 'bool',
        'target_all_admins' => 'bool',
        'schedule_next_run_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(NotificationJob::class);
    }

    public function dispatches(): HasMany
    {
        return $this->hasMany(NotificationDispatch::class);
    }

    public function courseTargets(): HasMany
    {
        return $this->hasMany(NotificationCourseTarget::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    protected function channels(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => array_values((array) json_decode($value ?: '[]', true)),
            set: fn ($value) => json_encode(array_values((array) $value))
        );
    }

    protected function targetUserIds(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => array_values((array) json_decode($value ?: '[]', true)),
            set: fn ($value) => json_encode(array_values((array) $value))
        );
    }
}
