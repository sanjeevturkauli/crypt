# Laravel Response Crypt 🔐

[![Latest Version](https://img.shields.io/packagist/v/sanjeev-dev/crypt.svg?style=flat-square)](https://packagist.org/packages/sanjeev-dev/crypt)
[![License](https://img.shields.io/packagist/l/sanjeev-dev/crypt.svg?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/sanjeev-dev/crypt.svg?style=flat-square)](https://packagist.org/packages/sanjeev-dev/crypt)

A powerful Laravel package for automatic API request/response encryption and decryption. Secure your API endpoints with ease by encrypting sensitive data in transit using middleware.

**Perfect for mobile apps with hex encoding support and auto-generated encryption keys!**

---

## 🚀 Quick Start (3 Steps)

**Step 1: Install**
```bash
composer require sanjeev/response-crypt
```
✅ Keys auto-generated in `.env`!

**Step 2: Publish Config**
```bash
php artisan vendor:publish --tag=response-crypt-config
```

**Step 3: Use in Routes**
```php
$middlewares = env('RESPONSE_CRYPT_ENABLED', false) 
    ? ['request.decrypt', 'response.encrypt'] 
    : [];

Route::middleware($middlewares)->group(function () {
    // Your encrypted routes here
});
```

**Enable in `.env`:**
```env
RESPONSE_CRYPT_ENABLED=true
```

**Done! 🎉** [Full Installation Guide →](INSTALLATION.md)

---

## ✨ Features

✅ **Auto-Generate Keys on Install** - Keys automatically created in `.env`  
✅ **Hex Encoding Support** - Perfect for mobile apps (compatible with existing implementations)  
✅ **Fixed IV Support** - Compatible with current mobile app setups  
✅ **Multiple Encryption Drivers** - hex (mobile), Laravel Crypt, OpenSSL  
✅ **Automatic Encryption/Decryption** - Middleware-based automatic handling  
✅ **Flexible Configuration** - Granular control via `.env` and config file  
✅ **Route Exclusions** - Skip encryption for specific routes  
✅ **Selective Key Encryption** - Exclude certain response keys from encryption  
✅ **Helper Functions** - `encrypt_data()`, `decrypt_data()` and more  
✅ **Facade Support** - Clean, expressive API using Laravel facades  
✅ **Laravel 10-13 Support** - Compatible with latest Laravel versions  
✅ **PHP 8.2+** - Modern PHP support  
## 📋 Requirements

- PHP 8.2 or higher
- Laravel 10.x, 11.x, 12.x, or 13.x

---

## 📦 Installation

### Step 1: Install via Composer

```bash
composer require sanjeev/response-crypt
```

✅ **Keys are automatically generated** and added to your `.env` file!

### Step 2: Publish Configuration

```bash
php artisan vendor:publish --tag=response-crypt-config
```

This creates `config/response-crypt.php` in your application.

### Step 3: Check Auto-Generated Keys

After installation, check your `.env` file. You'll see:

```env
# Response Crypt Package - Auto-generated Keys
RESPONSE_CRYPT_KEY="oJh92F4FPq7xE3+mvVuEXA=="
RESPONSE_CRYPT_IV="mWnVJb8mZ3hXjx9P9F2pG6F8ZT6Pb9vh+bDqWzTVkMg="
```

**These are automatically created on package installation!** You can customize them if needed.

### Step 4: Generate New Keys (Optional)

If you want to regenerate keys:

```bash
# Generate and save to .env
php artisan response-crypt:generate-keys

# Show keys without saving
php artisan response-crypt:generate-keys --show

# Force in production
php artisan response-crypt:generate-keys --force
```

---

## ⚙️ Configuration

### Environment Variables

```env
# Enable/Disable encryption
RESPONSE_CRYPT_ENABLED=true

# Encryption driver
# Options: hex, openssl_fixed, openssl, laravel
RESPONSE_CRYPT_DRIVER=hex

# Auto-generated keys (or set custom ones)
RESPONSE_CRYPT_KEY="your-key-here"
RESPONSE_CRYPT_IV="your-iv-here"

# Optional: Enable logging
RESPONSE_CRYPT_LOG_ENABLED=false
```

### Encryption Drivers

| Driver | Encoding | IV | Best For |
|--------|----------|-----|----------|
| `hex` | Hexadecimal | Fixed | **Mobile Apps** (Compatible with existing implementations) |
| `openssl_fixed` | Base64 | Fixed | Web applications with fixed IV requirement |
| `openssl` | Base64 | Random | **Maximum Security** (recommended for new projects) |
| `laravel` | Base64 | Random | Simple Laravel-only projects |

**Default: `hex`** - Perfect for mobile app compatibility!

### Config File Options

The `config/response-crypt.php` file offers extensive customization:

```php
return [
    // Enable/disable encryption globally
    'enabled' => env('RESPONSE_CRYPT_ENABLED', true),

    // Encryption driver: hex, openssl_fixed, openssl, laravel
    'driver' => env('RESPONSE_CRYPT_DRIVER', 'hex'),

    // Encryption key (auto-generated on install)
    'key' => env('RESPONSE_CRYPT_KEY'),

    // Encryption IV (auto-generated on install)
    'iv' => env('RESPONSE_CRYPT_IV'),

    // Enable response encryption
    'encrypt_response' => true,

    // Enable request decryption
    'decrypt_request' => true,

    // Response wrapper key
    'response_wrapper_key' => 'payload',

    // Request payload key
    'request_payload_key' => 'payload',

    // Include metadata in response
    'include_meta' => true,

    // Routes to exclude from encryption
    'excluded_routes' => [
        'login',
        'register',
        'sanctum/csrf-cookie',
        'health',
    ],

    // Response keys to exclude from encryption
    'excluded_keys' => [
        'token_type',
        'expires_in',
    ],

    // Cipher algorithm
    'cipher' => 'AES-256-CBC',
];
```

---

## 💻 Usage

### Basic Route Middleware

**Simple Enable/Disable Pattern (Recommended):**

```php
use Illuminate\Support\Facades\Route;

// Enable/disable encryption with environment variable
$middlewares = env('RESPONSE_CRYPT_ENABLED', false) 
    ? ['request.decrypt', 'response.encrypt'] 
    : [];

Route::middleware($middlewares)->group(function () {
    // All your API routes here
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/posts', [PostController::class, 'store']);
});
```

### Middleware Options

| Middleware | Alias | Description |
|------------|-------|-------------|
| `EncryptApiResponse` | `response.encrypt` | Encrypts outgoing responses only |
| `DecryptApiRequest` | `request.decrypt` | Decrypts incoming requests only |
| `EncryptDecryptApi` | `api.crypt` | Both encrypt response and decrypt request |

**Example Usage:**

```php
// Only encrypt responses
Route::middleware(['response.encrypt'])->get('/api/data', function () {
    return response()->json(['message' => 'This will be encrypted']);
});

// Only decrypt requests
Route::middleware(['request.decrypt'])->post('/api/process', function () {
    return response()->json(['received' => request()->all()]);
});

// Both encrypt and decrypt
Route::middleware(['api.crypt'])->post('/api/secure', function () {
    return response()->json([
        'status' => true,
        'data' => request()->all()
    ]);
});
```

### Using Facade

```php
use Sanjeev\ResponseCrypt\Facades\ResponseCrypt;

// Encrypt data
$encrypted = ResponseCrypt::encrypt(['secret' => 'data', 'key' => 'value']);

// Decrypt data
$decrypted = ResponseCrypt::decrypt($encrypted);

// Encrypt array for response
$response = ResponseCrypt::encryptArray(['status' => true, 'data' => $data]);

// Decrypt request array
$data = ResponseCrypt::decryptArray($request->all());
```

### Helper Functions

```php
// Encrypt data
$encrypted = encrypt_data(['name' => 'John', 'email' => 'john@example.com']);

// Decrypt data
$decrypted = decrypt_data($encrypted);

// Encrypt for response
$response = encrypt_response(['status' => true, 'message' => 'Success']);

// Decrypt request
$data = decrypt_request($request->all());
```

---

## 🔄 Migration from Existing Setup

If you have an existing encryption implementation like this:

```php
// Old way
$middlewares = env('IS_ENCRYPTION', false) 
    ? ['decrypt.request', 'encrypt.response'] 
    : [];
```

**Simply change to:**

```php
// New way with Response Crypt
$middlewares = env('RESPONSE_CRYPT_ENABLED', false) 
    ? ['request.decrypt', 'response.encrypt'] 
    : [];
```

**And update `.env`:**

```env
# Old
IS_ENCRYPTION=true
ENCRYPTION_KEY="..."
ENCRYPTION_IV="..."

# New (keys auto-generated on install)
RESPONSE_CRYPT_ENABLED=true
RESPONSE_CRYPT_KEY="auto-generated"
RESPONSE_CRYPT_IV="auto-generated"
RESPONSE_CRYPT_DRIVER=hex
```

**✅ That's it! Your mobile app will continue working without any changes!**

---

## 📱 Mobile App Compatibility

### Hex Encoding (Default)

The package uses **hex encoding by default** (`driver=hex`), which is:
- ✅ Compatible with most mobile apps
- ✅ Compatible with existing implementations
- ✅ No changes needed in your mobile app code

### Request Format

**Send encrypted request:**
```json
{
  "payload": "3a4b5c6d7e8f9a0b1c2d3e4f..."
}
```

**The server will automatically decrypt it!**

### Response Format

**Server encrypts response:**
```json
{
  "encrypted": true,
  "payload": "1a2b3c4d5e6f7a8b9c0d1e2f...",
  "meta": {
    "algorithm": "hex",
    "timestamp": "2024-06-18T10:30:00Z"
  }
}
```

**Your mobile app decrypts the `payload` field.**

---

## 🎯 Complete Example

**File: `routes/api.php`**

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\PostController;

// Encryption middleware toggle
$encryptionMiddlewares = env('RESPONSE_CRYPT_ENABLED', false) 
    ? ['request.decrypt', 'response.encrypt'] 
    : [];

// Public routes (no encryption)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// Protected encrypted routes
Route::middleware($encryptionMiddlewares)->group(function () {
    
    // Auth required routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/profile', [UserController::class, 'profile']);
        Route::post('/update-profile', [UserController::class, 'update']);
        Route::post('/logout', [AuthController::class, 'logout']);
        
        // Posts
        Route::get('/posts', [PostController::class, 'index']);
        Route::post('/posts', [PostController::class, 'store']);
        Route::get('/posts/{id}', [PostController::class, 'show']);
    });
    
    // Public encrypted routes
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/blogs', [BlogController::class, 'index']);
});
```

**File: `.env`**

```env
# Response Crypt Configuration
RESPONSE_CRYPT_ENABLED=true
RESPONSE_CRYPT_DRIVER=hex

# Auto-generated keys (created on package install)
RESPONSE_CRYPT_KEY="oJh92F4FPq7xE3+mvVuEXA=="
RESPONSE_CRYPT_IV="mWnVJb8mZ3hXjx9P9F2pG6F8ZT6Pb9vh+bDqWzTVkMg="
```

---

## 🔧 Advanced Configuration

### Exclude Specific Routes

```php
// config/response-crypt.php
'excluded_routes' => [
    'login',
    'register',
    'health',
    'api/public/*',
],
```

### Exclude Response Keys

```php
'excluded_keys' => [
    'token_type',
    'expires_in',
    'scope',
],
```

**Example Response:**
```json
{
  "token_type": "Bearer",
  "expires_in": 3600,
  "encrypted": true,
  "payload": "encrypted-data-here"
}
```

The `token_type` and `expires_in` remain unencrypted!

---

## 🧪 Testing

Run package tests:

```bash
composer test
```

Or using PHPUnit:

```bash
vendor/bin/phpunit
```

Run specific test:

```bash
vendor/bin/phpunit tests/Unit/ResponseCryptServiceTest.php
```

---

## 🔒 Security Best Practices

1. ✅ **Always use HTTPS** in production
2. ✅ **Keep encryption keys secure** (never commit `.env`)
3. ✅ **Rotate keys regularly**
4. ✅ **Use `hex` driver** for mobile apps (compatible)
5. ✅ **Use `openssl` driver** for maximum security (new projects)
6. ✅ **Monitor failed decryptions**
7. ✅ **Validate all input data**
8. ✅ **Use rate limiting** on encrypted endpoints

For detailed security guidelines, see [SECURITY.md](SECURITY.md)

---

## 📖 Documentation

| Document | Description |
|----------|-------------|
| [QUICKSTART.md](QUICKSTART.md) | 5-minute quick start guide |
| [INSTALLATION.md](INSTALLATION.md) | Detailed installation instructions |
| [COMPLETE_GUIDE.md](COMPLETE_GUIDE.md) | Comprehensive guide |
| [SECURITY.md](SECURITY.md) | Security best practices |
| [TESTING.md](TESTING.md) | Testing guide |
| [COMMANDS.md](COMMANDS.md) | Command reference |
| [CHANGELOG.md](CHANGELOG.md) | Version history |

---

## 💡 Key Benefits

✅ **Zero Configuration** - Works out of the box with auto-generated keys  
✅ **Mobile Compatible** - Hex encoding support for mobile apps  
✅ **Easy Toggle** - Enable/disable with one environment variable  
✅ **Flexible** - Use globally, per route group, or per individual route  
✅ **Secure** - Industry-standard AES-256-CBC encryption  
✅ **Well Tested** - Comprehensive test coverage  
✅ **Well Documented** - Extensive documentation and examples  
✅ **Laravel Native** - Follows Laravel conventions  

---

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

---

## 📜 License

This package is open-source software licensed under the [MIT License](LICENSE).

---

## 🙏 Credits

- **Author:** Sanjeev
- **Package:** sanjeev/response-crypt
- **Framework:** Laravel
- **Community:** Thank you for your support!

---

## 📞 Support

- **GitHub Repository:** https://github.com/sanjeev/response-crypt
- **Issues:** https://github.com/sanjeev/response-crypt/issues
- **Packagist:** https://packagist.org/packages/sanjeev/response-crypt

---

**Made with ❤️ for the Laravel community**

**Secure your APIs with ease! 🔒**
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
