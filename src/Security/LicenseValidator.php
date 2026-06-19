<?php

declare(strict_types=1);

namespace Sanjeev\ResponseCrypt\Security;

/**
 * License Validator - Ensures proper usage of the package.
 * 
 * This is open-source software under MIT License.
 * Modifications are allowed but must retain attribution.
 */
class LicenseValidator
{
    private const PACKAGE_NAME = 'sanjeev-dev/crypt';
    private const AUTHOR = 'Sanjeev Kumar';
    private const LICENSE = 'MIT';
    
    /**
     * Validate license and attribution.
     */
    public static function validate(): bool
    {
        // Check if running in vendor directory
        if (!IntegrityChecker::isVendorInstall()) {
            return true;
        }

        // Verify composer.json exists and has correct package name
        $composerPath = dirname(__DIR__, 2) . '/composer.json';
        
        if (!file_exists($composerPath)) {
            return false;
        }

        $composer = json_decode(file_get_contents($composerPath), true);
        
        if (!isset($composer['name']) || $composer['name'] !== self::PACKAGE_NAME) {
            return false;
        }

        return true;
    }

    /**
     * Get license information.
     */
    public static function getLicenseInfo(): array
    {
        return [
            'package' => self::PACKAGE_NAME,
            'author' => self::AUTHOR,
            'license' => self::LICENSE,
            'repository' => 'https://github.com/sanjeevturkauli/crypt',
        ];
    }
}
