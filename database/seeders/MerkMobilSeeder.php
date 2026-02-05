<?php

namespace Database\Seeders;

use App\Models\MerkMobil;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MerkMobilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $merkMobils = [
            [
                'nama_merk' => 'Toyota',
                'deskripsi' => 'Toyota adalah perusahaan otomotif Jepang terkemuka yang dikenal dengan kualitas, keandalan, dan inovasi.',
            ],
            [
                'nama_merk' => 'Honda',
                'deskripsi' => 'Honda adalah produsen mobil Jepang yang terkenal dengan efisiensi bahan bakar dan desain yang inovatif.',
            ],
            [
                'nama_merk' => 'Suzuki',
                'deskripsi' => 'Suzuki memproduksi berbagai jenis kendaraan dengan harga terjangkau dan cocok untuk pasar Indonesia.',
            ],
            [
                'nama_merk' => 'Daihatsu',
                'deskripsi' => 'Daihatsu adalah merek mobil Jepang yang spesialis dalam kendaraan kompak dan efisien.',
            ],
            [
                'nama_merk' => 'Mitsubishi',
                'deskripsi' => 'Mitsubishi menawarkan kendaraan dengan teknologi canggih dan performa yang baik.',
            ],
            [
                'nama_merk' => 'Nissan',
                'deskripsi' => 'Nissan adalah produsen Jepang yang dikenal dengan inovasi teknologi dan desain yang menarik.',
            ],
            [
                'nama_merk' => 'Hyundai',
                'deskripsi' => 'Hyundai adalah merek mobil Korea Selatan dengan harga kompetitif dan kualitas baik.',
            ],
            [
                'nama_merk' => 'Kia',
                'deskripsi' => 'Kia memproduksi kendaraan dengan desain stylish dan fitur lengkap.',
            ],
            [
                'nama_merk' => 'BMW',
                'deskripsi' => 'BMW adalah merek mobil mewah Jerman yang terkenal dengan teknologi dan performa premium.',
            ],
            [
                'nama_merk' => 'Mercedes-Benz',
                'deskripsi' => 'Mercedes-Benz adalah produsen mobil mewah Jerman dengan standar kualitas tertinggi.',
            ],
            [
                'nama_merk' => 'Audi',
                'deskripsi' => 'Audi menawarkan kendaraan premium dengan teknologi terdepan dan desain elegan.',
            ],
            [
                'nama_merk' => 'Volkswagen',
                'deskripsi' => 'Volkswagen adalah produsen Jerman yang menawarkan berbagai jenis kendaraan dengan kualitas baik.',
            ],
            [
                'nama_merk' => 'Ford',
                'deskripsi' => 'Ford adalah produsen mobil Amerika dengan sejarah panjang dan reputasi yang solid.',
            ],
            [
                'nama_merk' => 'Chevrolet',
                'deskripsi' => 'Chevrolet memproduksi berbagai jenis kendaraan dari sedan hingga truck.',
            ],
            [
                'nama_merk' => 'Wuling',
                'deskripsi' => 'Wuling adalah merek mobil China yang berkembang pesat di pasar Indonesia.',
            ],
            [
                'nama_merk' => 'MG',
                'deskripsi' => 'MG adalah merek mobil China modern dengan desain kontemporer dan harga terjangkau.',
            ],
        ];

        foreach ($merkMobils as $merk) {
            MerkMobil::create($merk);
        }
    }
}
