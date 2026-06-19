<?php

declare(strict_types=1);

namespace SecureCrypto\Encryption\Tests\Feature;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Route;
use SecureCrypto\Encryption\ResponseCryptServiceProvider;
use SecureCrypto\Encryption\Facades\ResponseCrypt;

class EncryptApiResponseTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [ResponseCryptServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('secure-crypto.enabled', true);
        $app['config']->set('secure-crypto.driver', 'hex');
        $app['config']->set('secure-crypto.key', base64_encode(random_bytes(32)));
        $app['config']->set('secure-crypto.iv', base64_encode(random_bytes(16)));
        $app['config']->set('secure-crypto.encrypt_response', true);
        $app['config']->set('secure-crypto.include_meta', true);
        $app['config']->set('app.key', 'base64:' . base64_encode('test-key-32-characters-long!!'));
    }

    public function test_encrypts_json_response(): void
    {
        Route::middleware(['response.encrypt'])->get('/test', function () {
            return response()->json(['message' => 'Hello World', 'status' => true]);
        });

        $response = $this->getJson('/test');

        $response->assertStatus(200);
        $data = $response->json();
        
        // In minimal mode, response is just the encrypted string
        $this->assertIsString($data);
        $this->assertNotEmpty($data);
        
        // Should be able to decrypt it
        $decrypted = ResponseCrypt::decrypt($data);
        $this->assertArrayHasKey('message', $decrypted);
        $this->assertEquals('Hello World', $decrypted['message']);
    }

    public function test_does_not_encrypt_when_disabled(): void
    {
        config(['secure-crypto.enabled' => false]);

        Route::middleware(['response.encrypt'])->get('/test', function () {
            return response()->json(['message' => 'Hello World']);
        });

        $response = $this->getJson('/test');
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Hello World']);
    }

    public function test_skips_excluded_routes(): void
    {
        config(['secure-crypto.excluded_routes' => ['login']]);

        Route::middleware(['response.encrypt'])->post('/login', function () {
            return response()->json(['token' => 'test-token']);
        });

        $response = $this->postJson('/login');
        $response->assertStatus(200);
        $response->assertJson(['token' => 'test-token']);
    }

    public function test_does_not_encrypt_redirect_responses(): void
    {
        Route::middleware(['response.encrypt'])->get('/redirect', function () {
            return redirect('/home');
        });

        $response = $this->get('/redirect');
        $response->assertRedirect('/home');
    }

    public function test_preserves_excluded_keys(): void
    {
        config(['secure-crypto.excluded_keys' => ['token_type', 'expires_in']]);

        Route::middleware(['response.encrypt'])->get('/auth', function () {
            return response()->json([
                'token_type' => 'Bearer',
                'expires_in' => 3600,
                'access_token' => 'secret-token',
            ]);
        });

        $response = $this->getJson('/auth');
        $data = $response->json();

        // In minimal mode with excluded keys, response should still be string (encrypted part only)
        $this->assertIsString($data);
        
        // Decrypt and verify only access_token was encrypted
        $decrypted = ResponseCrypt::decrypt($data);
        $this->assertArrayHasKey('access_token', $decrypted);
        $this->assertEquals('secret-token', $decrypted['access_token']);
        
        // Excluded keys should not be in encrypted payload
        $this->assertArrayNotHasKey('token_type', $decrypted);
        $this->assertArrayNotHasKey('expires_in', $decrypted);
    }
}
