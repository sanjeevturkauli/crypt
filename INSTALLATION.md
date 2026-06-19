# Installation Guide

Complete installation guide for Laravel Response Crypt package.

## Table of Contents

- [Requirements](#requirements)
- [Installation Steps](#installation-steps)
- [Configuration](#configuration)
- [Verification](#verification)
- [Troubleshooting](#troubleshooting)

## Requirements

Before installing this package, ensure your system meets these requirements:

- **PHP**: 8.2 or higher
- **Laravel**: 10.x, 11.x, 12.x, or 13.x
- **Composer**: Latest version recommended
- **OpenSSL Extension**: Enabled (usually enabled by default)

### Check Your PHP Version

```bash
php -v
```

Expected output: `PHP 8.2.x` or higher

### Check Laravel Version

```bash
php artisan --version
```

Expected output: `Laravel Framework 10.x.x` or higher

## Installation Steps

### Step 1: Install via Composer

Open your terminal in your Laravel project root directory and run:

```bash
composer require sanjeev-dev/crypt
```

**Expected Output:**
```
Using version ^1.0 for sanjeev-dev/crypt
./composer.json has been updated
Running composer update sanjeev-dev/crypt
Loading composer repositories with package information
Updating dependencies
Lock file operations: 1 install, 0 updates, 0 removals
  - Installing sanjeev-dev/crypt (1.0.0)
Package operations: 1 install, 0 updates, 0 removals
  - Installing sanjeev-dev/crypt (1.0.0): Extracting archive
Generating autoload files
```

### Step 2: Verify Installation

Check if the package is installed:

```bash
composer show sanjeev-dev/crypt
```

You should see package details including version, description, and dependencies.

### Step 3: Publish Configuration (Optional)

Publish the configuration file to customize package settings:

```bash
php artisan vendor:publish --tag=crypt-config
```

This creates `config/crypt.php` in your Laravel application.

**Expected Output:**
```
Copied File [/vendor/sanjeev-dev/crypt/config/crypt.php] To [/config/crypt.php]
Publishing complete.
```

### Step 4: Clear Cache

Clear application cache to ensure the package is properly loaded:

```bash
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
```

## Configuration

### Environment Variables

Add these variables to your `.env` file:

```env
# Enable/Disable encryption globally
RESPONSE_CRYPT_ENABLED=true

# Encryption driver: laravel or openssl
RESPONSE_CRYPT_DRIVER=laravel

# Custom encryption key (optional, defaults to APP_KEY)
RESPONSE_CRYPT_KEY="${APP_KEY}"

# Enable logging (for debugging only)
RESPONSE_CRYPT_LOG_ENABLED=false
```

### Config File Settings

Edit `config/crypt.php` if published:

```php
return [
    // Enable/disable globally
    'enabled' => env('RESPONSE_CRYPT_ENABLED', true),
    
    // Encryption driver
    'driver' => env('RESPONSE_CRYPT_DRIVER', 'laravel'),
    
    // Enable response encryption
    'encrypt_response' => true,
    
    // Enable request decryption
    'decrypt_request' => true,
    
    // Response wrapper key
    'response_wrapper_key' => 'payload',
    
    // Request payload key
    'request_payload_key' => 'payload',
    
    // Excluded routes (won't be encrypted)
    'excluded_routes' => [
        'login',
        'register',
        'sanctum/csrf-cookie',
    ],
    
    // Keys to exclude from encryption
    'excluded_keys' => [
        'token_type',
        'expires_in',
    ],
];
```

## Verification

### Method 1: Check Service Provider

Verify the service provider is auto-discovered:

```bash
php artisan package:discover
```

Look for `Sanjeev\ResponseCrypt\ResponseCryptServiceProvider` in the output.

### Method 2: Check Middleware

List all registered middleware:

```bash
php artisan route:list
```

The package registers these middleware aliases:
- `response.encrypt`
- `request.decrypt`
- `api.crypt`

### Method 3: Test with Tinker

```bash
php artisan tinker
```

```php
// Test encryption
use Sanjeev\ResponseCrypt\Facades\ResponseCrypt;

$data = ['message' => 'Hello World'];
$encrypted = ResponseCrypt::encrypt($data);
echo $encrypted;

$decrypted = ResponseCrypt::decrypt($encrypted);
var_dump($decrypted);
```

### Method 4: Create Test Route

Add this to `routes/api.php`:

```php
Route::middleware(['response.encrypt'])->get('/test-encrypt', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Encryption is working!',
        'timestamp' => now()->toIso8601String(),
    ]);
});
```

Test it:

```bash
curl http://your-app.test/api/test-encrypt
```

**Expected Response:**
```json
{
  "encrypted": true,
  "payload": "eyJpdiI6IjRGNnNMOE1XYnhOV...",
  "meta": {
    "algorithm": "laravel",
    "timestamp": "2026-06-19T10:30:00Z"
  }
}
```

## Basic Usage

### Apply Middleware to Routes

#### Single Route
```php
Route::middleware(['api.crypt'])->post('/secure-endpoint', [Controller::class, 'method']);
```

#### Route Group
```php
Route::middleware(['api.crypt'])->prefix('api')->group(function () {
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/profile', [ProfileController::class, 'show']);
});
```

#### Controller
```php
class SecureController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.crypt');
    }
    
    public function store(Request $request)
    {
        // Request is automatically decrypted
        $data = $request->all();
        
        // Response will be automatically encrypted
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }
}
```

### 🆕 Control Encryption via Request (v1.4.0+)

Users can disable encryption per request:

```php
// In your API client or Postman
// Add header: X-Disable-Encryption: true

// Or use query parameter
GET /api/users?encrypted=false
```

This is useful for debugging or when encryption is not needed.

### 🆕 Customize Response Structure (v1.4.0+)

Edit `config/crypt.php`:

```php
'response_structure' => [
    'success' => true,
    'status' => 200,
    'message' => 'success',
    'data' => '{payload}',     // Your encrypted data
    'encrypted' => '{encrypted}',
    'meta' => null,            // Set to null to hide metadata
],
```

Now your API returns:
```json
{
  "success": true,
  "status": 200,
  "message": "success",
  "data": "encrypted_payload_here",
  "encrypted": true
}
```

### Using Facade
```php
use Sanjeev\ResponseCrypt\Facades\ResponseCrypt;

// Encrypt
$encrypted = ResponseCrypt::encrypt(['key' => 'value']);

// Decrypt
$decrypted = ResponseCrypt::decrypt($encrypted);
```

### Using Helper Functions
```php
// Encrypt
$encrypted = encrypt_data(['key' => 'value']);

// Decrypt
$decrypted = decrypt_data($encrypted);
```

## Troubleshooting

### Issue 1: "Class 'ResponseCrypt' not found"

**Solution:**
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Issue 2: "Package sanjeev-dev/crypt not found"

**Possible Causes:**
- Package not on Packagist yet
- Composer cache issue

**Solution:**
```bash
# Clear composer cache
composer clear-cache

# Try installing again
composer require sanjeev-dev/crypt

# Or specify version
composer require sanjeev-dev/crypt:^1.4
```

### Issue 3: "Your requirements could not be resolved"

**Cause:** PHP or Laravel version incompatibility

**Solution:**
```bash
# Check your PHP version
php -v

# Check Laravel version
php artisan --version

# Ensure PHP >= 8.2 and Laravel >= 10.0
```

### Issue 4: Middleware Not Working

**Solution:**
```bash
# Clear all caches
php artisan optimize:clear

# Re-publish config
php artisan vendor:publish --tag=crypt-config --force

# Restart server
php artisan serve
```

### Issue 5: "Decryption Failed" Error

**Causes:**
- Encryption key mismatch
- Corrupted payload
- Wrong encryption driver

**Solution:**
```bash
# Check .env file
# Ensure APP_KEY is set
php artisan key:generate

# Check config
php artisan config:show crypt

# Verify driver setting
# In .env: RESPONSE_CRYPT_DRIVER=laravel
```

### Issue 6: Service Provider Not Loading

**Solution:**

1. Check `composer.json` has auto-discovery:
```json
"extra": {
    "laravel": {
        "providers": [
            "Sanjeev\\ResponseCrypt\\ResponseCryptServiceProvider"
        ]
    }
}
```

2. Manually register in `config/app.php` (if needed):
```php
'providers' => [
    // Other providers...
    Sanjeev\ResponseCrypt\ResponseCryptServiceProvider::class,
],

'aliases' => [
    // Other aliases...
    'ResponseCrypt' => Sanjeev\ResponseCrypt\Facades\ResponseCrypt::class,
],
```

### Issue 7: Routes Still Not Encrypted

**Checklist:**
- [ ] Is `RESPONSE_CRYPT_ENABLED=true` in `.env`?
- [ ] Is the route in the excluded routes list?
- [ ] Is middleware applied correctly?
- [ ] Are you testing the correct endpoint?

**Debug:**
```php
// Add to route
Route::middleware(['response.encrypt'])->get('/debug', function () {
    dd([
        'enabled' => config('response-crypt.enabled'),
        'driver' => config('response-crypt.driver'),
        'encrypt_response' => config('response-crypt.encrypt_response'),
    ]);
});
```

## Installation in Existing Project

If adding to an existing Laravel project:

```bash
# 1. Backup your project
git add .
git commit -m "Before installing response-crypt"

# 2. Install package
composer require sanjeev-dev/crypt

# 3. Publish config
php artisan vendor:publish --tag=crypt-config

# 4. Test in development first
RESPONSE_CRYPT_ENABLED=true

# 5. Apply middleware selectively
# Start with one route, then expand

# 6. Test thoroughly before production
```

## Uninstalling

If you need to remove the package:

```bash
# 1. Remove middleware from routes

# 2. Remove config file
rm config/crypt.php

# 3. Remove from composer
composer remove sanjeev-dev/crypt

# 4. Clear cache
php artisan optimize:clear
```

## Next Steps

After successful installation:

1. **Read the README**: [README.md](README.md)
2. **Check Examples**: [examples/basic-usage.php](examples/basic-usage.php)
3. **Review Configuration**: `config/crypt.php`
4. **Test Your Implementation**: Create test routes
5. **Integrate with Frontend**: [examples/javascript-client.js](examples/javascript-client.js)

## Support

If you encounter issues:

1. Check this installation guide
2. Review [README.md](README.md) for usage examples
3. Check GitHub issues: https://github.com/sanjeevturkauli/crypt/issues
4. Create a new issue with:
   - Laravel version
   - PHP version
   - Error message
   - Steps to reproduce

## Version History

- **v1.0.0** - Initial release
- **v1.1.0** - Bug fixes and improvements

---

**Happy Coding! 🚀**

For detailed usage instructions, see [README.md](README.md)
