<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeStep;
use App\Models\User;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'robert.hofer.zuerich@bluewin.ch')->firstOrFail();

        $catPasta = Category::where('id', 'c1000000-0000-0000-0000-000000000001')->first();
        $catVeggie = Category::where('id', 'c1000000-0000-0000-0000-000000000002')->first();
        $catSwiss = Category::where('id', 'c1000000-0000-0000-0000-000000000003')->first();
        $catGratin = Category::where('id', 'c1000000-0000-0000-0000-000000000004')->first();
        $catFast = Category::where('id', 'c1000000-0000-0000-0000-000000000005')->first();
        $catSnack = Category::where('id', 'c1000000-0000-0000-0000-000000000006')->first();
        $catMeat = Category::where('id', 'c1000000-0000-0000-0000-000000000007')->first();

        // 1. Spinat-Nudelpfanne
        $r1 = Recipe::create([
            'id' => 'r1000000-0000-0000-0000-000000000001',
            'user_id' => $user->id,
            'title' => 'Spinat-Nudelpfanne',
            'description' => 'Schnelles, cremiges und gesundes Pfannengericht für jeden Tag.',
            'portions' => 4,
            'prep_time_minutes' => 15,
            'cook_time_minutes' => 15,
            'rest_time_minutes' => null,
            'notes' => 'Schmeckt auch mit gerösteten Pinienkernen oder Feta hervorragend.',
            'source_url' => 'https://cookmate.online/recipes/',
            'is_favorite' => true,
            'server_version' => 1,
        ]);
        $r1->categories()->sync(array_filter([$catPasta?->id, $catVeggie?->id, $catFast?->id]));

        $r1Ingredients = [
            ['sort_order' => 0, 'name' => 'Penne oder Fusilli', 'amount_numeric' => 400.0, 'unit' => 'g', 'amount_text' => null],
            ['sort_order' => 1, 'name' => 'Blattspinat (TK oder frisch)', 'amount_numeric' => 400.0, 'unit' => 'g', 'amount_text' => null],
            ['sort_order' => 2, 'name' => 'rote Zwiebeln', 'amount_numeric' => 2.0, 'unit' => 'Stück', 'amount_text' => 'mittelgross'],
            ['sort_order' => 3, 'name' => 'Knoblauchzehen', 'amount_numeric' => 4.0, 'unit' => 'Zehen', 'amount_text' => null],
            ['sort_order' => 4, 'name' => 'Pflanzenöl (neutral)', 'amount_numeric' => 2.0, 'unit' => 'EL', 'amount_text' => null],
            ['sort_order' => 5, 'name' => 'Paprikapulver (edelsüss)', 'amount_numeric' => 0.25, 'unit' => 'TL', 'amount_text' => null],
            ['sort_order' => 6, 'name' => 'Salz', 'amount_numeric' => null, 'unit' => null, 'amount_text' => 'nach Geschmack'],
            ['sort_order' => 7, 'name' => 'schwarzer Pfeffer aus der Mühle', 'amount_numeric' => null, 'unit' => null, 'amount_text' => 'nach Geschmack'],
        ];
        foreach ($r1Ingredients as $item) {
            RecipeIngredient::create(array_merge($item, ['recipe_id' => $r1->id]));
        }

        $r1Steps = [
            ['step_number' => 1, 'instruction' => 'Die Nudeln in reichlich kochendem Salzwasser nach Packungsanleitung al dente kochen.'],
            ['step_number' => 2, 'instruction' => 'Zwiebeln und Knoblauchzehen schälen und fein hacken. Pflanzenöl in einer geräumigen Pfanne erhitzen und beides glasig anschwitzen.'],
            ['step_number' => 3, 'instruction' => 'Den Spinat dazugeben und sanft erwärmen bzw. zusammenfallen lassen. Mit Salz, frisch gemahlenem Pfeffer und edelsüssem Paprikapulver kräftig abschmecken.'],
            ['step_number' => 4, 'instruction' => 'Die al dente gekochten Nudeln abgiessen, direkt in die Pfanne unter den Spinat heben und noch 1-2 Minuten durchziehen lassen. Heiss servieren.'],
        ];
        foreach ($r1Steps as $step) {
            RecipeStep::create(array_merge($step, ['recipe_id' => $r1->id]));
        }

        // 2. Ofenrösti mit Bündnerfleisch
        $r2 = Recipe::create([
            'id' => 'r1000000-0000-0000-0000-000000000002',
            'user_id' => $user->id,
            'title' => 'Ofenrösti mit Bündnerfleisch',
            'description' => 'Traditionelle Schweizer Rösti knusprig im Ofen gebacken, verfeinert mit feinstem Bündnerfleisch.',
            'portions' => 4,
            'prep_time_minutes' => 20,
            'cook_time_minutes' => 35,
            'rest_time_minutes' => null,
            'notes' => 'Am besten gelingt die Rösti mit festkochenden Kartoffeln, die am Vortag mit Schale gekocht wurden.',
            'source_url' => null,
            'is_favorite' => true,
            'server_version' => 1,
        ]);
        $r2->categories()->sync(array_filter([$catSwiss?->id, $catMeat?->id]));

        $r2Ingredients = [
            ['sort_order' => 0, 'name' => 'Kartoffeln (festkochend, am Vortag gekocht)', 'amount_numeric' => 800.0, 'unit' => 'g', 'amount_text' => null],
            ['sort_order' => 1, 'name' => 'Bündnerfleisch (in feine Streifen geschnitten)', 'amount_numeric' => 100.0, 'unit' => 'g', 'amount_text' => null],
            ['sort_order' => 2, 'name' => 'Schweizer Butter (zerlassen)', 'amount_numeric' => 50.0, 'unit' => 'g', 'amount_text' => null],
            ['sort_order' => 3, 'name' => 'Meersalz', 'amount_numeric' => 1.0, 'unit' => 'TL', 'amount_text' => null],
            ['sort_order' => 4, 'name' => 'schwarzer Pfeffer aus der Mühle', 'amount_numeric' => null, 'unit' => null, 'amount_text' => 'nach Geschmack'],
            ['sort_order' => 5, 'name' => 'frischer Schnittlauch', 'amount_numeric' => 2.0, 'unit' => 'EL', 'amount_text' => 'fein geschnitten'],
        ];
        foreach ($r2Ingredients as $item) {
            RecipeIngredient::create(array_merge($item, ['recipe_id' => $r2->id]));
        }

        $r2Steps = [
            ['step_number' => 1, 'instruction' => 'Backofen auf 200 °C Ober-/Unterhitze vorheizen. Ein Backblech mit Backpapier auslegen.'],
            ['step_number' => 2, 'instruction' => 'Die vorgekochten, kalten Kartoffeln schälen und mit der Röstiraffel grob in eine grosse Schüssel reiben.'],
            ['step_number' => 3, 'instruction' => 'Die geriebenen Kartoffeln mit Salz und Pfeffer würzen. Die Hälfte der flüssigen Butter und die Bündnerfleischstreifen vorsichtig untermengen.'],
            ['step_number' => 4, 'instruction' => 'Die Masse ca. 1.5 cm dick auf das Backblech drücken und die restliche Butter gleichmässig darauf verteilen.'],
            ['step_number' => 5, 'instruction' => 'Im Ofen auf mittlerer Schiene ca. 35 Minuten backen, bis die Oberfläche goldbraun und herrlich knusprig ist. Vor dem Anschneiden mit Schnittlauch bestreuen.'],
        ];
        foreach ($r2Steps as $step) {
            RecipeStep::create(array_merge($step, ['recipe_id' => $r2->id]));
        }

        // 3. Cajun-Kartoffelspalten aus dem Airfryer
        $r3 = Recipe::create([
            'id' => 'r1000000-0000-0000-0000-000000000003',
            'user_id' => $user->id,
            'title' => 'Cajun-Kartoffelspalten aus dem Airfryer',
            'description' => 'Aussen kross, innen zart – die perfekte pikante Beilage aus der Heissluftfritteuse.',
            'portions' => 2,
            'prep_time_minutes' => 10,
            'cook_time_minutes' => 20,
            'rest_time_minutes' => null,
            'notes' => 'Mit Sauerrahm- oder Knoblauch-Dip servieren.',
            'source_url' => null,
            'is_favorite' => false,
            'server_version' => 1,
        ]);
        $r3->categories()->sync(array_filter([$catSnack?->id, $catVeggie?->id, $catFast?->id]));

        $r3Ingredients = [
            ['sort_order' => 0, 'name' => 'Kartoffeln (vorwiegend festkochend)', 'amount_numeric' => 600.0, 'unit' => 'g', 'amount_text' => null],
            ['sort_order' => 1, 'name' => 'Olivenöl', 'amount_numeric' => 1.5, 'unit' => 'EL', 'amount_text' => null],
            ['sort_order' => 2, 'name' => 'geräuchertes Paprikapulver', 'amount_numeric' => 1.0, 'unit' => 'TL', 'amount_text' => null],
            ['sort_order' => 3, 'name' => 'edelsüsses Paprikapulver', 'amount_numeric' => 0.5, 'unit' => 'TL', 'amount_text' => null],
            ['sort_order' => 4, 'name' => 'Knoblauchpulver', 'amount_numeric' => 0.5, 'unit' => 'TL', 'amount_text' => null],
            ['sort_order' => 5, 'name' => 'Zwiebelpulver', 'amount_numeric' => 0.5, 'unit' => 'TL', 'amount_text' => null],
            ['sort_order' => 6, 'name' => 'Meersalz', 'amount_numeric' => 0.75, 'unit' => 'TL', 'amount_text' => null],
            ['sort_order' => 7, 'name' => 'Cayennepfeffer', 'amount_numeric' => null, 'unit' => null, 'amount_text' => '1 Prise'],
        ];
        foreach ($r3Ingredients as $item) {
            RecipeIngredient::create(array_merge($item, ['recipe_id' => $r3->id]));
        }

        $r3Steps = [
            ['step_number' => 1, 'instruction' => 'Kartoffeln gründlich waschen, trocken tupfen und ungeschält in gleichmässig dicke Spalten (Wedges) schneiden.'],
            ['step_number' => 2, 'instruction' => 'In einer Schüssel Olivenöl mit den Gewürzen verrühren. Kartoffelspalten zugeben und gründlich wenden, bis alles gut überzogen ist.'],
            ['step_number' => 3, 'instruction' => 'Airfryer auf 190 °C vorheizen. Spalten in den Garkorb geben und ca. 18-20 Minuten frittieren, nach 10 Minuten Korb einmal kräftig durchschütteln.'],
        ];
        foreach ($r3Steps as $step) {
            RecipeStep::create(array_merge($step, ['recipe_id' => $r3->id]));
        }

        // 4. Französischer Kartoffel-Hackfleisch-Auflauf (Hachis Parmentier)
        $r4 = Recipe::create([
            'id' => 'r1000000-0000-0000-0000-000000000004',
            'user_id' => $user->id,
            'title' => 'Französischer Kartoffel-Hackfleisch-Auflauf',
            'description' => 'Herzhafter Hachis Parmentier: Saftiges Rinderhackfleisch unter einer golden überbackenen Kartoffelpüree-Kruste.',
            'portions' => 4,
            'prep_time_minutes' => 25,
            'cook_time_minutes' => 30,
            'rest_time_minutes' => null,
            'notes' => 'Lässt sich hervorragend am Vortag vorbereiten und frisch überbacken.',
            'source_url' => null,
            'is_favorite' => false,
            'server_version' => 1,
        ]);
        $r4->categories()->sync(array_filter([$catGratin?->id, $catMeat?->id]));

        $r4Ingredients = [
            // Gruppe: Fleischfüllung
            ['sort_order' => 0, 'group_name' => 'Fleischfüllung', 'name' => 'Rinderhackfleisch', 'amount_numeric' => 500.0, 'unit' => 'g', 'amount_text' => null],
            ['sort_order' => 1, 'group_name' => 'Fleischfüllung', 'name' => 'Zwiebel', 'amount_numeric' => 1.0, 'unit' => 'Stück', 'amount_text' => 'fein gehackt'],
            ['sort_order' => 2, 'group_name' => 'Fleischfüllung', 'name' => 'Knoblauchzehen', 'amount_numeric' => 2.0, 'unit' => 'Zehen', 'amount_text' => 'zerdrückt'],
            ['sort_order' => 3, 'group_name' => 'Fleischfüllung', 'name' => 'Tomatenmark', 'amount_numeric' => 2.0, 'unit' => 'EL', 'amount_text' => null],
            ['sort_order' => 4, 'group_name' => 'Fleischfüllung', 'name' => 'Rindsbouillon', 'amount_numeric' => 100.0, 'unit' => 'ml', 'amount_text' => null],
            ['sort_order' => 5, 'group_name' => 'Fleischfüllung', 'name' => 'Thymianblättchen', 'amount_numeric' => 1.0, 'unit' => 'EL', 'amount_text' => 'frisch'],
            ['sort_order' => 6, 'group_name' => 'Fleischfüllung', 'name' => 'Salz und Pfeffer', 'amount_numeric' => null, 'unit' => null, 'amount_text' => 'nach Geschmack'],
            // Gruppe: Kartoffelpüree
            ['sort_order' => 7, 'group_name' => 'Kartoffelpüree', 'name' => 'Kartoffeln (mehligkochend)', 'amount_numeric' => 800.0, 'unit' => 'g', 'amount_text' => null],
            ['sort_order' => 8, 'group_name' => 'Kartoffelpüree', 'name' => 'Butter', 'amount_numeric' => 40.0, 'unit' => 'g', 'amount_text' => null],
            ['sort_order' => 9, 'group_name' => 'Kartoffelpüree', 'name' => 'Milch (warm)', 'amount_numeric' => 100.0, 'unit' => 'ml', 'amount_text' => null],
            ['sort_order' => 10, 'group_name' => 'Kartoffelpüree', 'name' => 'Muskatnuss', 'amount_numeric' => null, 'unit' => null, 'amount_text' => 'frisch gerieben'],
            ['sort_order' => 11, 'group_name' => 'Kartoffelpüree', 'name' => 'Gruyère AOP', 'amount_numeric' => 80.0, 'unit' => 'g', 'amount_text' => 'fein gerieben'],
        ];
        foreach ($r4Ingredients as $item) {
            RecipeIngredient::create(array_merge($item, ['recipe_id' => $r4->id]));
        }

        $r4Steps = [
            ['step_number' => 1, 'instruction' => 'Kartoffeln schälen, in Würfel schneiden und in Salzwasser ca. 20 Minuten weich kochen. Abgiessen und mit Butter, lauwarmer Milch und Muskatnuss zu cremigem Püree stampfen.'],
            ['step_number' => 2, 'instruction' => 'In einer Pfanne Zwiebel und Knoblauch andünsten. Hackfleisch zugeben und krümelig anbraten. Tomatenmark kurz mitrösten, mit Bouillon ablöschen, Thymian zufügen und 10 Minuten sanft köcheln lassen. Mit Salz und Pfeffer abschmecken.'],
            ['step_number' => 3, 'instruction' => 'Backofen auf 200 °C vorheizen. Eine Auflaufform fetten, Hackfleischmasse gleichmässig am Boden verteilen.'],
            ['step_number' => 4, 'instruction' => 'Das Kartoffelpüree darüberstreichen, mit einer Gabel ein Rillenmuster ziehen und den geriebenen Gruyère grosszügig darauf verteilen.'],
            ['step_number' => 5, 'instruction' => 'Im Backofen ca. 25-30 Minuten backen, bis der Käse goldbraun blubbert.'],
        ];
        foreach ($r4Steps as $step) {
            RecipeStep::create(array_merge($step, ['recipe_id' => $r4->id]));
        }

        // 5. Tikka-Masala-Nudeln
        $r5 = Recipe::create([
            'id' => 'r1000000-0000-0000-0000-000000000005',
            'user_id' => $user->id,
            'title' => 'Tikka-Masala-Nudeln',
            'description' => 'Aromatische Pasta-Kreation mit cremiger indischer Tikka-Masala-Sauce.',
            'portions' => 3,
            'prep_time_minutes' => 10,
            'cook_time_minutes' => 20,
            'rest_time_minutes' => null,
            'notes' => 'Passt ideal mit Tagliatelle oder breiten Bandnudeln.',
            'source_url' => null,
            'is_favorite' => false,
            'server_version' => 1,
        ]);
        $r5->categories()->sync(array_filter([$catPasta?->id, $catFast?->id]));

        $r5Ingredients = [
            ['sort_order' => 0, 'name' => 'Tagliatelle', 'amount_numeric' => 350.0, 'unit' => 'g', 'amount_text' => null],
            ['sort_order' => 1, 'name' => 'Tikka-Masala-Paste', 'amount_numeric' => 2.0, 'unit' => 'EL', 'amount_text' => null],
            ['sort_order' => 2, 'name' => 'gehackte Tomaten (Dose)', 'amount_numeric' => 400.0, 'unit' => 'g', 'amount_text' => null],
            ['sort_order' => 3, 'name' => 'Vollrahm oder Kokosmilch', 'amount_numeric' => 150.0, 'unit' => 'ml', 'amount_text' => null],
            ['sort_order' => 4, 'name' => 'Zwiebel', 'amount_numeric' => 1.0, 'unit' => 'Stück', 'amount_text' => 'gewürfelt'],
            ['sort_order' => 5, 'name' => 'frisches Basilikum oder Koriander', 'amount_numeric' => null, 'unit' => null, 'amount_text' => 'zum Garnieren'],
        ];
        foreach ($r5Ingredients as $item) {
            RecipeIngredient::create(array_merge($item, ['recipe_id' => $r5->id]));
        }

        $r5Steps = [
            ['step_number' => 1, 'instruction' => 'Tagliatelle in Salzwasser al dente kochen.'],
            ['step_number' => 2, 'instruction' => 'Zwiebel in etwas Öl anschwitzen. Tikka-Masala-Paste zufügen und 1 Minute unter Rühren anrösten.'],
            ['step_number' => 3, 'instruction' => 'Gehackte Tomaten und Rahm unterrühren und 10 Minuten sanft einköcheln lassen.'],
            ['step_number' => 4, 'instruction' => 'Nudeln direkt unter die aromatische Sauce heben und mit frischem Basilikum garnieren.'],
        ];
        foreach ($r5Steps as $step) {
            RecipeStep::create(array_merge($step, ['recipe_id' => $r5->id]));
        }
    }
}

