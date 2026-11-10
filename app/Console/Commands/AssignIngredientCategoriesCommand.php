<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Console\Command;

class AssignIngredientCategoriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipes:assign-ingredient-categories 
                            {--category= : Nur eine bestimmte Zutat/Kategorie zuweisen (z.B. Fleisch, Geflügel, Fisch, Teigwaren)} 
                            {--dry-run : Zeigt nur eine Vorschau, ohne Änderungen in die Datenbank zu schreiben} 
                            {--user= : E-Mail oder ID des Benutzers}
                            {--recipe= : ID oder UUID eines einzelnen Rezepts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Weist anhand der Zutaten und Titel automatisch passende Hauptzutaten-Kategorien (Fleisch, Geflügel, Fisch, Teigwaren, Reis, Gemüse, Pilze etc.) zu.';

    /**
     * Definitions of ingredient categories with matching rules and exclusion patterns.
     */
    protected array $definitions = [
        'Fleisch' => [
            'color' => '#b91c1c',
            'include' => [
                'rind', 'schwein', 'kalb', 'lamm', 'hackfleisch', 'speck', 'schinken', 
                'bratwurst', 'geschnetzelt', 'salami', 'landjäger', 'wienerli', 'fleischkäse', 
                'cordon bleu', 'brät', 'ossobuco', 'entrecôte', 'steak', 'gulasch', 'kotelett', 
                'fleisch', 'wurst', 'bulette', 'frikadelle'
            ],
            'exclude' => ['bouillon', 'brühe', 'fond', 'fisch', 'poulet', 'hähnchen', 'soja', 'quorn', 'vegi', 'vegetarisch'],
        ],
        'Geflügel' => [
            'color' => '#d97706',
            'include' => ['poulet', 'hähnchen', 'huhn', 'trute', 'truthahn', 'geflügel', 'ente', 'pute', 'perlhuhn', 'wachtel'],
            'exclude' => ['bouillon', 'brühe', 'fond'],
        ],
        'Pouletbrust' => [
            'color' => '#d97706',
            'include' => ['pouletbrust', 'pouletbrüst', 'hähnchenbrust', 'hähnchenbrüst'],
            'exclude' => [],
        ],
        'Fisch' => [
            'color' => '#0284c7',
            'include' => [
                'fisch', 'lachs', 'thunfisch', 'forelle', 'dorsch', 'kabeljau', 'zander', 
                'crevette', 'garnele', 'meeresfrüchte', 'pangasius', 'seelachs', 'dorade', 
                'felchen', 'karpfen', 'hecht', 'scholle', 'sardelle', 'sardine', 
                'jakobsmuschel', 'miesmuschel', 'scampi', 'tintenfisch', 'calamari'
            ],
            'exclude' => ['fischsauce'],
        ],
        'Lachs' => [
            'color' => '#f97316',
            'include' => ['lachs'],
            'exclude' => ['seelachs'],
        ],
        'Thunfisch' => [
            'color' => '#0369a1',
            'include' => ['thunfisch'],
            'exclude' => [],
        ],
        'Crevetten' => [
            'color' => '#ec4899',
            'include' => ['crevette', 'garnele', 'scampi', 'prawn'],
            'exclude' => [],
        ],
        'Teigwaren' => [
            'color' => '#eab308',
            'include' => [
                'teigwaren', 'nudel', 'pasta', 'spaghetti', 'penne', 'tagliatelle', 
                'makkaroni', 'fusilli', 'spätzli', 'knöpfli', 'lasagne', 'tortellini', 
                'ravioli', 'farfalle', 'fettuccine', 'linguine', 'rigatoni', 'gnocchi', 
                'cannelloni', 'orecchiette'
            ],
            'exclude' => [],
        ],
        'Spätzli' => [
            'color' => '#ca8a04',
            'include' => ['spätzli', 'knöpfli'],
            'exclude' => [],
        ],
        'Reis' => [
            'color' => '#a8a29e',
            'include' => ['reis', 'risotto', 'basmati', 'jasminreis', 'wildreis'],
            'exclude' => ['reismehl', 'reisessig', 'reissirup', 'reispapier'],
        ],
        'Risotto' => [
            'color' => '#854d0e',
            'include' => ['risotto', 'arborio', 'carnaroli'],
            'exclude' => [],
        ],
        'Pilz' => [
            'color' => '#78350f',
            'include' => [
                'pilz', 'champignon', 'steinpilz', 'eierschwamm', 'morchel', 
                'kräuterseitling', 'shiitake', 'pfifferling', 'trüffel'
            ],
            'exclude' => [],
        ],
        'Gemüse' => [
            'color' => '#16a34a',
            'include' => [
                'gemüse', 'rüebli', 'karotte', 'zucchetti', 'zucchini', 'peperoni', 
                'spinat', 'broccoli', 'brokkoli', 'blumenkohl', 'kürbis', 'lauch', 
                'sellerie', 'fenchel', 'spargel', 'aubergine', 'wirsing', 'kohl', 
                'kohlrabi', 'rosenkohl', 'erbsen', 'bohne', 'mangold'
            ],
            'exclude' => ['bouillon', 'brühe', 'fond'],
        ],
        'Kürbis' => [
            'color' => '#ea580c',
            'include' => ['kürbis', 'butternut', 'hokkaido'],
            'exclude' => ['kürbiskerne', 'kürbiskernöl'],
        ],
        'Spargel' => [
            'color' => '#65a30d',
            'include' => ['spargel'],
            'exclude' => [],
        ],
        'Tomaten' => [
            'color' => '#dc2626',
            'include' => ['tomate', 'pelati'],
            'exclude' => ['tomatenpüree', 'tomatenmark'],
        ],
        'Peperoni' => [
            'color' => '#e11d48',
            'include' => ['peperoni', 'paprika'],
            'exclude' => ['paprikapulver'],
        ],
        'Kartoffel' => [
            'color' => '#8ea92c',
            'include' => ['kartoffel', 'kartoffeln', 'gschwellti', 'härdöpfel', 'patate'],
            'exclude' => ['kartoffelstärke', 'kartoffelmehl'],
        ],
        'Käse' => [
            'color' => '#f59e0b',
            'include' => [
                'käse', 'gruyère', 'parmesan', 'mozzarella', 'emmentaler', 'appenzeller', 
                'gorgonzola', 'feta', 'ricotta', 'mascarpone', 'sbrinz', 'cheddar', 
                'raclette', 'fondue'
            ],
            'exclude' => [],
        ],
        'Ei' => [
            'color' => '#facc15',
            'regex' => ['/\b(ei|eier|eigelb|eiweiss|spiegelei|spiegeleier|rührei|omelette)\b/iu'],
            'exclude' => ['eisbergsalat', 'eierschwamm', 'eierschwämme'],
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $singleCategory = $this->option('category');

        $userIdentifier = $this->option('user');
        $user = $userIdentifier 
            ? User::where('id', $userIdentifier)->orWhere('email', $userIdentifier)->first()
            : User::first();

        if (! $user) {
            $this->error('Kein Benutzer gefunden.');
            return self::FAILURE;
        }

        $definitionsToProcess = $this->definitions;
        if ($singleCategory) {
            $filtered = [];
            foreach ($this->definitions as $name => $def) {
                if (mb_strtolower($name) === mb_strtolower(trim($singleCategory))) {
                    $filtered[$name] = $def;
                }
            }
            if (empty($filtered)) {
                $this->error("Kategorie '{$singleCategory}' ist nicht in den vordefinierten Zutatengruppen enthalten.");
                $this->line("Verfügbare Kategorien: " . implode(', ', array_keys($this->definitions)));
                return self::FAILURE;
            }
            $definitionsToProcess = $filtered;
        }

        $this->info("Analysiere Rezepte für Benutzer: {$user->name} ({$user->email})");
        if ($dryRun) {
            $this->comment("DRY-RUN AKTIV: Es werden keine Änderungen in die Datenbank geschrieben.\n");
        }

        // Rezepte des Benutzers laden (optional gefiltert nach Einzelrezept)
        $recipeId = $this->option('recipe');
        $recipesQuery = Recipe::with(['ingredients', 'categories'])
            ->where('user_id', $user->id);

        if ($recipeId) {
            $recipesQuery->where('id', $recipeId);
        }

        $recipes = $recipesQuery->get();

        $tableRows = [];
        $totalNewlyAssigned = 0;

        foreach ($definitionsToProcess as $catName => $def) {
            // Kategorie finden oder bei echtem Durchlauf erstellen
            $category = Category::where('user_id', $user->id)
                ->where('name', $catName)
                ->first();

            $oldCount = $category ? $category->recipes->count() : 0;
            $matchedRecipes = [];
            $newlyAssignedCount = 0;

            foreach ($recipes as $recipe) {
                if ($this->recipeMatches($recipe, $def)) {
                    $matchedRecipes[] = $recipe->id;
                    $alreadyHas = $category && $recipe->categories->contains('id', $category->id);
                    if (! $alreadyHas) {
                        $newlyAssignedCount++;
                    }
                }
            }

            if (! $dryRun && ! empty($matchedRecipes)) {
                if (! $category) {
                    $category = Category::create([
                        'user_id' => $user->id,
                        'name' => $catName,
                        'color' => $def['color'] ?? '#8ea92c',
                    ]);
                }

                // Batch-Synchronisation ohne bisherige Kategorien zu entfernen
                $category->recipes()->syncWithoutDetaching($matchedRecipes);
            }

            $totalNewlyAssigned += $newlyAssignedCount;
            $newTotal = $oldCount + $newlyAssignedCount;

            $tableRows[] = [
                $catName,
                $oldCount,
                count($matchedRecipes),
                '+' . $newlyAssignedCount,
                $newTotal,
            ];
        }

        $this->table(
            ['Kategorie', 'Bisherige Rezepte', 'Gefunden anhand Zutaten', 'Neu zugeordnet', 'Gesamt nach Zuweisung'],
            $tableRows
        );

        if ($dryRun) {
            $this->comment("Vorschau abgeschlossen. Insgesamt würden {$totalNewlyAssigned} Zuweisungen vorgenommen werden.");
            $this->comment("Um die Änderungen zu speichern, führen Sie den Befehl ohne '--dry-run' aus:");
            $this->line("php artisan recipes:assign-ingredient-categories");
        } else {
            $this->info("Erfolgreich {$totalNewlyAssigned} neue Kategorie-Zuweisungen gespeichert!");
        }

        return self::SUCCESS;
    }

    /**
     * Prüft, ob ein Rezept anhand seines Titels oder seiner Zutaten zur Kategorie passt.
     */
    protected function recipeMatches(Recipe $recipe, array $def): bool
    {
        $title = mb_strtolower($recipe->title);

        // 1. Titel-Prüfung
        if (isset($def['regex'])) {
            foreach ($def['regex'] as $regex) {
                if (preg_match($regex, $title)) {
                    return true;
                }
            }
        } elseif (isset($def['include'])) {
            foreach ($def['include'] as $term) {
                if (str_contains($title, $term)) {
                    return true;
                }
            }
        }

        // 2. Zutaten-Prüfung
        foreach ($recipe->ingredients as $ingredient) {
            $name = mb_strtolower($ingredient->name);

            // Ausschlüsse prüfen (z.B. Tomatenmark, Gemüsebouillon etc.)
            if (! empty($def['exclude'])) {
                $isExcluded = false;
                foreach ($def['exclude'] as $excludeTerm) {
                    if (str_contains($name, $excludeTerm)) {
                        $isExcluded = true;
                        break;
                    }
                }
                if ($isExcluded) {
                    continue;
                }
            }

            // Inklusionen prüfen
            if (isset($def['regex'])) {
                foreach ($def['regex'] as $regex) {
                    if (preg_match($regex, $name)) {
                        return true;
                    }
                }
            } elseif (isset($def['include'])) {
                foreach ($def['include'] as $term) {
                    if (str_contains($name, $term)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
