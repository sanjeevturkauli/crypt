<?php

declare(strict_types=1);

namespace Sanjeev\ResponseCrypt;

use Illuminate\Support\ServiceProvider;
use Sanjeev\ResponseCrypt\Services\ResponseCryptService;
use Sanjeev\ResponseCrypt\Middleware\EncryptApiResponse;
use Sanjeev\ResponseCrypt\Middleware\DecryptApiRequest;
use Sanjeev\ResponseCrypt\Middleware\EncryptDecryptApi;

class ResponseCryptServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/response-crypt.php',
            'response-crypt'
        );

        $this->app->singleton('response-crypt', function ($app) {
            return new ResponseCryptService(
                config('response-crypt')
            );
        });

        $this->app->alias('response-crypt', ResponseCryptService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__ . '/../config/response-crypt.php' => config_path('response-crypt.php'),
            ], 'response-crypt-config');

            // Register commands
            $this->commands([
                \Sanjeev\ResponseCrypt\Console\GenerateKeysCommand::class,
            ]);

            // Auto-generate keys after package installation
            $this->autoGenerateKeysOnInstall();
        }

        $this->registerMiddleware();
    }

    /**
     * Auto-generate encryption keys when package is installed.
     */
    protected function autoGenerateKeysOnInstall(): void
    {
        // Check if keys don't exist in .env
        $envPath = $this->app->environmentFilePath();
        
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            
            // If keys don't exist, generate them
            $hasKey = preg_match('/^RESPONSE_CRYPT_KEY=/m', $envContent);
            $hasIV = preg_match('/^RESPONSE_CRYPT_IV=/m', $envContent);
            
            if (!$hasKey || !$hasIV) {
                $this->generateAndAppendKeys($envPath, $envContent, $hasKey, $hasIV);
            }
        }
    }

    /**
     * Generate and append encryption keys to .env file.
     */
    protected function generateAndAppendKeys(string $envPath, string $envContent, bool $hasKey, bool $hasIV): void
    {
        // Generate 32-byte key for AES-256
        $key = base64_encode(random_bytes(32));
        
        // Generate 16-byte IV for AES-256-CBC
        $iv = base64_encode(random_bytes(16));
        
        $newContent = $envContent;
        
        if (!$hasKey && !$hasIV) {
            $newContent .= "\n# Response Crypt Package - Auto-generated Keys\n";
        }
        
        if (!$hasKey) {
            $newContent .= 'RESPONSE_CRYPT_KEY="' . $key . '"' . "\n";
        }
        
        if (!$hasIV) {
            $newContent .= 'RESPONSE_CRYPT_IV="' . $iv . '"' . "\n";
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
        return ['response-crypt', ResponseCryptService::class];
    }
}
