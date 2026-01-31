<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KelurahanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $kelurahans = [
            ['kecamatan_id' => 1, 'name' => 'Cipayung'],
            ['kecamatan_id' => 1, 'name' => 'Ciputat'],
            ['kecamatan_id' => 1, 'name' => 'Jombang'],
            ['kecamatan_id' => 1, 'name' => 'Sawah Baru'],
            ['kecamatan_id' => 1, 'name' => 'Sawah Lama'],
            ['kecamatan_id' => 1, 'name' => 'Serua'],
            ['kecamatan_id' => 1, 'name' => 'Serua Indah'],
            ['kecamatan_id' => 2, 'name' => 'Cempaka Putih'],
            ['kecamatan_id' => 2, 'name' => 'Cireundue'],
            ['kecamatan_id' => 2, 'name' => 'Pisangan'],
            ['kecamatan_id' => 2, 'name' => 'Pondok Ranji'],
            ['kecamatan_id' => 2, 'name' => 'Rempoa'],
            ['kecamatan_id' => 2, 'name' => 'Rengas'],
            ['kecamatan_id' => 3, 'name' => 'Bambu Apus'],
            ['kecamatan_id' => 3, 'name' => 'Benda Baru'],
            ['kecamatan_id' => 3, 'name' => 'Kedaung'],
            ['kecamatan_id' => 3, 'name' => 'Pamulang Barat'],
            ['kecamatan_id' => 3, 'name' => 'Pamulang Timur'],
            ['kecamatan_id' => 3, 'name' => 'Pondok Benda'],
            ['kecamatan_id' => 3, 'name' => 'Pondok Cabe Ilir'],
            ['kecamatan_id' => 3, 'name' => 'Pondok Cabe Udik'],
            ['kecamatan_id' => 4, 'name' => 'Jurang Mangu Barat'],
            ['kecamatan_id' => 4, 'name' => 'Jurang Mangu Timur'],
            ['kecamatan_id' => 4, 'name' => 'Perigi Baru'],
            ['kecamatan_id' => 4, 'name' => 'Perigi Lama'],
            ['kecamatan_id' => 4, 'name' => 'Pondok Aren'],
            ['kecamatan_id' => 4, 'name' => 'Pondok Betung'],
            ['kecamatan_id' => 4, 'name' => 'Pondok Jaya'],
            ['kecamatan_id' => 4, 'name' => 'Pondok Kacang Barat'],
            ['kecamatan_id' => 4, 'name' => 'Pondok Kacang Timur'],
            ['kecamatan_id' => 4, 'name' => 'Pondok Karya'],
            ['kecamatan_id' => 5, 'name' => 'Buaran'],
            ['kecamatan_id' => 5, 'name' => 'Ciater'],
            ['kecamatan_id' => 5, 'name' => 'Cilenggang'],
            ['kecamatan_id' => 5, 'name' => 'Lengkong Gudang'],
            ['kecamatan_id' => 5, 'name' => 'Lengkong Gudang Timur'],
            ['kecamatan_id' => 5, 'name' => 'Lengkong Wetan'],
            ['kecamatan_id' => 5, 'name' => 'Rawa Buntu'],
            ['kecamatan_id' => 5, 'name' => 'Rawa Mekar Jaya'],
            ['kecamatan_id' => 5, 'name' => 'Serpong'],
            ['kecamatan_id' => 6, 'name' => 'Jelupang'],
            ['kecamatan_id' => 6, 'name' => 'Lengkong Karya'],
            ['kecamatan_id' => 6, 'name' => 'Paku Jaya'],
            ['kecamatan_id' => 6, 'name' => 'Pakualam'],
            ['kecamatan_id' => 6, 'name' => 'Pakulonan'],
            ['kecamatan_id' => 6, 'name' => 'Pondok Jagung'],
            ['kecamatan_id' => 6, 'name' => 'Pondok Jagung Timur'],
            // Kecamatan Setu
            ['kecamatan_id' => 7, 'name' => 'Bakti Jaya'],
            ['kecamatan_id' => 7, 'name' => 'Keranggan'],
            ['kecamatan_id' => 7, 'name' => 'Kademangan'],
            ['kecamatan_id' => 7, 'name' => 'Muncul'],
            ['kecamatan_id' => 7, 'name' => 'Setu'],
        ];

        DB::table('kelurahans')->insert($kelurahans);
    }
}
