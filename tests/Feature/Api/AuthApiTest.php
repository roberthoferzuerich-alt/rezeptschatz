<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_via_api_and_receives_sanctum_token(): void
    {
        $user = User::factory()->create([
            'email' => 'robert.hofer.zuerich@bluewin.ch',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'robert.hofer.zuerich@bluewin.ch',
            'password' => 'password',
            'device_name' => 'Samsung-Galaxy-S23',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'token',
                'user' => ['id', 'name', 'email', 'role'],
            ],
            'message',
        ]);
        $this->assertTrue($response->json('success'));
        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_login_with_wrong_password_fails(): void
    {
        User::factory()->create([
            'email' => 'robert.hofer.zuerich@bluewin.ch',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'robert.hofer.zuerich@bluewin.ch',
            'password' => 'falsches-passwort',
            'device_name' => 'Pixel-7',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'error' => [
                'code' => 'INVALID_CREDENTIALS',
            ],
        ]);
    }

    public function test_authenticated_user_can_access_me_and_logout(): void
    {
        $user = User::factory()->create(['name' => 'Robert Hofer', 'role' => 'admin']);
        $token = $user->createToken('Test-Device')->plainTextToken;

        // /me abfragen
        $meResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');

        $meResponse->assertStatus(200);
        $meResponse->assertJsonPath('data.user.name', 'Robert Hofer');

        // Logout
        $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout');

        $logoutResponse->assertStatus(200);
        $logoutResponse->assertJson(['success' => true]);

        $this->app['auth']->forgetGuards();

        // Nach Logout ist das Token ungültig
        $afterLogoutResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');

        $afterLogoutResponse->assertStatus(401);
    }
}
