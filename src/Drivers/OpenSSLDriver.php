<?php

declare(strict_types=1);

namespace Sanjeev\ResponseCrypt\Drivers;

class OpenSSLDriver extends BaseEncryptionDriver
{
    public function encrypt(string $data): string
    {
        try {
            $key = $this->getKey();
            $cipher = $this->getCipher();
            $useFixedIV = ($this->config['driver'] ?? 'openssl') === 'openssl_fixed';

            $iv = $useFixedIV 
                ? $this->getIV() 
                : $this->generateRandomIV($cipher);

            $encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);

            if ($encrypted === false) {
                $this->handleEncryptionFailure('OpenSSL encryption failed');
            }

            $result = $useFixedIV ? $encrypted : $iv . $encrypted;

            return base64_encode($result);
        } catch (\Exception $e) {
            $this->handleEncryptionFailure('OpenSSL encryption failed: ' . $e->getMessage(), $e);
        }
    }

    public function decrypt(string $encryptedData): string
    {
        try {
            $key = $this->getKey();
            $cipher = $this->getCipher();
            $useFixedIV = ($this->config['driver'] ?? 'openssl') === 'openssl_fixed';

            $decoded = base64_decode($encryptedData, true);

            if ($decoded === false) {
                $this->handleDecryptionFailure('Invalid base64 encoded data');
            }

            if ($useFixedIV) {
                $iv = $this->getIV();
                $encrypted = $decoded;
            } else {
                $ivLength = openssl_cipher_iv_length($cipher);
                $iv = substr($decoded, 0, $ivLength);
                $encrypted = substr($decoded, $ivLength);
            }

            $decrypted = openssl_decrypt($encrypted, $cipher, $key, OPENSSL_RAW_DATA, $iv);

            if ($decrypted === false) {
                $this->handleDecryptionFailure('OpenSSL decryption failed');
            }

            return $decrypted;
        } catch (\Exception $e) {
            $this->handleDecryptionFailure('OpenSSL decryption failed: ' . $e->getMessage(), $e);
        }
    }

    public function getName(): string
    {
        return $this->config['driver'] ?? 'openssl';
    }

    protected function generateRandomIV(string $cipher): string
    {
        $ivLength = openssl_cipher_iv_length($cipher);
        return openssl_random_pseudo_bytes($ivLength);
    }
}
