<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatuanUnit extends Model
{
    use HasFactory;
    protected $table = 'satuan_unit';
    protected $fillable = [
        'nama_satuan',
        'keterangan',
        'simbol_satuan',
        'external_satuan_unit_id',
    ];
}
