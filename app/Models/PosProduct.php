<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosProduct extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function posCategory()
    {
        return $this->belongsTo(PosCategory::class, 'category_id');
    }

    public function posProfile()
    {
        return $this->belongsTo(PosProfile::class, 'profile_id');
    }

    public function posInventory()
    {
        return $this->hasOne(PosInventory::class, 'product_id');
    }

    public function posDetailOrders()
    {
        return $this->hasMany(PosDetailOrder::class);
    }
}
