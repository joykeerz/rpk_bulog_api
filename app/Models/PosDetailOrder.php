<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosDetailOrder extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $fillable = ['order_id', 'product_id', 'item_quantity', 'item_subtotal'];

    public function posProduct()
    {
        return $this->belongsTo(PosProduct::class, 'product_id');
    }
}
