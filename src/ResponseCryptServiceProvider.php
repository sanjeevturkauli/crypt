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
            $this->publishes([
                __DIR__ . '/../config/response-crypt.php' => config_path('response-crypt.php'),
            ], 'response-crypt-config');
        }

        $this->registerMiddleware();
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
