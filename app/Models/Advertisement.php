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

    public function toArray()
    {
        $array = parent::toArray();
        if (isset($array['image']) && $array['image'] && strpos($array['image'], 'http') !== 0) {
            // Ensure the path has the correct prefix if it's just a filename
            if (strpos($array['image'], 'uploads/') !== 0) {
                $array['image'] = 'uploads/advertisements/' . ltrim($array['image'], '/');
            }
        }
        return $array;
    }
}
