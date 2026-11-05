<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Console\Command;

class AssignCategoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipes:assign-category 
                            {category : Name der Kategorie, z.B. "Kartoffel"} 
                            {keyword? : Suchwort im Titel oder den Zutaten (Standard: wie Kategorie)} 
                            {--user= : E-Mail oder ID des Benutzers}
                            {--dry-run : Zeigt nur gefundene Rezepte, ohne zu speichern}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Weist eine Kategorie automatisch allen Rezepten zu, die ein Schlagwort im Titel, in der Beschreibung oder in den Zutaten enthalten';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $categoryName = trim($this->argument('category'));
        $keyword = trim($this->argument('keyword') ?? $categoryName);
        $dryRun = (bool) $this->option('dry-run');

        $userIdentifier = $this->option('user');
        $user = null;
        if ($userIdentifier) {
            $user = User::where('id', $userIdentifier)->orWhere('email', $userIdentifier)->first();
        } else {
            $user = User::first();
        }

        if (! $user) {
            $this->error('Kein Benutzer gefunden.');
            return self::FAILURE;
        }

        $this->info("Suche nach '{$keyword}' für Benutzer '{$user->name}'...");

        // Kategorie finden oder anlegen
        $category = Category::firstOrCreate(
            ['user_id' => $user->id, 'name' => $categoryName],
            ['color' => '#8ea92c']
        );

        $recipes = Recipe::where('user_id', $user->id)
            ->where(function ($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhereHas('ingredients', function ($iq) use ($keyword) {
                        $iq->where('name', 'like', "%{$keyword}%");
                    });
            })
            ->get();

        $this->info("Gefundene Rezepte mit '{$keyword}': {$recipes->count()}");
        $alreadyAssigned = 0;
        $newlyAssigned = 0;

        foreach ($recipes as $recipe) {
            $hasCategory = $recipe->categories()->where('categories.id', $category->id)->exists();
            if ($hasCategory) {
                $alreadyAssigned++;
            } else {
                $newlyAssigned++;
                if (! $dryRun) {
                    $recipe->categories()->syncWithoutDetaching([$category->id]);
                }
            }
        }

        $this->table(
            ['Metrik', 'Anzahl'],
            [
                ['Kategorie', $category->name],
                ['Suchbegriff', $keyword],
                ['Rezepte passend', $recipes->count()],
                ['Bereits in Kategorie', $alreadyAssigned],
                [$dryRun ? 'Würde neu zugeordnet werden' : 'Neu zugeordnet', $newlyAssigned],
            ]
        );

        if ($dryRun) {
            $this->comment('Dry-Run beendet. Keine Änderungen gespeichert.');
        } else {
            $this->info("Kategorie '{$category->name}' erfolgreich zu allen {$recipes->count()} Rezepten hinzugefügt!");
        }

        return self::SUCCESS;
    }
}
