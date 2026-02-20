<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DietNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'doctor_id',
        'diet_id',
        'note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
