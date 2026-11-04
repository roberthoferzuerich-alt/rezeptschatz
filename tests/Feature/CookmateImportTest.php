<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CookmateImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;
use ZipArchive;

class CookmateImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_import_recipes_from_cookmate_xml(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['role' => 'admin']);

        $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<cookbook version="46">
    <recipe>
        <title>Zürcher Geschnetzeltes</title>
        <preptime>15 min</preptime>
        <cooktime>20 min</cooktime>
        <quantity>4</quantity>
        <description>
            <li>Ein traditioneller Schweizer Klassiker.</li>
        </description>
        <ingredient>
            <li>600 g Kalbfleisch</li>
            <li>250 g Champignons</li>
            <li>1 dl Weisswein</li>
            <li>2 dl Rahm</li>
            <li>Salz und Pfeffer</li>
        </ingredient>
        <recipetext>
            <li>1. Kalbfleisch kurz und heiss anbraten.</li>
            <li>2. Champignons andünsten, mit Weisswein ablöschen und Rahm beigeben.</li>
        </recipetext>
        <category>Schweizer Klassiker</category>
        <category>Fleischgerichte</category>
        <rating>5</rating>
    </recipe>
</cookbook>
XML;

        $tempXml = tempnam(sys_get_temp_dir(), 'test_xml_') . '.xml';
        file_put_contents($tempXml, $xml);

        $service = new CookmateImportService();
        $stats = $service->import($tempXml, $user);
        @unlink($tempXml);

        $this->assertEquals(1, $stats['recipes_count']);
        $this->assertEquals(2, $stats['categories_count']);

        $this->assertDatabaseHas('recipes', [
            'user_id' => $user->id,
            'title' => 'Zürcher Geschnetzeltes',
            'portions' => 4,
            'prep_time_minutes' => 15,
            'cook_time_minutes' => 20,
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Schweizer Klassiker',
        ]);

        $this->assertDatabaseHas('recipe_ingredients', [
            'name' => 'Kalbfleisch',
            'amount_numeric' => 600,
            'unit' => 'g',
        ]);
    }

    public function test_can_import_recipes_from_mcb_zip_with_images(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['role' => 'admin']);

        $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<cookbook version="46">
    <recipe>
        <title>Rösti mit Spiegelei</title>
        <preptime>10 min</preptime>
        <cooktime>25 min</cooktime>
        <quantity>2</quantity>
        <ingredient>
            <li>500 g festkochende Kartoffeln</li>
            <li>2 Eier</li>
        </ingredient>
        <recipetext>
            <li>Kartoffeln reiben und goldbraun braten.</li>
        </recipetext>
        <imagepath>images/roesti.jpg</imagepath>
    </recipe>
</cookbook>
XML;

        $tempZip = tempnam(sys_get_temp_dir(), 'test_mcb_') . '.mcb';
        $zip = new ZipArchive();
        $zip->open($tempZip, ZipArchive::CREATE);
        $zip->addFromString('recipes.xml', $xml);
        $zip->addFromString('images/roesti.jpg', 'fake-image-bytes-12345');
        $zip->close();

        $service = new CookmateImportService();
        $stats = $service->import($tempZip, $user);
        @unlink($tempZip);

        $this->assertEquals(1, $stats['recipes_count']);
        $this->assertEquals(1, $stats['images_count']);

        $this->assertDatabaseHas('recipes', [
            'title' => 'Rösti mit Spiegelei',
            'portions' => 2,
        ]);
    }
}
