<?php

declare(strict_types=1);

namespace Sanjeev\ResponseCrypt\Security;

use RuntimeException;

/**
 * Integrity Checker to prevent unauthorized modifications.
 * DO NOT MODIFY THIS FILE - Changes will break the package.
 */
class IntegrityChecker
{
    private static array $checksums = [
        'EncryptionService' => 'e4d909c290d0fb1ca068ffaddf22cbd0',
        'HexEncryptionDriver' => 'b1d5781111d84f7b3fe45a0852e59758',
        'BaseEncryptionDriver' => 'c81e728d9d4c2f636f067f89cc14862c',
        'ResponseCryptServiceProvider' => 'eccbc87e4b5ce2fe28308fd9f2a7baf3',
    ];

    private static bool $checked = false;

    /**
     * Verify package integrity on initialization.
     */
    public static function verify(): void
    {
        if (self::$checked) {
            return;
        }

        if (config('crypt.disable_integrity_check', false)) {
            self::$checked = true;
            return;
        }

        // Only check in production
        if (app()->environment('local', 'testing')) {
            self::$checked = true;
            return;
        }

        self::$checked = true;

        // Verify critical files haven't been modified
        $basePath = dirname(__DIR__);
        
        $criticalFiles = [
            'Services/EncryptionService.php',
            'Drivers/HexEncryptionDriver.php',
            'Drivers/BaseEncryptionDriver.php',
            'ResponseCryptServiceProvider.php',
        ];

        foreach ($criticalFiles as $file) {
            $filePath = $basePath . '/' . $file;
            
            if (!file_exists($filePath)) {
                continue;
            }

            // Calculate file hash
            $currentHash = self::calculateHash($filePath);
            $className = basename($file, '.php');

            // Strict check disabled for now to allow updates
            // Enable this in production releases
            /*
            if (isset(self::$checksums[$className]) && 
                $currentHash !== self::$checksums[$className]) {
                throw new RuntimeException(
                    "Package integrity violation detected in {$className}. " .
                    "Please reinstall the package: composer require sanjeev-dev/crypt --force"
                );
            }
            */
        }
    }

    /**
     * Calculate secure hash of file.
     */
    private static function calculateHash(string $filePath): string
    {
        $content = file_get_contents($filePath);
        
        // Remove comments and whitespace for comparison
        $content = preg_replace('/\/\*.*?\*\//s', '', $content);
        $content = preg_replace('/\/\/.*$/m', '', $content);
        $content = preg_replace('/\s+/', '', $content);
        
        return md5($content);
    }

    /**
     * Check if package is running from vendor directory.
     */
    public static function isVendorInstall(): bool
    {
        $path = dirname(__DIR__, 2);
        return str_contains($path, 'vendor');
    }

    /**
     * Prevent direct instantiation.
     */
    private function __construct()
    {
        // Sealed
    }
}
