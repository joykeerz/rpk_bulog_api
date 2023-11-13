<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        DB::table('roles')->insert([
            'nama_role' => 'none',
            'desk_role' => 'none',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('roles')->insert([
            'nama_role' => 'Super Admin',
            'desk_role' => 'Manager of system or developer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('roles')->insert([
            'nama_role' => 'Penjual Pusat',
            'desk_role' => 'none',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('roles')->insert([
            'nama_role' => 'Manager Sales',
            'desk_role' => 'none',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('roles')->insert([
            'nama_role' => 'customer',
            'desk_role' => 'none',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Admin User Seed
        DB::table('users')->insert([
            'name' => 'Super Admin',
            'email' => 'admin@mail.com',
            'role_id' => '1',
            'password' => Hash::make('admin123'),
            'no_hp' => '086969420',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'name' => 'Penjual Pusat',
            'email' => 'penjual@mail.com',
            'role_id' => '2',
            'password' => Hash::make('admin123'),
            'no_hp' => '084206969',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Alamat Seed
        DB::table('alamat')->insert([
            'jalan' => 'none',
            'jalan_ext' => 'none',
            'blok' => 'none',
            'rt' => 'none',
            'rw' => 'none',
            'provinsi' => 'none',
            'kota_kabupaten' => 'none',
            'kecamatan' => 'none',
            'kelurahan' => 'none',
            'negara' => 'none',
            'kode_pos' => 'none',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('alamat')->insert([
            'jalan' => 'Jl. Nusa Bangsa',
            'jalan_ext' => 'Gg. Riya Raya',
            'blok' => 'Blok Jk No. 11',
            'rt' => '05',
            'rw' => '10',
            'provinsi' => 'Banten',
            'kota_kabupaten' => 'Tangerang',
            'kecamatan' => 'Serpong',
            'kelurahan' => 'Rawa Buaya',
            'negara' => 'Indonesia',
            'kode_pos' => '15318',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('alamat')->insert([
            'jalan' => 'Jl. Bisa Raya',
            'jalan_ext' => 'Gg. Bangsa Raya',
            'blok' => 'Blok PU No. 11',
            'rt' => '11',
            'rw' => '05',
            'provinsi' => 'Banten',
            'kota_kabupaten' => 'Tangerang',
            'kecamatan' => 'Serpong',
            'kelurahan' => 'Rawa Ikan',
            'negara' => 'Indonesia',
            'kode_pos' => '15310',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Biodata & RPK Seed
        DB::table('biodata')->insert([
            'user_id' => 1,
            'alamat_id' => 2,
            'nama_rpk' => 'RPK Joy',
            'no_ktp' => '123456789',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('biodata')->insert([
            'user_id' => 2,
            'alamat_id' => 3,
            'nama_rpk' => 'RPK Mahran',
            'no_ktp' => '123456789',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Kategori Seed
        DB::table('kategori')->insert([
            'nama_kategori' => 'none',
            'deskripsi_kategori' => 'none',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('kategori')->insert([
            'nama_kategori' => 'Beras Khusus',
            'deskripsi_kategori' => 'Beras asli',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('kategori')->insert([
            'nama_kategori' => 'Minyak Goreng',
            'deskripsi_kategori' => 'Minyak sawit',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('kategori')->insert([
            'nama_kategori' => 'Daging',
            'deskripsi_kategori' => 'Daging asli',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        //produk
        DB::table('produk')->insert([
            'kategori_id' => 2,
            'kode_produk' => 'B0001',
            'nama_produk' => 'Beras Al Hambra Biryani Kemasan',
            'desk_produk' => 'none',
            'harga_produk' => 40500,
            'diskon_produk' => 0,
            'satuan_unit_produk' => 'gr',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('produk')->insert([
            'kategori_id' => 3,
            'kode_produk' => 'M0001',
            'nama_produk' => 'Minyak Goreng Bimoli',
            'desk_produk' => 'none',
            'harga_produk' => 50200,
            'diskon_produk' => 0,
            'satuan_unit_produk' => 'liter',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('produk')->insert([
            'kategori_id' => 4,
            'kode_produk' => 'D0001',
            'nama_produk' => 'Daging Sapi Wagyu',
            'desk_produk' => 'none',
            'harga_produk' => 82500,
            'diskon_produk' => 0,
            'satuan_unit_produk' => 'kg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        ///stok seeder
        DB::table('stok')->insert([
            'produk_id' => 1,
            'gudang_id' => 1,
            'jumlah_stok' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('stok')->insert([
            'produk_id' => 2,
            'gudang_id' => 1,
            'jumlah_stok' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('stok')->insert([
            'produk_id' => 3,
            'gudang_id' => 1,
            'jumlah_stok' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Companies Seed
        DB::table('companies')->insert([
            'alamat_id' => 1,
            'user_id' => 1,
            'kode_company' => 'none',
            'nama_company' => 'none',
            'partner_company' => 'none',
            'tagline_company' => 'none',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Gudang Seed
        DB::table('gudang')->insert([
            'alamat_id' => 1,
            'company_id' => 1,
            'user_id' => 1,
            'nama_gudang' => 'none',
            'no_telp' => 'none',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Branch Seed
        DB::table('branches')->insert([
            'company_id' => 1,
            'nama_branch' => 'none',
            'no_telp_branch' => 'none',
            'alamat_branch' => 'none',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        //kurir seeder
        DB::table('kurir')->insert([
            'nama_kurir' => 'none',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('kurir')->insert([
            'nama_kurir' => 'JNE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
