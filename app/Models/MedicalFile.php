<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'description',
        'uploaded_at',
        'status',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
