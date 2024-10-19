<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'short_title',
        'is_discount',
        'is_active',
        'discount',
        'image'
    ];
    public function getImageAttribute($val)
    {
        return ($val !== null) ? asset('categories/' . $val) : "";
    }
}