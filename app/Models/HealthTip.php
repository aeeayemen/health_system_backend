<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthTip extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'category_id', 'image_url', 'is_active'];

    public function category()
    {
        return $this->belongsTo(TipCategory::class, 'category_id');
    }
}
