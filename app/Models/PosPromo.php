<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosPromo extends Model
{
    use HasFactory;
    protected $table = 'pos_promos';

    public function posSale()
    {
        return $this->hasMany(PosSale::class, 'promo_id');
    }
}
