<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create([
            'name' => 'Umum & Kemampuan Dasar',
            'slug' => 'umum',
            'description' => 'Materi dan latihan SKB untuk kemampuan dasar umum',
            'order' => 1,
        ]);

        Category::create([
            'name' => 'Pendidikan & Kependidikan',
            'slug' => 'pendidikan',
            'description' => 'Untuk formasi guru, dosen, dan fungsional kependidikan',
            'order' => 2,
        ]);
    }
}