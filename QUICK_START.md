# 🚀 Quick Start Guide

Get started with SecureCrypto Laravel Encryption in 3 simple steps!

## Step 1: Install

```bash
composer require securecrypto/laravel-encryption
```

✅ Encryption keys are **automatically generated** and added to your `.env` file!

## Step 2: Publish Config (Optional)

```bash
php artisan vendor:publish --tag=secure-crypto-config
```

## Step 3: Use in Routes

```php
use Illuminate\Support\Facades\Route;

// Encrypt responses only
Route::middleware(['response.encrypt'])->get('/api/users', function () {
    return response()->json([
        'users' => User::all()
    ]);
});

// Decrypt requests only
Route::middleware(['request.decrypt'])->post('/api/process', function () {
    $data = request()->all(); // Already decrypted!
    return response()->json(['status' => 'success']);
});

// Both encrypt & decrypt
Route::middleware(['api.crypt'])->group(function () {
    Route::post('/secure-transaction', [TransactionController::class, 'store']);
    Route::get('/profile', [ProfileController::class, 'show']);
});
```

## Enable/Disable in `.env`

```env
RESPONSE_CRYPT_ENABLED=true
RESPONSE_CRYPT_DRIVER=hex
```

## 📚 Next Steps

- **Full Documentation**: [README.md](README.md)
- **Installation Guide**: [INSTALLATION.md](INSTALLATION.md)
- **Examples**: [examples/basic-usage.php](examples/basic-usage.php)
- **JavaScript Client**: [examples/javascript-client.js](examples/javascript-client.js)

## ⚡ Quick Examples

### Using Facade

```php
use Sanjeev\ResponseCrypt\Facades\ResponseCrypt;

// Encrypt
$encrypted = ResponseCrypt::encrypt(['secret' => 'data']);

// Decrypt
$decrypted = ResponseCrypt::decrypt($encrypted);
```

### Using Helper Functions

```php
// Encrypt
$encrypted = encrypt_data(['user_id' => 123]);

// Decrypt
$decrypted = decrypt_data($encrypted);
```

### Conditional Middleware

```php
$middlewares = env('RESPONSE_CRYPT_ENABLED', false) 
    ? ['request.decrypt', 'response.encrypt'] 
    : [];

Route::middleware($middlewares)->group(function () {
    // Your routes here
});
```

## 🔒 Security Notes

1. **Never commit** encryption keys to version control
2. **Use HTTPS** - Encryption complements, doesn't replace TLS
3. **Rotate keys** regularly for sensitive applications
4. **Validate input** after decryption

## 📞 Support

- **GitHub**: https://github.com/securecrypto/laravel-encryption
- **Issues**: https://github.com/securecrypto/laravel-encryption/issues
- **Packagist**: https://packagist.org/packages/securecrypto/laravel-encryption

---

**That's it! You're ready to secure your API! 🎉**
