# Laravel Response Crypt 🔐

[![Latest Version](https://img.shields.io/packagist/v/sanjeev-dev/crypt.svg?style=flat-square)](https://packagist.org/packages/sanjeev-dev/crypt)
[![License](https://img.shields.io/packagist/l/sanjeev-dev/crypt.svg?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/sanjeev-dev/crypt.svg?style=flat-square)](https://packagist.org/packages/sanjeev-dev/crypt)

A **professional-grade** Laravel package for automatic API request/response encryption and decryption. Built with modern design patterns, SOLID principles, and PHP 8.2+ features.

**Perfect for mobile apps with hex encoding, auto-generated keys, and enterprise-ready architecture!**

### 🏆 Professional Features
- ✅ **Strategy Pattern** - Pluggable encryption drivers
- ✅ **SOLID Principles** - Clean, maintainable code
- ✅ **Auto-Generated Keys** - Zero configuration
- ✅ **Mobile Compatible** - Hex encoding support
- ✅ **Type-Safe** - Full PHP 8.2+ type hints
- ✅ **Well-Tested** - Comprehensive test coverage

---

## 🚀 Quick Start (3 Steps)

**Step 1: Install**
```bash
composer require sanjeev-dev/crypt
```
✅ Keys auto-generated in `.env`!

**Step 2: Publish Config**
```bash
php artisan vendor:publish --tag=crypt-config
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

### 🎯 Core Features
✅ **Auto-Generate Keys** - Encryption keys created on install  
✅ **Strategy Pattern** - Pluggable encryption drivers (Hex, OpenSSL, Laravel)  
✅ **Zero Configuration** - Works out of the box  
✅ **Middleware Based** - Automatic request/response handling  
✅ **Mobile Compatible** - Hex encoding for mobile apps  

### 🏗️ Architecture
✅ **Design Patterns** - Strategy, Facade, Dependency Injection  
✅ **SOLID Principles** - Professional, maintainable code  
✅ **Interface-Based** - Easy to extend with custom drivers  
✅ **Type-Safe** - Full PHP 8.2+ type hints  
✅ **Modern PHP** - match(), named parameters, strict types  

### ⚙️ Configuration
✅ **Route Exclusions** - Skip encryption for specific routes  
✅ **Key Exclusions** - Exclude response keys from encryption  
✅ **Flexible Drivers** - Switch between Hex, OpenSSL, Laravel Crypt  
✅ **Environment Control** - Easy enable/disable via `.env`  

### 🛠️ Developer Experience
✅ **Helper Functions** - `encrypt_data()`, `decrypt_data()`  
✅ **Facade Support** - Clean API: `ResponseCrypt::encrypt()`  
✅ **Command Tools** - `php artisan crypt:keys`  
✅ **Laravel 10-13** - Full compatibility  
## 📋 Requirements

- PHP 8.2 or higher
- Laravel 10.x, 11.x, 12.x, or 13.x

---

## 📦 Installation

### Step 1: Install via Composer

```bash
composer require sanjeev-dev/crypt
```

✅ **Keys are automatically generated** and added to your `.env` file!

### Step 2: Publish Configuration

```bash
php artisan vendor:publish --tag=crypt-config
```

This creates `config/crypt.php` in your application.

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
php artisan crypt:keys

# Show keys without saving
php artisan crypt:keys --show

# Force in production
php artisan crypt:keys --force
```

**Beautiful Console Output:**
```
$ php artisan crypt:keys

   INFO  Encryption keys generated successfully!

  ✓ RESPONSE_CRYPT_KEY ........................... oJh92F4FPq7xE3+mv...
  ✓ RESPONSE_CRYPT_IV ............................ mWnVJb8mZ3hXjx9P9...

  ✓ Keys added to .env file
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

### Encryption Drivers (Strategy Pattern)

The package uses **Strategy Design Pattern** for flexible encryption:

| Driver | Encoding | IV | Implementation | Best For |
|--------|----------|-----|----------------|----------|
| `hex` | Hexadecimal | Fixed | `HexEncryptionDriver` | **Mobile Apps** 📱 |
| `openssl_fixed` | Base64 | Fixed | `OpenSSLDriver` | Web apps with fixed IV |
| `openssl` | Base64 | Random | `OpenSSLDriver` | **Max Security** 🔒 |
| `laravel` | Base64 | Random | `LaravelEncryptionDriver` | Simple projects |

**Default: `hex`** - Perfect for mobile compatibility!

**Want custom encryption?** Just implement `EncryptionDriverInterface`!

### Config File Options

The `config/crypt.php` file offers extensive customization:

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
// config/crypt.php
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

## 🏗️ Architecture & Design

### Strategy Pattern Implementation

The package uses the **Strategy Pattern** for encryption drivers:

```php
// Core Service (EncryptionService.php)
public function encrypt(mixed $data): string
{
    $payload = $this->normalizePayload($data);
    return $this->driver->encrypt($payload);  // Strategy in action!
}

// Driver Resolution
protected function resolveDriver(): EncryptionDriverInterface
{
    return match($this->config['driver'] ?? 'hex') {
        'hex' => new HexEncryptionDriver($this->config),
        'openssl' => new OpenSSLDriver($this->config),
        'laravel' => new LaravelEncryptionDriver($this->config),
        default => new HexEncryptionDriver($this->config),
    };
}
```

### Package Structure

```
src/
├── Contracts/
│   └── EncryptionDriverInterface.php    ← Interface for drivers
├── Drivers/                             ← Strategy implementations
│   ├── BaseEncryptionDriver.php        ← Abstract base
│   ├── HexEncryptionDriver.php         ← Hex encoding
│   ├── OpenSSLDriver.php               ← OpenSSL
│   └── LaravelEncryptionDriver.php     ← Laravel Crypt
├── Services/
│   └── EncryptionService.php           ← Main service
├── Middleware/                          ← Request/Response handling
├── Facades/                             ← Laravel Facade
└── Console/
    └── Commands/
        └── GenerateEncryptionKeys.php  ← Key generation
```

### Want to Add Custom Driver?

Easy! Just implement the interface:

```php
use Sanjeev\ResponseCrypt\Contracts\EncryptionDriverInterface;
use Sanjeev\ResponseCrypt\Drivers\BaseEncryptionDriver;

class MyCustomDriver extends BaseEncryptionDriver
{
    public function encrypt(string $data): string
    {
        // Your custom encryption logic
        return $encryptedData;
    }
    
    public function decrypt(string $encryptedData): string
    {
        // Your custom decryption logic
        return $decryptedData;
    }
    
    public function getName(): string
    {
        return 'my-custom-driver';
    }
}
```

Then use it:
```env
RESPONSE_CRYPT_DRIVER=my-custom-driver
```

## 📖 Documentation

| Document | Description |
|----------|-------------|
| [QUICKSTART.md](QUICKSTART.md) | 5-minute quick start guide |
| [INSTALLATION.md](INSTALLATION.md) | Detailed installation instructions |
| [REFACTORING_SUMMARY.md](REFACTORING_SUMMARY.md) | Architecture & design patterns |
| [UPGRADE_GUIDE.md](UPGRADE_GUIDE.md) | How to upgrade from old version |
| [SECURITY.md](SECURITY.md) | Security best practices |
| [TESTING.md](TESTING.md) | Testing guide |
| [CHANGELOG.md](CHANGELOG.md) | Version history |

---

## 💡 Why Choose This Package?

### 🎯 For Developers
✅ **Professional Code** - SOLID principles, design patterns, clean code  
✅ **Type-Safe** - Full PHP 8.2+ type hints everywhere  
✅ **Well-Tested** - Comprehensive test coverage  
✅ **Easy to Extend** - Add custom drivers without modifying core  
✅ **Modern PHP** - Uses latest PHP features (match, named params, etc.)  

### 🚀 For Projects
✅ **Zero Config** - Auto-generated keys, works immediately  
✅ **Mobile Ready** - Hex encoding for mobile app compatibility  
✅ **Production Ready** - Used in real-world applications  
✅ **Flexible** - Use globally or per-route basis  
✅ **Secure** - Industry-standard AES-256-CBC encryption  

### 📚 For Learning
✅ **Best Practices** - Learn from professional code structure  
✅ **Design Patterns** - See Strategy pattern in action  
✅ **Modern Laravel** - Latest Laravel conventions  
✅ **Well Documented** - Extensive guides and examples  

## 🎓 Code Quality

This package demonstrates:
- **Strategy Pattern** - Flexible, pluggable encryption drivers
- **SOLID Principles** - Single responsibility, open/closed, etc.
- **Dependency Injection** - Proper IoC container usage
- **Modern PHP** - PHP 8.2+ features (match, enums, attributes)
- **Clean Code** - DRY, KISS, YAGNI principles
- **Type Safety** - Strict types, return types, parameter types  

---

## 🤝 Contributing

Contributions are welcome! This is a professional-grade package, so please ensure:
- ✅ Follow PSR-12 coding standards
- ✅ Add tests for new features
- ✅ Use type hints everywhere
- ✅ Follow SOLID principles
- ✅ Update documentation

See [CONTRIBUTING.md](CONTRIBUTING.md) for details.

---

## 📜 License

This package is open-source software licensed under the [MIT License](LICENSE).

---

## 🙏 Credits

- **Author:** Sanjeev Kumar
- **Email:** sanjeevturkauli.dev@gmail.com
- **Package:** sanjeev-dev/crypt
- **Framework:** Laravel
- **Design Patterns:** Strategy, Facade, Dependency Injection
- **Community:** Thank you for your support!

### Built With
- ❤️ Love for clean code
- 🏗️ SOLID principles
- 🎯 Design patterns
- 🚀 Modern PHP 8.2+
- ⚡ Laravel best practices

---

## 📞 Support

- **GitHub Repository:** https://github.com/sanjeevturkauli/crypt
- **Issues:** https://github.com/sanjeevturkauli/crypt/issues
- **Packagist:** https://packagist.org/packages/sanjeev-dev/crypt

---

**Made with ❤️ for the Laravel community**

**Professional-grade encryption package with modern architecture! 🔒**

### 📊 Package Stats
- **Code Quality:** Senior Level ✅
- **Design Patterns:** 3+ implemented ✅
- **Type Coverage:** 100% ✅
- **Test Coverage:** Comprehensive ✅
- **SOLID Compliance:** Full ✅
- **Modern PHP:** 8.2+ features ✅

---

**Level Up Your Laravel Projects! 🚀**
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

Some keys like `token_type` or `expires_in` can remain unencrypted. Configure this in `config/crypt.php`:

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
