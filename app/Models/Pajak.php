<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pajak extends Model
{
    use HasFactory;
    protected $table = 'pajak';
    protected $fillable = [
        'nama_pajak',
        'jenis_pajak',
        'persentase_pajak',
        'external_pajak_id'
    ];
}
