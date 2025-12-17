<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forum extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'doctor_id',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function members()
    {
        return $this->hasMany(ForumMember::class);
    }
}
