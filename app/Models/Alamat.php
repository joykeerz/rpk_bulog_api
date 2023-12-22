<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alamat extends Model
{
    use HasFactory;
    protected $table = 'alamat';
    protected $guarded = ['id'];

    public function daftarAlamat()
    {
        return $this->hasMany(DaftarAlamat::class);
    }
    // public function biodata()
    // {
    //     return $this->belongsTo(Biodata::class);
    // }

    // public function company()
    // {
    //     return $this->belongsTo(Company::class);
    // }

    // public function pesanan()
    // {
    //     return $this->belongsTo(Pesanan::class);
    // }

    // public function gudang()
    // {
    //     return $this->belongsTo(Gudang::class);
    // }
}
