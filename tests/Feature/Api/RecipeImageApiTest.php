<?php

namespace Tests\Feature\Api;

use App\Models\Recipe;
use App\Models\User;
use App\Services\RecipeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RecipeImageApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_and_stream_recipe_image(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => 'admin']);
        $token = $user->createToken('Test-Device')->plainTextToken;

        $recipeService = new RecipeService();
        $recipe = $recipeService->createRecipe($user, ['title' => 'Rezept mit Bild']);

        $file = UploadedFile::fake()->image('rezept.jpg', 800, 600);

        // Upload via API
        $uploadResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/recipes/{$recipe->id}/image", [
                'image' => $file,
            ]);

        $uploadResponse->assertStatus(200);
        $uploadResponse->assertJson(['success' => true]);
        $this->assertNotEmpty($uploadResponse->json('data.image_hash'));

        $recipe->refresh();
        $this->assertNotNull($recipe->image_path);
        Storage::disk('local')->assertExists($recipe->image_path);

        // Download via API
        $downloadResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->get("/api/v1/recipes/{$recipe->id}/image");

        $downloadResponse->assertStatus(200);
        $downloadResponse->assertHeader('Cache-Control', 'max-age=86400, must-revalidate, private');
    }

    public function test_unauthenticated_request_cannot_download_recipe_image(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $recipeService = new RecipeService();
        $recipe = $recipeService->createRecipe($user, ['title' => 'Geheimes Rezept']);

        $file = UploadedFile::fake()->image('geheim.jpg');
        $recipeService->saveRecipeImage($recipe, $file);

        $response = $this->getJson("/api/v1/recipes/{$recipe->id}/image");
        $response->assertStatus(401);
    }
}
