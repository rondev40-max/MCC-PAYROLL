<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MobileAuthTest extends TestCase
{
    // Without this the in-memory sqlite database is never migrated, so the very
    // first User::factory()->create() failed with "no such table: users".
    use RefreshDatabase;

    public function test_mobile_login_returns_token_for_valid_credentials(): void
    {
        $user = User::factory()->create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/mobile/login', [
            'email' => 'employee@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'token',
                'user' => ['id', 'name', 'email'],
            ]);

        $this->assertEquals($user->id, $response->json('user.id'));
        $this->assertNotEmpty($response->json('token'));
    }
}
