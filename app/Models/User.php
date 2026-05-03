<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;

// class User extends Authenticatable implements MustVerifyEmail
class User extends Authenticatable
{
    // use HasApiTokens, HasFactory, Notifiable, HasRoles, MustVerifyEmailTrait;
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        
        'type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'roles', // Hide the roles array
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['role'];

    /**
     * Get the user's role name.
     *
     * @return string|null
     */
    public function getRoleAttribute()
    {
        return $this->type;
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }

    public function patient()
    {
        return $this->hasOne(Patient::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function isPatient()
    {
        return in_array($this->type, ['patient', 'payed', 'subscribed']);
    }

    public function isDoctor()
    {
        return $this->type === 'doctor';
    }

    public function isAdmin()
    {
        return $this->type === 'admin';
    }

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function medicalTests()
    {
        return $this->hasMany(MedicalTest::class);
    }
}
