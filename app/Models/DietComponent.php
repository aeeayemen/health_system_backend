<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DietComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'periods_time',
        'period_name',
        'doctor_id',
        'diet_id',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function diet()
    {
        return $this->belongsTo(Diet::class);
    }
}
