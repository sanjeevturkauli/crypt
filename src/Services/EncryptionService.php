<?php

declare(strict_types=1);

namespace SecureCrypto\Encryption\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use SecureCrypto\Encryption\Exceptions\DecryptionFailedException;
use SecureCrypto\Encryption\Exceptions\EncryptionFailedException;
use SecureCrypto\Encryption\Contracts\EncryptionDriverInterface;
use SecureCrypto\Encryption\Drivers\{HexEncryptionDriver, LaravelEncryptionDriver, OpenSSLDriver};

class EncryptionService
{
    protected array $config;
    protected EncryptionDriverInterface $driver;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->driver = $this->resolveDriver();
    }

    /**
     * Encrypt data using the configured driver.
     */
    public function encrypt(mixed $data): string
    {
        try {
            $payload = $this->normalizePayload($data);
            return $this->driver->encrypt($payload);
        } catch (\Exception $e) {
            $this->logError('Encryption failed', $e);
            throw new EncryptionFailedException(
                'Failed to encrypt data: ' . $e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * Decrypt encrypted string using the configured driver.
     */
    public function decrypt(string $payload): mixed
    {
        try {
            $decrypted = $this->driver->decrypt($payload);
            return $this->denormalizePayload($decrypted);
        } catch (\Exception $e) {
            $this->logError('Decryption failed', $e);
            throw new DecryptionFailedException(
                'Failed to decrypt payload: ' . $e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * Encrypt array and return structured response.
     */
    public function encryptArray(array $data): array
    {
        $encryptedPayload = $this->encrypt($data);

        return array_filter([
            'encrypted' => $this->shouldIncludeMeta(),
            $this->getResponseWrapperKey() => $encryptedPayload,
            'meta' => $this->shouldIncludeMeta() ? $this->buildMetadata() : null,
        ]);
    }

    /**
     * Decrypt array structure and return original data.
     */
    public function decryptArray(array $data): array
    {
        $payloadKey = $this->getRequestPayloadKey();

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
        return (bool) ($this->config['enabled'] ?? true);
    }

    /**
     * Check if request should be skipped.
     */
    public function shouldSkipRequest(Request $request): bool
    {
        $currentRoute = $request->path();
        $excludedRoutes = $this->config['excluded_routes'] ?? [];

        return collect($excludedRoutes)
            ->contains(fn($pattern) => $this->matchesPattern($currentRoute, $pattern));
    }

    /**
     * Check if response should be encrypted.
     */
    public function shouldEncryptResponse($response): bool
    {
        return $response instanceof \Illuminate\Http\JsonResponse
            && !$response->isRedirection()
            && $response->getStatusCode() < 500;
    }

    /**
     * Resolve the encryption driver based on configuration.
     */
    protected function resolveDriver(): EncryptionDriverInterface
    {
        return match($this->config['driver'] ?? 'hex') {
            'hex' => new HexEncryptionDriver($this->config),
            'openssl', 'openssl_fixed' => new OpenSSLDriver($this->config),
            'laravel' => new LaravelEncryptionDriver($this->config),
            default => new HexEncryptionDriver($this->config),
        };
    }

    /**
     * Normalize payload for encryption.
     */
    protected function normalizePayload(mixed $data): string
    {
        return is_string($data) ? $data : json_encode($data, JSON_THROW_ON_ERROR);
    }

    /**
     * Denormalize decrypted payload.
     */
    protected function denormalizePayload(string $decrypted): mixed
    {
        $decoded = json_decode($decrypted, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $decrypted;
    }

    /**
     * Build metadata for encrypted response.
     */
    protected function buildMetadata(): array
    {
        return [
            'algorithm' => $this->config['driver'] ?? 'hex',
            'cipher' => $this->config['cipher'] ?? 'AES-256-CBC',
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get response wrapper key from config.
     */
    protected function getResponseWrapperKey(): string
    {
        return $this->config['response_wrapper_key'] ?? 'payload';
    }

    /**
     * Get request payload key from config.
     */
    protected function getRequestPayloadKey(): string
    {
        return $this->config['request_payload_key'] ?? 'payload';
    }

    /**
     * Check if metadata should be included.
     */
    protected function shouldIncludeMeta(): bool
    {
        return (bool) ($this->config['include_meta'] ?? true);
    }

    /**
     * Check if route matches pattern using wildcards.
     */
    protected function matchesPattern(string $route, string $pattern): bool
    {
        $pattern = '#^' . str_replace(['/', '*'], ['\/', '.*'], $pattern) . '$#i';
        return (bool) preg_match($pattern, $route);
    }

    /**
     * Log error safely without exposing sensitive data.
     */
    protected function logError(string $message, \Exception $e): void
    {
        if ($this->config['log_enabled'] ?? false) {
            Log::error($message, [
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
