<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosDiscount extends Model
{
    use HasFactory;
    protected $table = 'pos_discounts';
    protected $fillable = [
        'profile_id',
        'discount_name',
        'discount_type',
        'discount_value',
        'is_from_bulog',
        'is_active'
    ];
}
