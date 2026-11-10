<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class AddFamilyUsersCommand extends Command
{
    /**
     * Der Name und die Signatur des Konsolenbefehls.
     */
    protected $signature = 'users:add-family 
                            {--password=password : Standard-Passwort für die Benutzer}';

    /**
     * Die Beschreibung des Konsolenbefehls.
     */
    protected $description = 'Erstellt oder aktualisiert die Familienmitglieder Annemarie Hofer und Lucienne Hofer';

    public function handle(): int
    {
        $password = $this->option('password') ?: 'password';

        // Ggf. alten Tippfehler bereinigen, falls bereits angelegt
        $oldAnnemarie = User::where('email', 'annemarie.hofer@bluewin.ch')->first();
        if ($oldAnnemarie) {
            $oldAnnemarie->update(['email' => 'annemariehofer@bluewin.ch']);
        }

        $users = [
            [
                'name' => 'Annemarie Hofer',
                'email' => 'annemariehofer@bluewin.ch',
                'role' => 'member',
            ],
            [
                'name' => 'Lucienne Hofer',
                'email' => 'lucienne.hofer@bluewin.ch',
                'role' => 'member',
            ],
        ];

        $tableRows = [];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'role' => $userData['role'],
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                ]
            );

            $tableRows[] = [
                $user->id,
                $user->name,
                $user->email,
                $user->role,
                'Aktiv',
            ];
        }

        $this->info("✓ Familien-Benutzer erfolgreich eingerichtet!");
        $this->table(
            ['ID', 'Name', 'E-Mail', 'Rolle', 'Status'],
            $tableRows
        );
        $this->comment("Standard-Passwort: '{$password}' (kann im Profil geändert werden)");

        return self::SUCCESS;
    }
}
