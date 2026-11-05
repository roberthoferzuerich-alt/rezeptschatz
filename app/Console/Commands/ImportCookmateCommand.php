<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\CookmateImportService;
use Illuminate\Console\Command;

class ImportCookmateCommand extends Command
{
    protected $signature = 'recipes:import-cookmate 
                            {file? : Pfad zur .mcb oder .xml Datei} 
                            {--user= : E-Mail des Benutzers (Standard: robert.hofer.zuerich@bluewin.ch)}';

    protected $description = 'Importiert Rezepte und Bilder aus einem COOKmate .mcb oder .xml Archiv';

    public function handle(CookmateImportService $service): int
    {
        $filePath = $this->argument('file') ?: public_path('download/mycookbookrecipes.mcb');

        if (!file_exists($filePath)) {
            $this->error("Datei nicht gefunden: {$filePath}");
            return self::FAILURE;
        }

        $email = $this->option('user') ?: 'robert.hofer.zuerich@bluewin.ch';
        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::first();
        }

        if (!$user) {
            $this->error("Kein Benutzer in der Datenbank gefunden.");
            return self::FAILURE;
        }

        $this->info("Starte COOKmate-Import...");
        $this->info("Datei: {$filePath} (" . round(filesize($filePath) / 1024 / 1024, 2) . " MB)");
        $this->info("Benutzer: {$user->name} ({$user->email})");

        $startTime = microtime(true);

        try {
            $stats = $service->import($filePath, $user);

            $duration = round(microtime(true) - $startTime, 2);

            $this->newLine();
            $this->info("=========================================");
            $this->info(" Import erfolgreich abgeschlossen! ({$duration}s) ");
            $this->info("=========================================");
            $this->table(
                ['Kategorie', 'Anzahl'],
                [
                    ['Rezepte importiert', $stats['recipes_count']],
                    ['Bilder verknüpft', $stats['images_count']],
                    ['Neue Kategorien', $stats['categories_count']],
                    ['Übersprungen (ohne Titel)', $stats['skipped_count']],
                ]
            );

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Fehler beim Import: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}

