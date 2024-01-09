<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosInventory extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function posProduct()
    {
        return $this->hasOne(PosProduct::class, 'inventory_id');
    }
}
