<?php

declare(strict_types=1);

namespace Sanjeev\ResponseCrypt\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Sanjeev\ResponseCrypt\Exceptions\DecryptionFailedException;
use Sanjeev\ResponseCrypt\Exceptions\EncryptionFailedException;

class ResponseCryptService
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Encrypt data and return encrypted string.
     */
    public function encrypt(mixed $data): string
    {
        try {
            $payload = is_string($data) ? $data : json_encode($data);

            if ($this->config['driver'] === 'openssl') {
                return $this->encryptWithOpenSSL($payload);
            }

            return Crypt::encryptString($payload);
        } catch (\Exception $e) {
            $this->logError('Encryption failed', $e);
            throw new EncryptionFailedException('Failed to encrypt data: ' . $e->getMessage());
        }
    }

    /**
     * Decrypt encrypted string and return original data.
     */
    public function decrypt(string $payload): mixed
    {
        try {
            if ($this->config['driver'] === 'openssl') {
                $decrypted = $this->decryptWithOpenSSL($payload);
            } else {
                $decrypted = Crypt::decryptString($payload);
            }

            $decoded = json_decode($decrypted, true);
            return $decoded !== null ? $decoded : $decrypted;
        } catch (\Exception $e) {
            $this->logError('Decryption failed', $e);
            throw new DecryptionFailedException('Failed to decrypt payload: ' . $e->getMessage());
        }
    }

    /**
     * Encrypt array and return encrypted data structure.
     */
    public function encryptArray(array $data): array
    {
        $encryptedPayload = $this->encrypt($data);

        $response = [
            $this->config['response_wrapper_key'] => $encryptedPayload,
        ];

        if ($this->config['include_meta']) {
            $response['encrypted'] = true;
            $response['meta'] = [
                'algorithm' => $this->config['driver'],
                'timestamp' => now()->toIso8601String(),
            ];
        }

        return $response;
    }

    /**
     * Decrypt array structure and return original data.
     */
    public function decryptArray(array $data): array
    {
        $payloadKey = $this->config['request_payload_key'];

        if (!isset($data[$payloadKey])) {
            return $data;
        }

        $decrypted = $this->decrypt($data[$payloadKey]);

        return is_array($decrypted) ? $decrypted : ['data' => $decrypted];
    }

    /**
     * Check if encryption is enabled.
     */
    public function isEnabled(): bool
    {
        return (bool) $this->config['enabled'];
    }

    /**
     * Check if request should be skipped from encryption/decryption.
     */
    public function shouldSkipRequest(Request $request): bool
    {
        $currentRoute = $request->path();
        $excludedRoutes = $this->config['excluded_routes'] ?? [];

        foreach ($excludedRoutes as $pattern) {
            if ($this->matchesPattern($currentRoute, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if response should be encrypted.
     */
    public function shouldEncryptResponse($response): bool
    {
        if (!$response instanceof \Illuminate\Http\JsonResponse) {
            return false;
        }

        if ($response->isRedirection()) {
            return false;
        }

        if ($response->getStatusCode() >= 500) {
            return false;
        }

        return true;
    }

    /**
     * Encrypt using OpenSSL AES-256-CBC.
     */
    protected function encryptWithOpenSSL(string $data): string
    {
        $key = $this->getEncryptionKey();
        $cipher = $this->config['cipher'] ?? 'AES-256-CBC';
        $ivLength = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);

        $encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            throw new EncryptionFailedException('OpenSSL encryption failed');
        }

        $result = base64_encode($iv . $encrypted);

        return $result;
    }

    /**
     * Decrypt using OpenSSL AES-256-CBC.
     */
    protected function decryptWithOpenSSL(string $data): string
    {
        $key = $this->getEncryptionKey();
        $cipher = $this->config['cipher'] ?? 'AES-256-CBC';
        $decoded = base64_decode($data, true);

        if ($decoded === false) {
            throw new DecryptionFailedException('Invalid base64 encoded data');
        }

        $ivLength = openssl_cipher_iv_length($cipher);
        $iv = substr($decoded, 0, $ivLength);
        $encrypted = substr($decoded, $ivLength);

        $decrypted = openssl_decrypt($encrypted, $cipher, $key, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            throw new DecryptionFailedException('OpenSSL decryption failed');
        }

        return $decrypted;
    }

    /**
     * Get encryption key from config.
     */
    protected function getEncryptionKey(): string
    {
        $key = $this->config['key'];

        if (empty($key)) {
            throw new \RuntimeException('Encryption key is not configured');
        }

        if (str_starts_with($key, 'base64:')) {
            return base64_decode(substr($key, 7));
        }

        return $key;
    }

    /**
     * Check if route matches pattern.
     */
    protected function matchesPattern(string $route, string $pattern): bool
    {
        $pattern = str_replace('/', '\/', $pattern);
        $pattern = str_replace('*', '.*', $pattern);

        return (bool) preg_match('/^' . $pattern . '$/i', $route);
    }

    /**
     * Log error safely without exposing sensitive data.
     */
    protected function logError(string $message, \Exception $e): void
    {
        if ($this->config['log_enabled'] ?? false) {
            Log::error($message, [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
