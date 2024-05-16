<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DaftarAlamat extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $table = "daftar_alamat";

    public function alamat()
    {
        return $this->belongsTo(Alamat::class, 'alamat_id', 'id');
    }
}
