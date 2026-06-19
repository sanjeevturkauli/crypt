<?php

declare(strict_types=1);

namespace Sanjeev\ResponseCrypt;

use Illuminate\Support\ServiceProvider;
use Sanjeev\ResponseCrypt\Services\EncryptionService;
use Sanjeev\ResponseCrypt\Middleware\{EncryptApiResponse, DecryptApiRequest, EncryptDecryptApi};

class ResponseCryptServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/crypt.php',
            'crypt'
        );

        $this->app->singleton('crypt.service', function ($app) {
            return new EncryptionService(
                config('crypt')
            );
        });

        $this->app->alias('crypt.service', EncryptionService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/crypt.php' => config_path('crypt.php'),
            ], 'crypt-config');

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
        $hasKey = $this->hasEnvironmentKey($envContent, 'RESPONSE_CRYPT_KEY');
        $hasIV = $this->hasEnvironmentKey($envContent, 'RESPONSE_CRYPT_IV');
        
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
            $newContent .= "\n# Response Crypt Package - Auto-generated Keys\n";
        }
        
        if (!$hasKey) {
            $newContent .= sprintf('RESPONSE_CRYPT_KEY="%s"%s', $keys['key'], PHP_EOL);
        }
        
        if (!$hasIV) {
            $newContent .= sprintf('RESPONSE_CRYPT_IV="%s"%s', $keys['iv'], PHP_EOL);
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
