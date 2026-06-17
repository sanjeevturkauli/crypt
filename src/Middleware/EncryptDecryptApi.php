<?php

declare(strict_types=1);

namespace Sanjeev\ResponseCrypt\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EncryptDecryptApi
{
    protected DecryptApiRequest $decryptMiddleware;
    protected EncryptApiResponse $encryptMiddleware;

    public function __construct(
        DecryptApiRequest $decryptMiddleware,
        EncryptApiResponse $encryptMiddleware
    ) {
        $this->decryptMiddleware = $decryptMiddleware;
        $this->encryptMiddleware = $encryptMiddleware;
    }

    /**
     * Handle an incoming request and outgoing response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $this->decryptMiddleware->handle(
            $request,
            fn($req) => $this->encryptMiddleware->handle($req, $next)
        );
    }
}
