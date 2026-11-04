<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class AccountSettings extends Component
{
    #[Url(as: 'tab')]
    public string $tab = 'account';

    // Persönliche Informationen
    public string $name = '';
    public string $email = '';

    // Passwort ändern
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    // Benachrichtigungen
    public bool $notify_shared_recipes = true;
    public bool $notify_sync_status = true;

    // Einstellungen
    public int $default_servings = 4;
    public string $default_view = 'grid'; // 'grid' oder 'list'

    public function mount(): void
    {
        $user = Auth::user();
        if ($user) {
            $this->name = $user->name;
            $this->email = $user->email;
        }

        // Falls ein ungültiger Tab über die URL kommt, auf 'account' zurückfallen
        $validTabs = [
            'account', 'personal', 'password', 'credits',
            'notifications', 'settings', 'header-image',
            'close-account', 'help', 'news', 'websites'
        ];

        if (!in_array($this->tab, $validTabs, true)) {
            $this->tab = 'account';
        }
    }

    public function setTab(string $tabName): void
    {
        $this->tab = $tabName;
    }

    public function updateProfile(): void
    {
        $user = Auth::user();

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ], [
            'name.required' => 'Bitte geben Sie einen Namen an.',
            'email.required' => 'Bitte geben Sie eine gültige E-Mail-Adresse an.',
            'email.email' => 'Das Format der E-Mail-Adresse ist ungültig.',
            'email.unique' => 'Diese E-Mail-Adresse wird bereits verwendet.',
        ]);

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        session()->flash('success', 'Ihre persönlichen Informationen wurden erfolgreich aktualisiert.');
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'string', Password::min(8), 'confirmed'],
        ], [
            'current_password.required' => 'Bitte geben Sie Ihr aktuelles Passwort ein.',
            'current_password.current_password' => 'Das aktuelle Passwort ist nicht korrekt.',
            'new_password.required' => 'Bitte geben Sie ein neues Passwort ein.',
            'new_password.min' => 'Das neue Passwort muss mindestens 8 Zeichen lang sein.',
            'new_password.confirmed' => 'Die Bestätigung des neuen Passworts stimmt nicht überein.',
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

        session()->flash('success', 'Ihr Passwort wurde erfolgreich geändert.');
    }

    public function saveSettings(): void
    {
        session()->flash('success', 'Ihre Einstellungen wurden gespeichert.');
    }

    public function saveNotifications(): void
    {
        session()->flash('success', 'Ihre Benachrichtigungseinstellungen wurden aktualisiert.');
    }

    public function render()
    {
        return view('livewire.account-settings', [
            'user' => Auth::user(),
        ]);
    }
}
