<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) {
            return;
        }

        $standardTags = [
            ['name' => 'Schnell (< 30 Min.)', 'color' => '#10b981', 'icon' => 'timer'],
            ['name' => 'Gäste & Fest', 'color' => '#f59e0b', 'icon' => 'celebration'],
            ['name' => 'Einfach & Alltag', 'color' => '#8ea92c', 'icon' => 'restaurant'],
            ['name' => 'Klassiker', 'color' => '#6366f1', 'icon' => 'menu_book'],
            ['name' => 'Sommer', 'color' => '#ec4899', 'icon' => 'wb_sunny'],
            ['name' => 'Winter & Deftig', 'color' => '#8b5cf6', 'icon' => 'ac_unit'],
            ['name' => 'Low-Carb', 'color' => '#06b6d4', 'icon' => 'fitness_center'],
            ['name' => 'Vegetarisch', 'color' => '#22c55e', 'icon' => 'eco'],
            ['name' => 'Resteverwertung', 'color' => '#f97316', 'icon' => 'recycling'],
            ['name' => 'Apéro & Fingerfood', 'color' => '#eab308', 'icon' => 'tapas'],
            ['name' => 'Backofen', 'color' => '#ef4444', 'icon' => 'oven_gen'],
            ['name' => 'Schweizer Küche', 'color' => '#dc2626', 'icon' => 'flag'],
        ];

        foreach ($users as $user) {
            foreach ($standardTags as $tag) {
                Tag::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'name' => $tag['name'],
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'color' => $tag['color'],
                        'icon' => $tag['icon'],
                        'server_version' => 1,
                    ]
                );
            }
        }
    }
}

