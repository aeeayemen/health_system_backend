<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $table = 'subscribed_users';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'id', // Since it's a foreign key to users and primary key, we might need to set it manually or let the controller handle it via relationship
        'user_id',
        'fullname',
        'gender',
        'height',
        'weight',
        'phone_number',
        'image',
        'birthdate',
        'physical_activity',
        'medical',
        'target_weight',
        'allergies',
        'current_doctor_id',
        'subscription_price',
        'subscription_type',
        'subscription_start_date',
        'subscription_end_date',
        'subscription_receipt_image',
        'subscription_status',
    ];

    protected $casts = [
        // 'birthdate' is string in migration, but maybe we want to cast it if it's a date string? Migration says string 100.
        // Keeping it as string or date depending on usage. Let's keep it simple for now or cast to date if it's YYYY-MM-DD.
        // Migration: $table->string('birthdate', 100)->nullable();
        // 'birthdate' => 'date', 
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'current_doctor_id');
    }

    public function dietPlans()
    {
        return $this->hasMany(DietPlan::class);
    }

    public function diets()
    {
        return $this->hasMany(Diet::class, 'user_id', 'user_id');
    }

    public function measurements()
    {
        return $this->hasMany(Measurement::class);
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function medicalFiles()
    {
        return $this->hasMany(MedicalFile::class);
    }

    public function medicalTests()
    {
        return $this->hasMany(MedicalTest::class, 'user_id', 'id');
    }

    public function getCurrentWeightAttribute()
    {
        return $this->weight;
    }

    /**
     * Get the patient's age based on birthdate.
     */
    public function getAgeAttribute()
    {
        if (!$this->birthdate) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($this->birthdate)->age;
        } catch (\Exception $e) {
            return null;
        }
    }
}
