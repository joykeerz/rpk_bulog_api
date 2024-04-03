<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function orderLines()
    {
        return $this->hasMany(OrderLine::class, 'sales_order_id');
    }
}
