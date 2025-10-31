<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmailContract
{
    use HasFactory, Notifiable, MustVerifyEmail;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'name',
        'email',
        'password_hash',
        'role',
        'status',
        'telephone',
        'residenza_citta',
        'residenza_provincia',
        'residenza_stato',
        'residenza_via',
        'residenza_numero_civico',
        'codice_fiscale',
        'luogo_nascita',
        'data_nascita',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'data_nascita' => 'date:Y-m-d',
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Relationships
     */
    public function teacherProfile()
    {
        return $this->hasOne(Teacher::class);
    }

    public function bookingsAsTeacher()
    {
        return $this->hasMany(Booking::class, 'teacher_id');
    }

    public function bookingsAsClient()
    {
        return $this->hasMany(Booking::class, 'client_id');
    }

    public function membershipSubscriptions()
    {
        return $this->hasMany(MembershipSubscription::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
