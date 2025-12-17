<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'patient_id',
        'consultation_type',
        'status',
        'scheduled_date',
        'completed_date',
        'notes',
        'recommendations',
        'whatsapp_link',
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'completed_date' => 'datetime',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
