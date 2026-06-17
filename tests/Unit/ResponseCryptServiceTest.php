<?php

declare(strict_types=1);

namespace Sanjeev\ResponseCrypt\Tests\Unit;

use Orchestra\Testbench\TestCase;
use Sanjeev\ResponseCrypt\Services\ResponseCryptService;
use Sanjeev\ResponseCrypt\Exceptions\EncryptionFailedException;
use Sanjeev\ResponseCrypt\Exceptions\DecryptionFailedException;
use Illuminate\Http\Request;

class ResponseCryptServiceTest extends TestCase
{
    protected ResponseCryptService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $config = [
            'enabled' => true,
            'driver' => 'laravel',
            'key' => 'base64:' . base64_encode('test-key-32-characters-long!!'),
            'encrypt_response' => true,
            'decrypt_request' => true,
            'response_wrapper_key' => 'payload',
            'request_payload_key' => 'payload',
            'include_meta' => true,
            'encrypt_web_response' => false,
            'excluded_routes' => ['login', 'register'],
            'excluded_keys' => ['token_type'],
            'error_response' => ['status' => false, 'message' => 'Invalid encrypted payload.'],
            'cipher' => 'AES-256-CBC',
            'log_enabled' => false,
        ];

        $this->service = new ResponseCryptService($config);
    }

    public function test_can_encrypt_string(): void
    {
        $data = 'test string';
        $encrypted = $this->service->encrypt($data);

        $this->assertIsString($encrypted);
        $this->assertNotEquals($data, $encrypted);
    }

    public function test_can_encrypt_array(): void
    {
        $data = ['name' => 'John', 'age' => 30];
        $encrypted = $this->service->encrypt($data);

        $this->assertIsString($encrypted);
    }

    public function test_can_decrypt_encrypted_data(): void
    {
        $originalData = ['name' => 'John', 'age' => 30];
        $encrypted = $this->service->encrypt($originalData);
        $decrypted = $this->service->decrypt($encrypted);

        $this->assertEquals($originalData, $decrypted);
    }

    public function test_encrypt_array_returns_proper_structure(): void
    {
        $data = ['name' => 'John'];
        $result = $this->service->encryptArray($data);

        $this->assertArrayHasKey('payload', $result);
        $this->assertArrayHasKey('encrypted', $result);
        $this->assertArrayHasKey('meta', $result);
        $this->assertTrue($result['encrypted']);
    }

    public function test_is_enabled_returns_true(): void
    {
        $this->assertTrue($this->service->isEnabled());
    }

    public function test_should_skip_request_for_excluded_routes(): void
    {
        $request = Request::create('/login', 'POST');
        $this->assertTrue($this->service->shouldSkipRequest($request));
    }

    public function test_should_not_skip_request_for_non_excluded_routes(): void
    {
        $request = Request::create('/api/users', 'POST');
        $this->assertFalse($this->service->shouldSkipRequest($request));
    }
}
