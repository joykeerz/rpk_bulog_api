<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosProfile extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function posProduct()
    {
        return $this->hasMany(PosProduct::class, 'profile_id');
    }

    public function posCategory()
    {
        return $this->hasMany(PosCategory::class, 'profile_id');
    }

    public function posOrder()
    {
        return $this->hasMany(PosOrder::class, 'profile_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
