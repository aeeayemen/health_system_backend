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
        'periods',
        'states_id',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
