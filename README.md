# Laravel Response Crypt 🔐

[![Latest Version](https://img.shields.io/packagist/v/sanjeev-dev/crypt.svg?style=flat-square)](https://packagist.org/packages/sanjeev-dev/crypt)
[![License](https://img.shields.io/packagist/l/sanjeev-dev/crypt.svg?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/sanjeev-dev/crypt.svg?style=flat-square)](https://packagist.org/packages/sanjeev-dev/crypt)

A powerful Laravel package for automatic API request/response encryption and decryption. Secure your API endpoints with ease by encrypting sensitive data in transit using middleware.

## Features

✅ **Automatic Encryption/Decryption** - Middleware-based automatic handling  
✅ **Multiple Encryption Drivers** - Laravel Crypt or OpenSSL  
✅ **Flexible Configuration** - Granular control over encryption behavior  
✅ **Route Exclusions** - Skip encryption for specific routes  
✅ **Selective Key Encryption** - Exclude certain response keys from encryption  
✅ **Helper Functions** - Easy-to-use helper functions  
✅ **Facade Support** - Clean, expressive API using Laravel facades  
✅ **Comprehensive Testing** - Full test coverage included  
✅ **Laravel 10, 11, 12, 13 Support** - Compatible with modern Laravel versions  

## Requirements

- PHP 8.2 or higher
- Laravel 10.x, 11.x, 12.x, or 13.x

## Installation

Install the package via Composer:

```bash
composer require sanjeev-dev/crypt
```

### Publish Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=response-crypt-config
```

This will create a `config/response-crypt.php` file in your Laravel application.

## Configuration

The configuration file offers extensive customization options:

```php
return [
    // Enable/disable the package globally
    'enabled' => env('RESPONSE_CRYPT_ENABLED', true),

    // Encryption driver: 'laravel' or 'openssl'
    'driver' => env('RESPONSE_CRYPT_DRIVER', 'laravel'),

    // Encryption key (defaults to APP_KEY)
    'key' => env('RESPONSE_CRYPT_KEY', env('APP_KEY')),

    // Enable response encryption
    'encrypt_response' => true,

    // Enable request decryption
    'decrypt_request' => true,

    // Response wrapper key name
    'response_wrapper_key' => 'payload',

    // Request payload key name
    'request_payload_key' => 'payload',

    // Include metadata in encrypted response
    'include_meta' => true,

    // Excluded routes (won't be encrypted/decrypted)
    'excluded_routes' => [
        'login',
        'register',
        'sanctum/csrf-cookie',
    ],

    // Keys to exclude from encryption in response
    'excluded_keys' => [
        'token_type',
        'expires_in',
    ],
];
```

### Environment Variables

Add these to your `.env` file:

```env
RESPONSE_CRYPT_ENABLED=true
RESPONSE_CRYPT_DRIVER=laravel
RESPONSE_CRYPT_KEY="${APP_KEY}"
RESPONSE_CRYPT_LOG_ENABLED=false
```

## Usage

### Middleware

The package provides three middleware options:

| Middleware | Alias | Description |
|------------|-------|-------------|
| `EncryptApiResponse` | `response.encrypt` | Encrypts outgoing responses |
| `DecryptApiRequest` | `request.decrypt` | Decrypts incoming requests |
| `EncryptDecryptApi` | `api.crypt` | Both encrypt and decrypt |

#### Example 1: Encrypt Response Only

```php
use Illuminate\Support\Facades\Route;

Route::middleware(['response.encrypt'])->get('/api/users', function () {
    return response()->json([
        'status' => true,
        'data' => [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ],
    ]);
});
```

**Response:**
```json
{
  "encrypted": true,
  "payload": "eyJpdiI6IjRGNnNMOE1XYnhOV...",
  "meta": {
    "algorithm": "laravel",
    "timestamp": "2026-06-18T10:30:00Z"
  }
}
```

#### Example 2: Decrypt Request Only

```php
Route::middleware(['request.decrypt'])->post('/api/process', function () {
    $data = request()->all();
    
    return response()->json([
        'status' => true,
        'message' => 'Data received',
        'data' => $data,
    ]);
});
```

**Request:**
```json
{
  "payload": "eyJpdiI6IjRGNnNMOE1XYnhOV..."
}
```

The encrypted payload will be automatically decrypted and available via `request()->all()`.

#### Example 3: Both Encrypt & Decrypt

```php
Route::middleware(['api.crypt'])->post('/api/secure-transaction', function () {
    $data = request()->all();
    
    return response()->json([
        'transaction_id' => uniqid(),
        'status' => 'completed',
        'amount' => $data['amount'],
    ]);
});
```

#### Example 4: Protected Route Group

```php
Route::middleware(['api.crypt', 'auth:sanctum'])->prefix('secure')->group(function () {
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/profile', [ProfileController::class, 'show']);
});
```

### Using the Facade

```php
use Sanjeev\ResponseCrypt\Facades\ResponseCrypt;

// Encrypt data
$encrypted = ResponseCrypt::encrypt([
    'card_number' => '1234567890123456',
    'cvv' => '123',
]);

// Decrypt data
$decrypted = ResponseCrypt::decrypt($encrypted);
```

### Using Helper Functions

```php
// Encrypt
$encrypted = encrypt_data(['user_id' => 123, 'action' => 'login']);

// Decrypt
$decrypted = decrypt_data($encrypted);
```

## Advanced Usage

### Exclude Specific Response Keys

Some keys like `token_type` or `expires_in` can remain unencrypted. Configure this in `config/response-crypt.php`:

```php
'excluded_keys' => [
    'token_type',
    'expires_in',
],
```

**Example Response:**
```json
{
  "token_type": "Bearer",
  "expires_in": 3600,
  "encrypted": true,
  "payload": "eyJpdiI6IjRGNnNMOE..."
}
```

### Exclude Routes from Encryption

Add route patterns to skip encryption:

```php
'excluded_routes' => [
    'login',
    'register',
    'password/*',
    'health',
],
```

### Custom Error Handling

When decryption fails, the package returns a configurable error response:

```php
'error_response' => [
    'status' => false,
    'message' => 'Invalid encrypted payload.',
    'error' => 'DECRYPTION_FAILED',
],
```

You can customize this in the config file.

## Client-Side Integration

### JavaScript Example

```javascript
// Encrypt data before sending
async function encryptData(data, key) {
    // Use your preferred encryption library (e.g., CryptoJS)
    const encrypted = CryptoJS.AES.encrypt(
        JSON.stringify(data), 
        key
    ).toString();
    
    return encrypted;
}

// Send encrypted request
const data = { username: 'john', password: 'secret' };
const encrypted = await encryptData(data, 'your-encryption-key');

fetch('/api/process', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ payload: encrypted })
});

// Decrypt response
async function decryptResponse(encryptedPayload, key) {
    const bytes = CryptoJS.AES.decrypt(encryptedPayload, key);
    return JSON.parse(bytes.toString(CryptoJS.enc.Utf8));
}
```

See [examples/javascript-client.js](examples/javascript-client.js) for a complete implementation.

### Postman Integration

Import the [Postman collection](examples/postman-collection.json) to test encrypted endpoints.

## Testing

Run the test suite:

```bash
composer test
```

Or with coverage:

```bash
./vendor/bin/phpunit --coverage-html coverage
```

## Security Considerations

⚠️ **Important Security Notes:**

1. **Never log decrypted data** in production environments
2. **Use strong encryption keys** - Laravel's APP_KEY should be 32 characters
3. **Use HTTPS** - Encryption in transit complements, doesn't replace TLS/SSL
4. **Rotate keys regularly** - Implement key rotation strategy for sensitive applications
5. **Validate decrypted data** - Always validate and sanitize decrypted input
6. **Disable in development** - Set `RESPONSE_CRYPT_ENABLED=false` for easier debugging

## API Reference

### ResponseCrypt Facade

```php
// Encrypt data
ResponseCrypt::encrypt(mixed $data): string

// Decrypt data
ResponseCrypt::decrypt(string $encryptedData): mixed

// Check if encryption is enabled
ResponseCrypt::isEnabled(): bool
```

### Helper Functions

```php
encrypt_data(mixed $data): string
decrypt_data(string $encryptedData): mixed
```

## Troubleshooting

### Decryption Failed Error

**Cause:** Key mismatch or corrupted payload  
**Solution:** Ensure both client and server use the same encryption key

### Middleware Not Working

**Cause:** Middleware not registered  
**Solution:** Clear cache with `php artisan config:clear` and `php artisan cache:clear`

### Performance Issues

**Cause:** Encrypting large responses  
**Solution:** Use selective encryption with `excluded_keys` or encrypt only sensitive fields

## Examples

Check the [examples](examples/) directory for:

- [Basic Usage](examples/basic-usage.php) - Common use cases
- [JavaScript Client](examples/javascript-client.js) - Frontend integration
- [Postman Collection](examples/postman-collection.json) - API testing

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Author

**Sanjeev Kumar**  
Email: sanjeevturkauli.dev@gmail.com  
GitHub: [@sanjeev-dev](https://github.com/sanjeev-dev)

## Support

If you find this package helpful, please ⭐ star the repository!

For issues and feature requests, please use the [GitHub issue tracker](https://github.com/sanjeev-dev/crypt/issues).

---

Made with ❤️ by [Sanjeev Kumar](https://github.com/sanjeev-dev)
