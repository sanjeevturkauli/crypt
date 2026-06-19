<?php

declare(strict_types=1);

namespace SecureCrypto\Encryption\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string encrypt(mixed $data)
 * @method static mixed decrypt(string $payload)
 * @method static array encryptArray(array $data)
 * @method static array decryptArray(array $data)
 * @method static bool isEnabled()
 * @method static bool shouldSkipRequest(\Illuminate\Http\Request $request)
 * @method static bool shouldEncryptResponse($response)
 *
 * @see \Sanjeev\ResponseCrypt\Services\EncryptionService
 */
class ResponseCrypt extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'crypt.service';
    }
}
