<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'robert.hofer.zuerich@bluewin.ch')->first() ?? User::first();
        if (!$user) {
            return;
        }

        $categories = [
            [
                'id' => 'c1000000-0000-0000-0000-000000000001',
                'name' => 'Pasta & Nudeln',
                'icon' => 'dinner_dining',
                'color' => '#E67E22',
            ],
            [
                'id' => 'c1000000-0000-0000-0000-000000000002',
                'name' => 'Vegetarisch',
                'icon' => 'eco',
                'color' => '#27AE60',
            ],
            [
                'id' => 'c1000000-0000-0000-0000-000000000003',
                'name' => 'Schweizer Klassiker',
                'icon' => 'flag',
                'color' => '#C0392B',
            ],
            [
                'id' => 'c1000000-0000-0000-0000-000000000004',
                'name' => 'Auflauf & Gratin',
                'icon' => 'bakery_dining',
                'color' => '#D35400',
            ],
            [
                'id' => 'c1000000-0000-0000-0000-000000000005',
                'name' => 'Schnelle Küche',
                'icon' => 'timer',
                'color' => '#2980B9',
            ],
            [
                'id' => 'c1000000-0000-0000-0000-000000000006',
                'name' => 'Beilagen & Snacks',
                'icon' => 'restaurant',
                'color' => '#8E44AD',
            ],
            [
                'id' => 'c1000000-0000-0000-0000-000000000007',
                'name' => 'Fleisch & Geflügel',
                'icon' => 'kebab_dining',
                'color' => '#7F8C8D',
            ],
        ];

        foreach ($categories as $cat) {
            // Verwendet INSERT IGNORE auf Datenbank-Ebene: Falls ID existiert (auch gelöscht), wird der Fehler ignoriert
            DB::table('categories')->insertOrIgnore([
                'id' => $cat['id'],
                'user_id' => $user->id,
                'name' => $cat['name'],
                'icon' => $cat['icon'],
                'color' => $cat['color'],
                'server_version' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
