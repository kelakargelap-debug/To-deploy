<?php

namespace Database\Seeders;

use App\Models\Tryout;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TryoutSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tryout::create([
            'title' => 'SKB CPNS Umum — Latihan Dasar Komputer',
            'slug' => 'skb-cpns-umum-latihan-1',
            'category_id' => 1,
            'status' => 'PUBLISHED',
            'required_tier' => 'FREE',
            'duration_minutes' => 45,
            'total_questions' => 5,
            'passing_score' => 70,
            'randomize_order' => false,
            'show_result' => true,
        ]);

        Tryout::create([
            'title' => 'Premium SKB Pedagogik & Fungsional Guru',
            'slug' => 'skb-pedagogik-guru-premium',
            'category_id' => 2,
            'status' => 'PUBLISHED',
            'required_tier' => 'PREMIUM',
            'duration_minutes' => 60,
            'total_questions' => 5,
            'passing_score' => 75,
            'randomize_order' => true,
            'show_result' => true,
        ]);
    }
}