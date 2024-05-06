<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosOrder extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function posProfile()
    {
        return $this->belongsTo(PosProfile::class, 'profile_id');
    }

    public function posSale()
    {
        return $this->hasOne(PosSale::class, 'order_id');
    }
}
