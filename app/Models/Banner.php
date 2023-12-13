<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;
    protected $table = "banners";
    protected $fillable = [
        'judul_banner',
        'deskripsi_banner',
        'gambar_banner',
        'external_banner_id'
    ];
    protected $hidden = [
        'external_banner_id',
        // 'created_at',
        // 'updated_at'
    ];
}
