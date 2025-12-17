<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyCalculation extends Model
{
    use HasFactory;

    protected $fillable = [
        'waist',
        'stomach',
        'arm',
        'chest',
        'thigh',
        'shoulder',
        'buttocks',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
