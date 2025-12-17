<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainCalculation extends Model
{
    use HasFactory;

    protected $fillable = [
        'calories',
        'protin',
        'fat',
        'carbo',
        'BMR',
        'BMI',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
