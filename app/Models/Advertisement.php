<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'title',
        'date',
        'image',
        'describtion',
        'phone_number',
        'type',
        'GPS',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
