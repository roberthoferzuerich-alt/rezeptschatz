<?php

namespace App\Console\Commands;

use App\Models\Recipe;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class AssignTagsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipes:assign-tags 
                            {--tag= : Nur ein bestimmtes Schlagwort zuweisen (z.B. Schweizer Küche, Schnell (< 30 Min.), Vegetarisch)} 
                            {--dry-run : Zeigt nur eine Vorschau, ohne Änderungen in die Datenbank zu schreiben} 
                            {--user= : E-Mail oder ID des Benutzers}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analysiert alle Rezepte und weist automatisch passende Schlagwörter (Tags wie #Schnell, #Schweizer Küche, #Klassiker, #Vegetarisch, #Backofen etc.) zu.';

    /**
     * Standard-Definitionen für automatische Schlagwörter.
     */
    protected array $definitions = [
        'Schnell (< 30 Min.)' => [
            'color' => '#10b981',
            'icon' => 'timer',
            'type' => 'quick',
            'include' => ['schnell', 'blitz', 'express', 'ruckzuck', '30 min', '15 min', '20 min', 'quick'],
        ],
        'Schweizer Küche' => [
            'color' => '#dc2626',
            'icon' => 'flag',
            'type' => 'swiss',
            'include' => [
                'rösti', 'roesti', 'zopf', 'geschnetzelt', 'fondue', 'raclette', 'wähe', 'waehe', 
                'älplermagronen', 'alplermagronen', 'bündner', 'buendner', 'cervelat', 'appenzeller', 
                'gruyère', 'gruyere', 'sbrinz', 'tilsiter', 'emmental', 'vacherin', 'churer', 'berner', 
                'birchermüesli', 'birchermuesli', 'torta di pane', 'capuns', 'maluns', 'polenta', 
                'schlorzi', 'nidwalder', 'basler mehlsuppe', 'tirggel', 'biberli', 'cholera', 
                'ghacktes mit hörnli', 'pastetli', 'fleischvogel', 'suure mocke', 'voressen', 
                'riz casimir', 'betty bossi'
            ],
        ],
        'Klassiker' => [
            'color' => '#6366f1',
            'icon' => 'menu_book',
            'type' => 'classic',
            'include' => [
                'klassiker', 'klassisch', 'gulasch', 'cordon bleu', 'lasagne', 'bolognese', 
                'carbonara', 'risotto', 'tiramisu', 'schweinsbraten', 'rindsbraten', 'roulade', 
                'schnitzel', 'kartoffelstock', 'stroganoff', 'chili con carne', 'ratatouille', 
                'boeuf bourguignon', 'coq au vin', 'paella', 'quiche lorraine', 'currywurst', 
                'tafelspitz', 'kaiserschmarrn', 'apfelstrudel', 'hackbraten'
            ],
        ],
        'Vegetarisch' => [
            'color' => '#22c55e',
            'icon' => 'eco',
            'type' => 'vegetarian',
        ],
        'Backofen' => [
            'color' => '#ef4444',
            'icon' => 'oven_gen',
            'type' => 'oven',
            'include' => [
                'backofen', 'backen', 'vorgeheizt', 'ober-/unterhitze', 'oberhitze', 'unterhitze', 
                'umluft', 'gratinieren', 'wähe', 'waehe', 'auflauf', 'gratin', 'kuchen', 'torte', 
                'blech', 'pizza', 'quiche', 'fladen'
            ],
        ],
        'Gäste & Fest' => [
            'color' => '#f59e0b',
            'icon' => 'celebration',
            'type' => 'festive',
            'include' => [
                'fest', 'gäste', 'party', 'buffet', 'weihnacht', 'ostern', 'silvester', 
                'feiertag', 'gourmet', 'menü', 'einladung'
            ],
        ],
        'Einfach & Alltag' => [
            'color' => '#8ea92c',
            'icon' => 'restaurant',
            'type' => 'everyday',
            'include' => ['einfach', 'alltag', 'unkompliziert', 'familiengericht', 'kinderhit'],
        ],
        'Sommer' => [
            'color' => '#ec4899',
            'icon' => 'wb_sunny',
            'type' => 'summer',
            'include' => [
                'sommer', 'grill', 'grillieren', 'salat', 'beeren', 'erdbeer', 'himbeer', 
                'wassermelone', 'melon', 'gazpacho', 'eis', 'sorbet', 'erfrischend'
            ],
        ],
        'Winter & Deftig' => [
            'color' => '#8b5cf6',
            'icon' => 'ac_unit',
            'type' => 'winter',
            'include' => [
                'winter', 'eintopf', 'deftig', 'kürbis', 'kuerbis', 'marroni', 'kastanien', 
                'kohl', 'wirsing', 'sauerkraut', 'schmoren', 'schmortopf', 'fondue', 'raclette', 'suppe'
            ],
        ],
        'Low-Carb' => [
            'color' => '#06b6d4',
            'icon' => 'fitness_center',
            'type' => 'lowcarb',
            'include' => ['low-carb', 'low carb', 'kohlenhydratarm'],
        ],
        'Apéro & Fingerfood' => [
            'color' => '#eab308',
            'icon' => 'tapas',
            'type' => 'apero',
            'include' => [
                'apéro', 'apero', 'fingerfood', 'snack', 'häppchen', 'haeppchen', 'crostini', 
                'bruschetta', 'dip', 'spiesse', 'blätterteiggebäck', 'canapés', 'canapes'
            ],
        ],
        'Resteverwertung' => [
            'color' => '#f97316',
            'icon' => 'recycling',
            'type' => 'leftovers',
            'include' => ['reste', 'resteverwertung', 'verwertung', 'allerlei', 'vom vortag'],
        ],
    ];

    /**
     * Fleisch- und Fischbegriffe für Vegetarisch-Prüfung.
     */
    protected array $meatTerms = [
        'fleisch', 'rind', 'schwein', 'kalb', 'lamm', 'hackfleisch', 'speck', 'schinken', 
        'bratwurst', 'geschnetzelt', 'salami', 'landjäger', 'wienerli', 'fleischkäse', 
        'cordon bleu', 'brät', 'ossobuco', 'entrecôte', 'steak', 'gulasch', 'kotelett', 
        'wurst', 'bulette', 'frikadelle', 'poulet', 'hähnchen', 'huhn', 'trute', 'truthahn', 
        'geflügel', 'ente', 'pute', 'perlhuhn', 'wachtel', 'fisch', 'lachs', 'thunfisch', 
        'forelle', 'dorsch', 'kabeljau', 'zander', 'crevette', 'garnele', 'meeresfrüchte', 
        'pangasius', 'seelachs', 'dorade', 'felchen', 'scampi', 'tintenfisch', 'calamari'
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $singleTag = $this->option('tag');

        $userIdentifier = $this->option('user');
        $user = $userIdentifier 
            ? User::where('id', $userIdentifier)->orWhere('email', $userIdentifier)->first()
            : User::first();

        if (! $user) {
            $this->error('Kein Benutzer gefunden.');
            return self::FAILURE;
        }

        $definitionsToProcess = $this->definitions;
        if ($singleTag) {
            $filtered = [];
            foreach ($this->definitions as $name => $def) {
                if (mb_strtolower($name) === mb_strtolower(trim($singleTag))) {
                    $filtered[$name] = $def;
                }
            }
            if (empty($filtered)) {
                $this->error("Schlagwort '{$singleTag}' ist nicht in den Standard-Definitionen enthalten.");
                $this->line("Verfügbare Schlagwörter: " . implode(', ', array_keys($this->definitions)));
                return self::FAILURE;
            }
            $definitionsToProcess = $filtered;
        }

        $this->info("Analysiere Rezepte für Schlagwörter: {$user->name} ({$user->email})");
        if ($dryRun) {
            $this->comment("DRY-RUN AKTIV: Es werden keine Änderungen in die Datenbank geschrieben.\n");
        }

        // Alle Rezepte des Benutzers mit Tags, Zutaten und Schritten laden
        $recipes = Recipe::with(['tags', 'ingredients', 'steps', 'categories'])
            ->where('user_id', $user->id)
            ->get();

        $tableRows = [];
        $totalNewlyAssigned = 0;

        foreach ($definitionsToProcess as $tagName => $def) {
            $tag = Tag::where('user_id', $user->id)
                ->where('name', $tagName)
                ->first();

            $oldCount = $tag ? $tag->recipes->count() : 0;
            $matchedRecipes = [];
            $newlyAssignedCount = 0;

            foreach ($recipes as $recipe) {
                if ($this->recipeMatches($recipe, $tagName, $def)) {
                    $matchedRecipes[] = $recipe->id;
                    $alreadyHas = $tag && $recipe->tags->contains('id', $tag->id);
                    if (! $alreadyHas) {
                        $newlyAssignedCount++;
                    }
                }
            }

            if (! $dryRun && ! empty($matchedRecipes)) {
                if (! $tag) {
                    $tag = Tag::create([
                        'id' => (string) Str::uuid(),
                        'user_id' => $user->id,
                        'name' => $tagName,
                        'color' => $def['color'] ?? '#8ea92c',
                        'icon' => $def['icon'] ?? 'label',
                        'server_version' => 1,
                    ]);
                }

                // Batch-Synchronisation ohne bisherige Tags zu entfernen
                $tag->recipes()->syncWithoutDetaching($matchedRecipes);
            }

            $totalNewlyAssigned += $newlyAssignedCount;
            $newTotal = $oldCount + $newlyAssignedCount;

            $tableRows[] = [
                $tagName,
                $oldCount,
                count($matchedRecipes),
                '+' . $newlyAssignedCount,
                $newTotal,
            ];
        }

        $this->table(
            ['Schlagwort / Tag', 'Bisherige Rezepte', 'Passende Rezepte', 'Neu zugeordnet', 'Gesamt nach Zuweisung'],
            $tableRows
        );

        if ($dryRun) {
            $this->comment("Vorschau abgeschlossen. Insgesamt würden {$totalNewlyAssigned} neue Schlagwort-Zuweisungen vorgenommen werden.");
            $this->comment("Um die Änderungen zu speichern, führen Sie den Befehl ohne '--dry-run' aus:");
            $this->line("php artisan recipes:assign-tags");
        } else {
            $this->info("Erfolgreich {$totalNewlyAssigned} neue Schlagwort-Zuweisungen gespeichert!");
        }

        return self::SUCCESS;
    }

    /**
     * Prüft, ob ein Rezept zum Schlagwort passt.
     */
    protected function recipeMatches(Recipe $recipe, string $tagName, array $def): bool
    {
        $type = $def['type'] ?? '';
        $title = mb_strtolower($recipe->title);
        $desc = mb_strtolower($recipe->description ?? '');
        $prep = (int) ($recipe->prep_time_minutes ?? 0);
        $cook = (int) ($recipe->cook_time_minutes ?? 0);
        $totalTime = $prep + $cook;

        switch ($type) {
            case 'quick':
                if ($totalTime > 0 && $totalTime <= 30) {
                    return true;
                }
                return $this->containsAny($title, $def['include']) || $this->containsAny($desc, $def['include']);

            case 'swiss':
                if ($this->containsAny($title, $def['include']) || $this->containsAny($desc, $def['include'])) {
                    return true;
                }
                foreach ($recipe->ingredients as $ing) {
                    if ($this->containsAny(mb_strtolower($ing->name), $def['include'])) {
                        return true;
                    }
                }
                return false;

            case 'classic':
                return $this->containsAny($title, $def['include']) || $this->containsAny($desc, $def['include']);

            case 'vegetarian':
                // Wenn Rezept Zutaten hat, darf keines der Fleisch-/Fischbegriffe vorkommen
                if ($recipe->ingredients->isEmpty()) {
                    return false;
                }
                if ($this->containsAny($title, $this->meatTerms)) {
                    return false;
                }
                foreach ($recipe->ingredients as $ing) {
                    $name = mb_strtolower($ing->name);
                    // Ausnahme für Gemüsebouillon
                    if (str_contains($name, 'bouillon') || str_contains($name, 'brühe') || str_contains($name, 'fond')) {
                        if (str_contains($name, 'gemüse') || str_contains($name, 'vegi')) {
                            continue;
                        }
                    }
                    if ($this->containsAny($name, $this->meatTerms)) {
                        return false;
                    }
                }
                return true;

            case 'oven':
                if ($this->containsAny($title, $def['include']) || $this->containsAny($desc, $def['include'])) {
                    return true;
                }
                foreach ($recipe->steps as $step) {
                    if ($this->containsAny(mb_strtolower($step->instruction), $def['include'])) {
                        return true;
                    }
                }
                return false;

            case 'festive':
                if ($recipe->portions >= 6) {
                    return true;
                }
                return $this->containsAny($title, $def['include']) || $this->containsAny($desc, $def['include']);

            case 'everyday':
                if ($recipe->ingredients->count() >= 1 && $recipe->ingredients->count() <= 7 && $recipe->steps->count() <= 4 && $totalTime <= 45) {
                    return true;
                }
                return $this->containsAny($title, $def['include']) || $this->containsAny($desc, $def['include']);

            case 'summer':
            case 'winter':
            case 'lowcarb':
            case 'apero':
            case 'leftovers':
                if ($this->containsAny($title, $def['include']) || $this->containsAny($desc, $def['include'])) {
                    return true;
                }
                foreach ($recipe->ingredients as $ing) {
                    if ($this->containsAny(mb_strtolower($ing->name), $def['include'])) {
                        return true;
                    }
                }
                return false;

            default:
                if (isset($def['include'])) {
                    return $this->containsAny($title, $def['include']);
                }
                return false;
        }
    }

    protected function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }
        return false;
    }
}
