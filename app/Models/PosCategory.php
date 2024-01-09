<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosCategory extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function posProduct()
    {
        return $this->hasMany(PosProduct::class, 'category_id');
    }

    public function posProfile()
    {
        return $this->belongsTo(PosProfile::class, 'profile_id');
    }
}
