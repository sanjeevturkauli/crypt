<?php

declare(strict_types=1);

use SecureCrypto\Encryption\Facades\ResponseCrypt;

if (!function_exists('encrypt_data')) {
    /**
     * Encrypt data using ResponseCrypt.
     */
    function encrypt_data(mixed $data): string
    {
        return ResponseCrypt::encrypt($data);
    }
}

if (!function_exists('decrypt_data')) {
    /**
     * Decrypt data using ResponseCrypt.
     */
    function decrypt_data(string $payload): mixed
    {
        return ResponseCrypt::decrypt($payload);
    }
}

if (!function_exists('encrypt_response')) {
    /**
     * Encrypt array and return formatted response structure.
     */
    function encrypt_response(array $data): array
    {
        return ResponseCrypt::encryptArray($data);
    }
}

if (!function_exists('decrypt_request')) {
    /**
     * Decrypt array and return original data.
     */
    function decrypt_request(array $data): array
    {
        return ResponseCrypt::decryptArray($data);
    }
}
