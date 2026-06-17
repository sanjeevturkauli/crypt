<?php

/**
 * Basic Usage Examples for Response Crypt Package
 */

use Illuminate\Support\Facades\Route;
use Sanjeev\ResponseCrypt\Facades\ResponseCrypt;

// ============================================
// Example 1: Encrypt Response Only
// ============================================
Route::middleware(['response.encrypt'])->get('/api/users', function () {
    return response()->json([
        'status' => true,
        'data' => [
            'id' => 1,
            'name' => 'Sanjeev Kumar',
            'email' => 'sanjeev@example.com',
            'role' => 'Developer',
        ],
    ]);
});

// Response will be:
// {
//   "encrypted": true,
//   "payload": "eyJpdiI6IjRGNnNMOE...",
//   "meta": {
//     "algorithm": "laravel",
//     "timestamp": "2026-06-17T10:30:00Z"
//   }
// }

// ============================================
// Example 2: Decrypt Request Only
// ============================================
Route::middleware(['request.decrypt'])->post('/api/process', function () {
    $data = request()->all();
    
    return response()->json([
        'status' => true,
        'message' => 'Data received and decrypted',
        'received' => $data,
    ]);
});

// Request format:
// {
//   "payload": "eyJpdiI6IjRGNnNMOE..."
// }

// ============================================
// Example 3: Both Encrypt and Decrypt
// ============================================
Route::middleware(['api.crypt'])->post('/api/secure-transaction', function () {
    $data = request()->all();
    
    // Process the decrypted data
    $result = [
        'transaction_id' => uniqid(),
        'status' => 'completed',
        'amount' => $data['amount'] ?? 0,
        'timestamp' => now()->toIso8601String(),
    ];
    
    return response()->json($result);
});

// ============================================
// Example 4: Manual Encryption Using Facade
// ============================================
Route::post('/api/custom-encrypt', function () {
    $sensitiveData = [
        'card_number' => '1234567890123456',
        'cvv' => '123',
        'expiry' => '12/25',
    ];
    
    $encrypted = ResponseCrypt::encrypt($sensitiveData);
    
    return response()->json([
        'encrypted_data' => $encrypted,
        'message' => 'Data encrypted successfully',
    ]);
});

// ============================================
// Example 5: Manual Decryption Using Facade
// ============================================
Route::post('/api/custom-decrypt', function () {
    $encryptedPayload = request()->input('encrypted_data');
    
    try {
        $decrypted = ResponseCrypt::decrypt($encryptedPayload);
        
        return response()->json([
            'status' => true,
            'data' => $decrypted,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Decryption failed',
        ], 422);
    }
});

// ============================================
// Example 6: Using Helper Functions
// ============================================
Route::post('/api/helper-example', function () {
    // Encrypt using helper
    $data = ['user_id' => 123, 'action' => 'login'];
    $encrypted = encrypt_data($data);
    
    // Decrypt using helper
    $decrypted = decrypt_data($encrypted);
    
    return response()->json([
        'original' => $data,
        'encrypted' => $encrypted,
        'decrypted' => $decrypted,
    ]);
});

// ============================================
// Example 7: Exclude Specific Response Keys
// ============================================
Route::middleware(['response.encrypt'])->post('/api/auth/login', function () {
    return response()->json([
        'token_type' => 'Bearer',        // Not encrypted (excluded key)
        'expires_in' => 3600,             // Not encrypted (excluded key)
        'access_token' => 'secret-token', // Will be encrypted
        'user' => [                       // Will be encrypted
            'id' => 1,
            'name' => 'John Doe',
        ],
    ]);
});

// Response will be:
// {
//   "token_type": "Bearer",
//   "expires_in": 3600,
//   "encrypted": true,
//   "payload": "eyJpdiI6IjRGNnNMOE..."
// }

// ============================================
// Example 8: Protected Route Group
// ============================================
Route::middleware(['api.crypt', 'auth:sanctum'])->prefix('secure')->group(function () {
    Route::post('/transactions', function () {
        return response()->json([
            'status' => 'success',
            'transaction' => [
                'id' => 'TXN123',
                'amount' => 1000,
                'currency' => 'USD',
            ],
        ]);
    });
    
    Route::get('/profile', function () {
        return response()->json([
            'status' => 'success',
            'profile' => auth()->user(),
        ]);
    });
});
