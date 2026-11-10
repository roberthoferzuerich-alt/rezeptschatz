<?php

namespace App\Livewire;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class UserManual extends Component
{
    public string $searchQuery = '';

    public function render()
    {
        $path = base_path('Documentation/BENUTZERANLEITUNG.md');
        $rawMarkdown = File::exists($path) ? File::get($path) : '# Benutzeranleitung nicht gefunden';

        // In HTML konvertieren mit sicherem Markdown Parser von Laravel
        $renderedHtml = Str::markdown($rawMarkdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return view('livewire.user-manual', [
            'contentHtml' => $renderedHtml,
            'rawMarkdown' => $rawMarkdown,
        ]);
    }
}
