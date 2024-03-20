<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    use HasFactory;
    protected $table = 'prices';
    protected $fillable = [
        'id',
        'price_value',
        'produk_id',
        'company_id',
        'created_at',
        'updated_at',
    ];
}
