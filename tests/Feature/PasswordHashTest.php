<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\PasswordHash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordHashTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Insert a user straight through the query builder so the User model's
     * 'password' => 'hashed' cast does not reject a non-bcrypt hash.
     */
    private function insertUserWithRawHash(string $email, string $hash, string $role = 'employee'): int
    {
        return DB::table('users')->insertGetId([
            'name'       => 'Legacy User',
            'email'      => $email,
            'password'   => $hash,
            'role'       => $role,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function argon2idHash(string $plain): string
    {
        return password_hash($plain, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost'   => 4,
            'threads'     => 3,
        ]);
    }

    public function test_check_verifies_a_bcrypt_hash(): void
    {
        $hash = Hash::make('correct-horse-battery');

        $this->assertTrue(PasswordHash::check('correct-horse-battery', $hash));
        $this->assertFalse(PasswordHash::check('wrong-password', $hash));
    }

    public function test_check_never_throws_on_missing_or_malformed_hashes(): void
    {
        // This is the regression test for the reported production 500:
        // RuntimeException "This password does not use the Bcrypt algorithm."
        $this->assertFalse(PasswordHash::check(null, null));
        $this->assertFalse(PasswordHash::check('secret', ''));
        $this->assertFalse(PasswordHash::check('secret', null));
        $this->assertFalse(PasswordHash::check('', Hash::make('secret')));
        $this->assertFalse(PasswordHash::check('secret', 'not-a-hash-at-all'));
        $this->assertFalse(PasswordHash::check('secret', '$2y$truncated'));
    }

    public function test_algorithm_and_describe_report_the_stored_algorithm(): void
    {
        $this->assertSame('none', PasswordHash::algorithm(null));
        $this->assertSame('none', PasswordHash::algorithm(''));
        $this->assertSame('unknown', PasswordHash::algorithm('plain-text-password'));
        $this->assertSame('bcrypt', PasswordHash::algorithm(Hash::make('secret')));
        $this->assertStringContainsString('cost=', PasswordHash::describe(Hash::make('secret')));
    }

    public function test_argon2id_password_verifies_and_upgrades_to_bcrypt(): void
    {
        if (!defined('PASSWORD_ARGON2ID')) {
            $this->markTestSkipped('This PHP build has no argon2 support.');
        }

        $id = $this->insertUserWithRawHash('legacy@example.com', $this->argon2idHash('legacy-secret-1234'));

        $user = User::find($id);
        $this->assertSame('argon2id', PasswordHash::algorithm($user->password));

        $this->assertTrue(PasswordHash::checkAndUpgrade('legacy-secret-1234', $user));

        $stored = DB::table('users')->where('id', $id)->value('password');
        $this->assertSame('bcrypt', PasswordHash::algorithm($stored));
        $this->assertTrue(PasswordHash::check('legacy-secret-1234', $stored));
    }

    public function test_login_with_a_non_bcrypt_hash_and_wrong_password_is_not_a_server_error(): void
    {
        // Must hold whether or not this PHP build can read argon2: with support
        // the password simply does not match, without it the hash is
        // unverifiable. Either way the user gets the login form back, not a 500.
        $hash = defined('PASSWORD_ARGON2ID')
            ? $this->argon2idHash('legacy-secret-1234')
            : '$argon2id$v=19$m=65536,t=4,p=3$S2lzNEh4Zy5JcXlZZzVGVQ$GLJ4/6uZd4lEnWeDXSa0ZUCOpESf6kWbnbTfm7czx5s';

        $this->insertUserWithRawHash('legacy@example.com', $hash);

        $response = $this->post('/', [
            'email'     => 'legacy@example.com',
            'password'  => 'definitely-wrong',
            'user_type' => 'employee',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_mobile_login_with_a_non_bcrypt_hash_returns_401_not_500(): void
    {
        $hash = defined('PASSWORD_ARGON2ID')
            ? $this->argon2idHash('legacy-secret-1234')
            : '$argon2id$v=19$m=65536,t=4,p=3$S2lzNEh4Zy5JcXlZZzVGVQ$GLJ4/6uZd4lEnWeDXSa0ZUCOpESf6kWbnbTfm7czx5s';

        $this->insertUserWithRawHash('legacy@example.com', $hash);

        $response = $this->postJson('/api/mobile/login', [
            'email'    => 'legacy@example.com',
            'password' => 'definitely-wrong',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid credentials']);
    }

    public function test_logging_in_does_not_rewrite_the_stored_password(): void
    {
        // Regression test for the removed AppServiceProvider Login listener,
        // which rehashed getAuthPassword() — the stored hash rather than the
        // plaintext — and permanently locked accounts out.
        $hash = password_hash('legacy-secret-1234', PASSWORD_BCRYPT, ['cost' => 6]);
        $id   = $this->insertUserWithRawHash('stale-cost@example.com', $hash);

        $this->assertTrue(
            PasswordHash::needsUpgrade($hash),
            'Fixture must use a cost that differs from the configured default.'
        );

        Auth::login(User::find($id));

        $this->assertSame($hash, DB::table('users')->where('id', $id)->value('password'));
    }
}
