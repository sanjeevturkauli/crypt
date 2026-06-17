<?php

declare(strict_types=1);

namespace Sanjeev\ResponseCrypt\Tests\Feature;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Route;
use Sanjeev\ResponseCrypt\ResponseCryptServiceProvider;

class EncryptApiResponseTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [ResponseCryptServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('response-crypt.enabled', true);
        $app['config']->set('response-crypt.driver', 'laravel');
        $app['config']->set('response-crypt.encrypt_response', true);
        $app['config']->set('response-crypt.include_meta', true);
        $app['config']->set('app.key', 'base64:' . base64_encode('test-key-32-characters-long!!'));
    }

    public function test_encrypts_json_response(): void
    {
        Route::middleware(['response.encrypt'])->get('/test', function () {
            return response()->json(['message' => 'Hello World', 'status' => true]);
        });

        $response = $this->getJson('/test');

        $response->assertStatus(200);
        $response->assertJsonStructure(['payload', 'encrypted', 'meta']);
        $data = $response->json();
        $this->assertTrue($data['encrypted']);
    }

    public function test_does_not_encrypt_when_disabled(): void
    {
        config(['response-crypt.enabled' => false]);

        Route::middleware(['response.encrypt'])->get('/test', function () {
            return response()->json(['message' => 'Hello World']);
        });

        $response = $this->getJson('/test');
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Hello World']);
    }

    public function test_skips_excluded_routes(): void
    {
        config(['response-crypt.excluded_routes' => ['login']]);

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
        config(['response-crypt.excluded_keys' => ['token_type', 'expires_in']]);

        Route::middleware(['response.encrypt'])->get('/auth', function () {
            return response()->json([
                'token_type' => 'Bearer',
                'expires_in' => 3600,
                'access_token' => 'secret-token',
            ]);
        });

        $response = $this->getJson('/auth');
        $data = $response->json();

        $this->assertEquals('Bearer', $data['token_type']);
        $this->assertEquals(3600, $data['expires_in']);
        $this->assertArrayHasKey('payload', $data);
    }
}
