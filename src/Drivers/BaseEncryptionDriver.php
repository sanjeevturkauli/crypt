<?php

declare(strict_types=1);

namespace Sanjeev\ResponseCrypt\Drivers;

use Sanjeev\ResponseCrypt\Contracts\EncryptionDriverInterface;
use Sanjeev\ResponseCrypt\Exceptions\{DecryptionFailedException, EncryptionFailedException};

abstract class BaseEncryptionDriver implements EncryptionDriverInterface
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->validateConfiguration();
    }

    /**
     * Get encryption key from configuration.
     */
    protected function getKey(): string
    {
        $key = $this->config['key'] ?? null;

        if (empty($key)) {
            throw new \RuntimeException(
                'Encryption key not configured. Please set RESPONSE_CRYPT_KEY in your .env file.'
            );
        }

        return $this->decodeKey($key);
    }

    /**
     * Get encryption IV from configuration.
     */
    protected function getIV(): string
    {
        $iv = $this->config['iv'] ?? null;

        if (empty($iv)) {
            throw new \RuntimeException(
                'Encryption IV not configured. Please set RESPONSE_CRYPT_IV in your .env file.'
            );
        }

        return $this->decodeKey($iv);
    }

    /**
     * Get cipher algorithm.
     */
    protected function getCipher(): string
    {
        return $this->config['cipher'] ?? 'AES-256-CBC';
    }

    /**
     * Decode base64 encoded key or return as is.
     */
    protected function decodeKey(string $key): string
    {
        if (str_starts_with($key, 'base64:')) {
            return base64_decode(substr($key, 7));
        }

        $decoded = base64_decode($key, true);
        return $decoded !== false ? $decoded : $key;
    }

    /**
     * Validate driver configuration.
     */
    protected function validateConfiguration(): void
    {
        // Override in child classes if needed
    }

    /**
     * Handle encryption failure.
     */
    protected function handleEncryptionFailure(string $message, ?\Throwable $previous = null): never
    {
        throw new EncryptionFailedException($message, previous: $previous);
    }

    /**
     * Handle decryption failure.
     */
    protected function handleDecryptionFailure(string $message, ?\Throwable $previous = null): never
    {
        throw new DecryptionFailedException($message, previous: $previous);
    }
}
