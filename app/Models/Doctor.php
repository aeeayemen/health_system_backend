<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'gender',
        'degree',
        'bank_account',
        'phone_number',
        'CV',
        'admin_id',
        'user_id',
        'application_status',
        'specialization',
        'license_number',
        'years_of_experience',
        'bio',
        'profile_image',
        'is_verified',
        'rating',
        'consultation_fee',
        'is_available',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'verification_date' => 'datetime',
        'is_available' => 'boolean',
        'rating' => 'decimal:2',
        'consultation_fee' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function patients()
    {
        return $this->hasMany(Patient::class, 'current_doctor_id');
    }

    public function dietPlans()
    {
        return $this->hasMany(DietPlan::class);
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
