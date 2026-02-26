<?php

namespace App\Models;

use App\Enums\AthkarCategory;
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

    protected $casts = [
        'category' => AthkarCategory::class, // تحويل تلقائي للنوع
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}