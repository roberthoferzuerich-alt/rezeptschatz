<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'robert.hofer.zuerich@bluewin.ch'],
            [
                'name' => 'Robert Hofer',
                'role' => 'admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'annemariehofer@bluewin.ch'],
            [
                'name' => 'Annemarie Hofer',
                'role' => 'member',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'lucienne.hofer@bluewin.ch'],
            [
                'name' => 'Lucienne Hofer',
                'role' => 'member',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}
