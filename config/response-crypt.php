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
    | Supported drivers: "laravel", "openssl"
    | - laravel: Uses Laravel's built-in Crypt facade (recommended)
    | - openssl: Uses OpenSSL AES-256-CBC encryption
    |
    */
    'driver' => env('RESPONSE_CRYPT_DRIVER', 'laravel'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | The encryption key used for encrypting/decrypting data.
    | By default, it uses the application's APP_KEY.
    | You can override this with RESPONSE_CRYPT_KEY environment variable.
    |
    */
    'key' => env('RESPONSE_CRYPT_KEY', env('APP_KEY')),

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
    | The cipher used for OpenSSL encryption. Only used when driver is "openssl".
    |
    */
    'cipher' => 'AES-256-CBC',

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
