<?php

declare(strict_types=1);

namespace Sanjeev\ResponseCrypt\Tests\Feature;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Route;
use Sanjeev\ResponseCrypt\ResponseCryptServiceProvider;
use Sanjeev\ResponseCrypt\Facades\ResponseCrypt;

class DecryptApiRequestTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [ResponseCryptServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('crypt.enabled', true);
        $app['config']->set('crypt.driver', 'hex');
        $app['config']->set('crypt.key', base64_encode(random_bytes(32)));
        $app['config']->set('crypt.iv', base64_encode(random_bytes(16)));
        $app['config']->set('crypt.decrypt_request', true);
        $app['config']->set('app.key', 'base64:' . base64_encode('test-key-32-characters-long!!'));
    }

    public function test_decrypts_incoming_request(): void
    {
        Route::middleware(['request.decrypt'])->post('/api/data', function () {
            return response()->json(['received' => request()->all()]);
        });

        $originalData = ['name' => 'John', 'email' => 'john@example.com'];
        $encrypted = ResponseCrypt::encrypt($originalData);

        $response = $this->postJson('/api/data', ['payload' => $encrypted]);

        $response->assertStatus(200);
        $data = $response->json('received');
        $this->assertEquals('John', $data['name']);
        $this->assertEquals('john@example.com', $data['email']);
    }

    public function test_returns_error_for_invalid_payload(): void
    {
        Route::middleware(['request.decrypt'])->post('/api/data', function () {
            return response()->json(['received' => request()->all()]);
        });

        $response = $this->postJson('/api/data', ['payload' => 'invalid-encrypted-data']);

        $response->assertStatus(422);
        $response->assertJson(['status' => false]);
    }

    public function test_skips_decryption_when_disabled(): void
    {
        config(['crypt.enabled' => false]);

        Route::middleware(['request.decrypt'])->post('/api/data', function () {
            return response()->json(['received' => request()->all()]);
        });

        $response = $this->postJson('/api/data', ['name' => 'John']);

        $response->assertStatus(200);
        $data = $response->json('received');
        $this->assertEquals('John', $data['name']);
    }

    public function test_combined_middleware_encrypts_and_decrypts(): void
    {
        Route::middleware(['api.crypt'])->post('/api/secure', function () {
            return response()->json([
                'message' => 'Success',
                'data' => request()->all(),
            ]);
        });

        $originalData = ['name' => 'Jane', 'role' => 'Admin'];
        $encrypted = ResponseCrypt::encrypt($originalData);

        $response = $this->postJson('/api/secure', ['payload' => $encrypted]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('encrypted', $data);
    }
}
