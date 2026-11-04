<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rezeptbuch:create-user 
                            {--name= : Name des Benutzers} 
                            {--email= : E-Mail-Adresse} 
                            {--password= : Passwort}
                            {--role= : Rolle (admin oder member)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Erstellt ein geschütztes Benutzerkonto für Rezeptbuch';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Name des Benutzers', 'Robert');
        $email = $this->option('email') ?: $this->ask('E-Mail-Adresse');
        $password = $this->option('password') ?: $this->secret('Passwort (wird verdeckt eingegeben)');
        $role = $this->option('role') ?: $this->choice('Rolle des Benutzers', ['member', 'admin'], 0);

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:admin,member'],
        ], [
            'email.unique' => 'Ein Benutzer mit dieser E-Mail existiert bereits.',
            'password.min' => 'Das Passwort muss mindestens 8 Zeichen lang sein.',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $role,
        ]);

        $this->info("Benutzer '{$user->name}' ({$user->email}) mit Rolle '{$user->role}' erfolgreich erstellt (ID {$user->id}).");

        return self::SUCCESS;
    }
}
