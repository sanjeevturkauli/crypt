<?php

declare(strict_types=1);

namespace SecureCrypto\Encryption\Contracts;

interface EncryptionDriverInterface
{
    /**
     * Encrypt the given data.
     */
    public function encrypt(string $data): string;

    /**
     * Decrypt the given encrypted data.
     */
    public function decrypt(string $encryptedData): string;

    /**
     * Get the driver name.
     */
    public function getName(): string;
}
