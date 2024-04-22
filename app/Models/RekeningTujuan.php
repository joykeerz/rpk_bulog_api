<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekeningTujuan extends Model
{
    use HasFactory;
    protected $table = 'rekening_tujuan';
    protected $guarded = ['id'];
}
