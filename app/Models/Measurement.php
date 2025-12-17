<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Measurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'weight',
        'waist_circumference',
        'hip_circumference',
        'chest_circumference',
        'bmi',
        'measurement_date',
        'notes',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'waist_circumference' => 'decimal:2',
        'hip_circumference' => 'decimal:2',
        'chest_circumference' => 'decimal:2',
        'bmi' => 'decimal:2',
        'measurement_date' => 'date',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
