<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alamat extends Model
{
    use HasFactory;
    protected $table = 'alamat';
    protected $fillable = [
        'id',
        'jalan',
        'jalan_ext',
        'blok',
        'nomor',
        'rt',
        'rw',
        'provinsi',
        'provinsi_id',
        'kota_kabupaten',
        'kabupaten_id',
        'kecamatan',
        'kecamatan_id',
        'kelurahan',
        'kelurahan_id',
        'negara',
        'kode_pos',
        'external_alamat_id',
    ];
    public function daftarAlamats()
    {
        return $this->hasMany(DaftarAlamat::class, 'alamat_id', 'id');
    }
    // protected $guarded = ['id'];

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
