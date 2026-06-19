<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enable/Disable Response Encryption
    |--------------------------------------------------------------------------
    |
    | This option controls whether the response encryption and request
    | decryption features are enabled globally. You can toggle this
    | via the RESPONSE_CRYPT_ENABLED environment variable.
    |
    */
    'enabled' => env('RESPONSE_CRYPT_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Encryption Driver
    |--------------------------------------------------------------------------
    |
    | Supported drivers: "laravel", "openssl", "openssl_fixed", "hex"
    | - laravel: Uses Laravel's built-in Crypt facade (base64 encoded)
    | - openssl: Uses OpenSSL AES-256-CBC with random IV (base64 encoded)
    | - openssl_fixed: Uses OpenSSL AES-256-CBC with fixed IV (base64 encoded)
    | - hex: Uses OpenSSL AES-256-CBC with fixed IV (hex encoded) - Compatible with your current setup
    |
    */
    'driver' => env('RESPONSE_CRYPT_DRIVER', 'hex'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | The encryption key used for encrypting/decrypting data.
    | This will be auto-generated when you publish the config.
    | You can customize it in your .env file with RESPONSE_CRYPT_KEY.
    |
    */
    'key' => env('RESPONSE_CRYPT_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Encryption IV (Initialization Vector)
    |--------------------------------------------------------------------------
    |
    | The IV used for OpenSSL encryption when driver is 'openssl_fixed'.
    | This will be auto-generated when you publish the config.
    | You can customize it in your .env file with RESPONSE_CRYPT_IV.
    |
    | Note: Using fixed IV is less secure than random IV.
    | Use 'openssl' driver for random IV (recommended for production).
    |
    */
    'iv' => env('RESPONSE_CRYPT_IV'),

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
    | Supported: "base64", "hex"
    | - base64: Standard base64 encoding (default)
    | - hex: Hexadecimal encoding (compatible with mobile apps)
    |
    */
    'encoding' => env('RESPONSE_CRYPT_ENCODING', 'hex'),

    /*
    |--------------------------------------------------------------------------
    | Response Structure Template
    |--------------------------------------------------------------------------
    |
    | Customize the encrypted response structure. You can use placeholders:
    | - {payload}    : The encrypted data
    | - {encrypted}  : Boolean flag (true/false)
    | - {algorithm}  : Encryption algorithm used
    | - {cipher}     : Cipher method
    | - {timestamp}  : Current timestamp
    |
    | Examples:
    | 
    | Standard format (current):
    | [
    |     'encrypted' => true,
    |     'payload' => '{payload}',
    |     'meta' => ['algorithm' => '{algorithm}', 'cipher' => '{cipher}']
    | ]
    |
    | Custom API format:
    | [
    |     'success' => true,
    |     'status' => 200,
    |     'message' => 'success',
    |     'data' => '{payload}',
    |     'encrypted' => true
    | ]
    |
    | Minimal format:
    | [
    |     'data' => '{payload}'
    | ]
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
    | Allow Plain Response via Accept Header
    |--------------------------------------------------------------------------
    |
    | When true, clients can request plain (unencrypted) responses by using
    | Accept: application/json header. Useful for debugging and development.
    | Set to false in production for maximum security.
    |
    */
    'allow_plain_via_accept' => env('RESPONSE_CRYPT_ALLOW_PLAIN', false),

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */

    /*
    | Disable Integrity Check
    | Set to true only if you're developing/modifying the package
    */
    'disable_integrity_check' => env('RESPONSE_CRYPT_DISABLE_INTEGRITY', false),

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable logging for encryption/decryption operations.
    | WARNING: Never log decrypted sensitive data in production.
    |
    */
    'log_enabled' => env('RESPONSE_CRYPT_LOG_ENABLED', false),
];
