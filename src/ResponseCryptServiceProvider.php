<?php

declare(strict_types=1);

namespace SecureCrypto\Encryption;

use Illuminate\Support\ServiceProvider;
use SecureCrypto\Encryption\Services\EncryptionService;
use SecureCrypto\Encryption\Middleware\{EncryptApiResponse, DecryptApiRequest, EncryptDecryptApi};
use SecureCrypto\Encryption\Security\{IntegrityChecker, CodeProtection, LicenseValidator};

class ResponseCryptServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Initialize security layer
        CodeProtection::init();
        
        $this->mergeConfigFrom(
            __DIR__ . '/../config/secure-crypto.php',
            'secure-crypto'
        );

        $this->app->singleton('crypt.service', function ($app) {
            return new EncryptionService(
                config('secure-crypto')
            );
        });

        $this->app->alias('crypt.service', EncryptionService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Verify package integrity
        IntegrityChecker::verify();
        
        // Validate license
        if (!LicenseValidator::validate() && !app()->environment('testing')) {
            logger()->warning('ResponseCrypt: Package integrity check failed');
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/secure-crypto.php' => config_path('secure-crypto.php'),
            ], 'secure-crypto-config');

            // Register commands
            $this->commands([
                Console\Commands\GenerateEncryptionKeys::class,
                Console\Commands\CleanupPackage::class,
            ]);

            $this->autoGenerateKeysOnInstall();
        }

        $this->registerMiddleware();
    }

    /**
     * Auto-generate encryption keys on package installation.
     */
    protected function autoGenerateKeysOnInstall(): void
    {
        $envPath = $this->app->environmentFilePath();
        
        if (!file_exists($envPath)) {
            return;
        }

        $envContent = file_get_contents($envPath);
        $hasKey = $this->hasEnvironmentKey($envContent, 'CRYPT_KEY');
        $hasIV = $this->hasEnvironmentKey($envContent, 'CRYPT_IV');
        
        if (!$hasKey || !$hasIV) {
            $this->generateAndAppendKeys($envPath, $envContent, $hasKey, $hasIV);
        }
    }

    /**
     * Check if environment key exists.
     */
    protected function hasEnvironmentKey(string $content, string $key): bool
    {
        return (bool) preg_match("/^{$key}=/m", $content);
    }

    /**
     * Generate and append encryption keys to .env file.
     */
    protected function generateAndAppendKeys(
        string $envPath,
        string $envContent,
        bool $hasKey,
        bool $hasIV
    ): void {
        $keys = [
            'key' => base64_encode(random_bytes(32)),
            'iv' => base64_encode(random_bytes(16)),
        ];
        
        $newContent = $envContent;
        
        if (!$hasKey && !$hasIV) {
            $newContent .= "\n# Encryption Keys\n";
        }
        
        if (!$hasKey) {
            $newContent .= sprintf('CRYPT_KEY="%s"%s', $keys['key'], PHP_EOL);
        }
        
        if (!$hasIV) {
            $newContent .= sprintf('CRYPT_IV="%s"%s', $keys['iv'], PHP_EOL);
        }
        
        file_put_contents($envPath, $newContent);
    }

    /**
     * Register middleware.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];

        // Register middleware aliases
        $router->aliasMiddleware('response.encrypt', EncryptApiResponse::class);
        $router->aliasMiddleware('request.decrypt', DecryptApiRequest::class);
        $router->aliasMiddleware('api.crypt', EncryptDecryptApi::class);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return ['crypt.service', EncryptionService::class];
    }
}
