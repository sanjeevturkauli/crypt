<?php

declare(strict_types=1);

namespace SecureCrypto\Encryption\Drivers;

use Illuminate\Support\Facades\Crypt;

class LaravelEncryptionDriver extends BaseEncryptionDriver
{
    public function encrypt(string $data): string
    {
        try {
            return Crypt::encryptString($data);
        } catch (\Exception $e) {
            $this->handleEncryptionFailure('Laravel encryption failed: ' . $e->getMessage(), $e);
        }
    }

    public function decrypt(string $encryptedData): string
    {
        try {
            return Crypt::decryptString($encryptedData);
        } catch (\Exception $e) {
            $this->handleDecryptionFailure('Laravel decryption failed: ' . $e->getMessage(), $e);
        }
    }

    public function getName(): string
    {
        return 'laravel';
    }

    protected function validateConfiguration(): void
    {
        // Laravel Crypt uses APP_KEY automatically, no validation needed
    }
}
