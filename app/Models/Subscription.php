<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'plan_type',
        'price',
        'duration_months',
        'start_date',
        'end_date',
        'status',
        'auto_renew',
        'receipt_image',
    ];

    protected $appends = ['receipt_url'];

    public function getReceiptUrlAttribute()
    {
        if (!$this->receipt_image) return null;
        if (strpos($this->receipt_image, 'http') === 0) return $this->receipt_image;
        return url($this->receipt_image);
    }

    protected $casts = [
        'price' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'auto_renew' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
