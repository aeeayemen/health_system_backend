<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DietPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'diet_id',
        'name',
        'start_day',
        'end_day',
        'description'
    ];

    public function diet()
    {
        return $this->belongsTo(Diet::class);
    }
}
