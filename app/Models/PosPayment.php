<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosPayment extends Model
{
    use HasFactory;
    protected $table = 'pos_payment_methods';
    protected $guarded = ['id'];

    public function sales()
    {
        return $this->hasMany(PosSale::class, 'payment_method_id');
    }
}
