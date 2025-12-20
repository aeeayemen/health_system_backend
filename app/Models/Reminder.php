<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'time',
        'user_id',
        'describtion',
    ];

    /**
     * Get the user that owns the reminder.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
