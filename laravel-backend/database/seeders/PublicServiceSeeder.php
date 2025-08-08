<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PublicServiceSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Baca dan decode file JSON
        $jsonPath = database_path('seeders/facilities.json');
        if (!File::exists($jsonPath)) {
            $this->command->error("File facilities.json tidak ditemukan!");
            return;
        }
        $facilities = json_decode(File::get($jsonPath));

        // 2. Siapkan kategori dalam sebuah map untuk efisiensi
        $categoryMap = [];

        // 3. Loop melalui data JSON untuk memproses setiap fasilitas
        foreach ($facilities as $facility) {
            // Cek apakah kategori sudah ada di map, jika tidak, buat baru di DB
            if (!isset($categoryMap[$facility->category])) {
                $categoryId = DB::table('service_categories')->insertGetId([
                    'name' => $facility->category,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $categoryMap[$facility->category] = $categoryId;
            }

            // 4. Masukkan data fasilitas ke database
            DB::table('public_services')->insert([
                'name' => $facility->name,
                'category_id' => $categoryMap[$facility->category], // Gunakan ID dari map
                'description' => $facility->description,
                'address' => $facility->address,
                'location' => DB::raw("ST_GeomFromText('POINT($facility->lng $facility->lat)', 4326)"),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}