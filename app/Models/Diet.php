<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diet extends Model
{
    use HasFactory;

    protected $fillable = [
        'price',
        'doctor_id',
        'user_id',
        'subscription_id',
        'status',
        'periods',
        'states_id',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function components()
    {
        return $this->hasMany(DietComponent::class);
    }

    public function notes()
    {
        return $this->hasMany(DietNote::class);
    }
}
