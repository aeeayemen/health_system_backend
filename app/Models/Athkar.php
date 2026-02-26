<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Athkar extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'title',
        'content',
        'repetition',
        'admin_id',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
