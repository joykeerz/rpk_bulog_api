<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductFile extends Model
{
    use HasFactory;
    protected $table = 'product_files';
    protected $fillable = [
        'file_name',
        // 'file_path',
        // 'file_type',
        // 'file_size',
    ];
}
