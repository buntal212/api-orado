<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WilayahKotaProbolinggoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('kecamatans')->upsert([
            ['kode' => '35.74.01', 'nama_kecamatan' => 'Kademangan', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.02', 'nama_kecamatan' => 'Wonoasih', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.03', 'nama_kecamatan' => 'Mayangan', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.04', 'nama_kecamatan' => 'Kanigaran', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.05', 'nama_kecamatan' => 'Kedopok', 'created_at' => $now, 'updated_at' => $now],
        ], ['kode'], ['nama_kecamatan', 'updated_at']);

        DB::table('kelurahans')->upsert([
            ['kode' => '35.74.01.1001', 'kode_kecamatan' => '35.74.01', 'nama_kelurahan' => 'Ketapang', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.01.1002', 'kode_kecamatan' => '35.74.01', 'nama_kelurahan' => 'Triwung Lor', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.01.1003', 'kode_kecamatan' => '35.74.01', 'nama_kelurahan' => 'Triwung Kidul', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.01.1007', 'kode_kecamatan' => '35.74.01', 'nama_kelurahan' => 'Pohsangit Kidul', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.01.1008', 'kode_kecamatan' => '35.74.01', 'nama_kelurahan' => 'Kademangan', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.01.1009', 'kode_kecamatan' => '35.74.01', 'nama_kelurahan' => 'Pilang', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.02.1001', 'kode_kecamatan' => '35.74.02', 'nama_kelurahan' => 'Jrebeng Kidul', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.02.1002', 'kode_kecamatan' => '35.74.02', 'nama_kelurahan' => 'Pakistaji', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.02.1003', 'kode_kecamatan' => '35.74.02', 'nama_kelurahan' => 'Kedunggaleng', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.02.1005', 'kode_kecamatan' => '35.74.02', 'nama_kelurahan' => 'Kedung Asem', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.02.1006', 'kode_kecamatan' => '35.74.02', 'nama_kelurahan' => 'Sumber Taman', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.02.1007', 'kode_kecamatan' => '35.74.02', 'nama_kelurahan' => 'Wonoasih', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.03.1001', 'kode_kecamatan' => '35.74.03', 'nama_kelurahan' => 'Mayangan', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.03.1002', 'kode_kecamatan' => '35.74.03', 'nama_kelurahan' => 'Mangunharjo', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.03.1004', 'kode_kecamatan' => '35.74.03', 'nama_kelurahan' => 'Jati', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.03.1007', 'kode_kecamatan' => '35.74.03', 'nama_kelurahan' => 'Sukabumi', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.03.1010', 'kode_kecamatan' => '35.74.03', 'nama_kelurahan' => 'Wiroborang', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.04.1001', 'kode_kecamatan' => '35.74.04', 'nama_kelurahan' => 'Tisnonegaran', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.04.1002', 'kode_kecamatan' => '35.74.04', 'nama_kelurahan' => 'Sukoharjo', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.04.1003', 'kode_kecamatan' => '35.74.04', 'nama_kelurahan' => 'Kanigaran', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.04.1004', 'kode_kecamatan' => '35.74.04', 'nama_kelurahan' => 'Kebonsari Wetan', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.04.1005', 'kode_kecamatan' => '35.74.04', 'nama_kelurahan' => 'Curahgrinting', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.04.1006', 'kode_kecamatan' => '35.74.04', 'nama_kelurahan' => 'Kebonsari Kulon', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.05.1001', 'kode_kecamatan' => '35.74.05', 'nama_kelurahan' => 'Jrebeng Kulon', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.05.1002', 'kode_kecamatan' => '35.74.05', 'nama_kelurahan' => 'Kareng Lor', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.05.1003', 'kode_kecamatan' => '35.74.05', 'nama_kelurahan' => 'Sumber Wetan', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.05.1004', 'kode_kecamatan' => '35.74.05', 'nama_kelurahan' => 'Jrebeng Lor', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.05.1005', 'kode_kecamatan' => '35.74.05', 'nama_kelurahan' => 'Kedopok', 'created_at' => $now, 'updated_at' => $now],
            ['kode' => '35.74.05.1006', 'kode_kecamatan' => '35.74.05', 'nama_kelurahan' => 'Jrebeng Wetan', 'created_at' => $now, 'updated_at' => $now],
        ], ['kode'], ['kode_kecamatan', 'nama_kelurahan', 'updated_at']);
    }
}
