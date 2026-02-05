<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\KecamatanSeeder;
use Database\Seeders\KelurahanSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admins')->insert([
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('password'),
            ]
        ]);

        DB::table('category_kendaraans')->insert([
            [
                'name' => 'Mobil',
            ],
            [
                'name' => 'Motor',
            ]
        ]);

        $this->call(KecamatanSeeder::class);
        $this->call(KelurahanSeeder::class);
        $this->call(SpecialistSeeder::class);
        $this->call(MerkMobilSeeder::class);
    }
}
