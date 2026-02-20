<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    use HasFactory;

    protected $fillable = [
        'meal_id',
        'name',
        'serving',
        'describtion',
        'carbo',
        'protin',
        'fat',
        'energy',
        'category',
        'diet_plan_id',
        'day_number',
        'meal_type',
        'calories',
        'meal_name',
    ];
}
