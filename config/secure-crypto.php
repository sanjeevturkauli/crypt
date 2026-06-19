<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enable/Disable Encryption
    |--------------------------------------------------------------------------
    |
    | Master switch for encryption/decryption features.
    | Works for both API and Web routes.
    |
    */
    'enabled' => env('CRYPT_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Encryption Driver
    |--------------------------------------------------------------------------
    |
    | Supported drivers: "laravel", "openssl", "openssl_fixed", "hex"
    | - laravel: Uses Laravel's built-in Crypt facade (base64 encoded)
    | - openssl: Uses OpenSSL AES-256-CBC with random IV (base64 encoded)
    | - openssl_fixed: Uses OpenSSL AES-256-CBC with fixed IV (base64 encoded)
    | - hex: Uses OpenSSL AES-256-CBC with fixed IV (hex encoded) - Best for mobile
    |
    */
    'driver' => env('CRYPT_DRIVER', 'hex'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | The encryption key used for encrypting/decrypting data.
    | Auto-generated on installation.
    |
    */
    'key' => env('CRYPT_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Encryption IV (Initialization Vector)
    |--------------------------------------------------------------------------
    |
    | The IV used for OpenSSL encryption.
    | Auto-generated on installation.
    |
    */
    'iv' => env('CRYPT_IV'),

    /*
    |--------------------------------------------------------------------------
    | Encrypt Response
    |--------------------------------------------------------------------------
    |
    | Enable or disable response encryption. When true, outgoing API
    | responses will be automatically encrypted.
    |
    */
    'encrypt_response' => true,

    /*
    |--------------------------------------------------------------------------
    | Decrypt Request
    |--------------------------------------------------------------------------
    |
    | Enable or disable request decryption. When true, incoming encrypted
    | API request payloads will be automatically decrypted.
    |
    */
    'decrypt_request' => true,

    /*
    |--------------------------------------------------------------------------
    | Response Wrapper Key
    |--------------------------------------------------------------------------
    |
    | The key name used to wrap the encrypted response payload.
    |
    */
    'response_wrapper_key' => 'payload',

    /*
    |--------------------------------------------------------------------------
    | Request Payload Key
    |--------------------------------------------------------------------------
    |
    | The key name expected in incoming requests that contains the
    | encrypted payload.
    |
    */
    'request_payload_key' => 'payload',

    /*
    |--------------------------------------------------------------------------
    | Include Metadata
    |--------------------------------------------------------------------------
    |
    | When true, the encrypted response will include metadata such as
    | the encryption algorithm and timestamp.
    |
    */
    'include_meta' => true,

    /*
    |--------------------------------------------------------------------------
    | Encrypt Web Responses
    |--------------------------------------------------------------------------
    |
    | By default, only API JSON responses are encrypted. Set this to true
    | if you want to encrypt web responses as well (not recommended).
    |
    */
    'encrypt_web_response' => false,

    /*
    |--------------------------------------------------------------------------
    | Excluded Routes
    |--------------------------------------------------------------------------
    |
    | List of route names or patterns that should be excluded from
    | encryption/decryption. These routes will work normally.
    |
    */
    'excluded_routes' => [
        'login',
        'register',
        'sanctum/csrf-cookie',
        'password/*',
        'health',
        'ping',
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Response Keys
    |--------------------------------------------------------------------------
    |
    | Array keys that should not be encrypted in the response.
    | These will be returned as plain text alongside encrypted data.
    |
    */
    'excluded_keys' => [
        'token_type',
        'expires_in',
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Response
    |--------------------------------------------------------------------------
    |
    | Default error response returned when decryption fails.
    |
    */
    'error_response' => [
        'status' => false,
        'message' => 'Invalid encrypted payload.',
        'error' => 'DECRYPTION_FAILED',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cipher
    |--------------------------------------------------------------------------
    |
    | The cipher used for OpenSSL encryption.
    | Only used when driver is "openssl", "openssl_fixed", or "hex".
    |
    */
    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Encoding Format
    |--------------------------------------------------------------------------
    |
    | The encoding format for encrypted data.
    | - base64: Standard base64 encoding (web-friendly)
    | - hex: Hexadecimal encoding (mobile-friendly)
    |
    */
    'encoding' => env('CRYPT_ENCODING', 'hex'),

    /*
    |--------------------------------------------------------------------------
    | Response Mode
    |--------------------------------------------------------------------------
    |
    | Controls how encrypted responses are returned:
    | 
    | - 'minimal'  : Only encrypted string (clean, professional)
    |   "a2e19e18e6f504111ec9a8a82ce920e..."
    |
    | - 'wrapped'  : Full response with metadata
    |   {"success": true, "data": "encrypted...", "meta": {...}}
    |
    | - 'custom'   : Use response_structure template below
    |
    */
    'response_mode' => env('CRYPT_RESPONSE_MODE', 'minimal'),

    /*
    |--------------------------------------------------------------------------
    | Response Structure Template
    |--------------------------------------------------------------------------
    |
    | Only used when response_mode is 'custom'
    | Customize the encrypted response structure with placeholders:
    | - {payload}    : The encrypted data
    | - {encrypted}  : Boolean flag (true/false)
    | - {algorithm}  : Encryption algorithm used
    | - {cipher}     : Cipher method
    | - {timestamp}  : Current timestamp
    |
    */
    'response_structure' => [
        'success' => true,
        'status' => 200,
        'message' => 'success',
        'data' => '{payload}',
        'encrypted' => '{encrypted}',
        'meta' => '{meta}',  // Set to null to disable metadata
    ],

    /*
    |--------------------------------------------------------------------------
    | Allow Plain Response Control
    |--------------------------------------------------------------------------
    |
    | When true, clients can request plain (unencrypted) responses via:
    | - Header: X-Disable-Encryption: true
    | - Query: ?encrypted=false
    | - Accept: application/json (when enabled)
    |
    */
    'allow_plain_via_accept' => env('CRYPT_ALLOW_PLAIN', false),

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */

    /*
    | Disable Integrity Check
    | Set to true only if you're developing/modifying the package
    */
    'disable_integrity_check' => env('CRYPT_DISABLE_INTEGRITY', false),

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable logging for encryption/decryption operations.
    | WARNING: Never log decrypted sensitive data in production.
    |
    */
    'log_enabled' => env('CRYPT_LOG_ENABLED', false),
];
