<?php

declare(strict_types=1);

namespace SecureCrypto\Encryption\Drivers;

class HexEncryptionDriver extends BaseEncryptionDriver
{
    public function encrypt(string $data): string
    {
        try {
            $key = $this->getKey();
            $iv = $this->getIV();
            $cipher = $this->getCipher();

            $encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);

            if ($encrypted === false) {
                $this->handleEncryptionFailure('OpenSSL encryption failed');
            }

            return bin2hex($encrypted);
        } catch (\Exception $e) {
            $this->handleEncryptionFailure('Hex encryption failed: ' . $e->getMessage(), $e);
        }
    }

    public function decrypt(string $encryptedData): string
    {
        try {
            $key = $this->getKey();
            $iv = $this->getIV();
            $cipher = $this->getCipher();

            $encrypted = hex2bin($encryptedData);

            if ($encrypted === false) {
                $this->handleDecryptionFailure('Invalid hexadecimal data');
            }

            $decrypted = openssl_decrypt($encrypted, $cipher, $key, OPENSSL_RAW_DATA, $iv);

            if ($decrypted === false) {
                $this->handleDecryptionFailure('OpenSSL decryption failed');
            }

            return $decrypted;
        } catch (\Exception $e) {
            $this->handleDecryptionFailure('Hex decryption failed: ' . $e->getMessage(), $e);
        }
    }

    public function getName(): string
    {
        return 'hex';
    }

    protected function validateConfiguration(): void
    {
        $key = $this->getKey();
        $iv = $this->getIV();

        if (strlen($key) !== 32) {
            throw new \RuntimeException(
                sprintf('Encryption key must be 32 bytes for AES-256, got %d bytes', strlen($key))
            );
        }

        if (strlen($iv) !== 16) {
            throw new \RuntimeException(
                sprintf('Encryption IV must be 16 bytes for AES-256-CBC, got %d bytes', strlen($iv))
            );
        }
    }
}
