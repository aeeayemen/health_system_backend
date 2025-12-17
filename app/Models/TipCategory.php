<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name_en', 'name_ar'];

    public function tips()
    {
        return $this->hasMany(Tip::class, 'category_id');
    }
}
