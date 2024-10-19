<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StuffType extends Model
{
    use HasFactory;
    public function getImageAttribute($val)
    {
        return ($val !== null) ? asset('stuff_images/' . $val) : "";
    }
}