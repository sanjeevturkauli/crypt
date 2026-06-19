<?php

declare(strict_types=1);

namespace SecureCrypto\Encryption\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use SecureCrypto\Encryption\Facades\ResponseCrypt;
use SecureCrypto\Encryption\Exceptions\DecryptionFailedException;

class DecryptApiRequest
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!ResponseCrypt::isEnabled()) {
            return $next($request);
        }

        if (!config('secure-crypto.decrypt_request', true)) {
            return $next($request);
        }

        if (ResponseCrypt::shouldSkipRequest($request)) {
            return $next($request);
        }

        if (!$this->shouldDecrypt($request)) {
            return $next($request);
        }

        try {
            $payloadKey = config('secure-crypto.request_payload_key', 'payload');

            if ($request->has($payloadKey)) {
                $decryptedData = ResponseCrypt::decryptArray($request->all());
                $request->merge($decryptedData);
                $request->request->remove($payloadKey);
            }
        } catch (DecryptionFailedException $e) {
            return response()->json(
                config('secure-crypto.error_response', [
                    'status' => false,
                    'message' => 'Invalid encrypted payload.',
                    'error' => 'DECRYPTION_FAILED',
                ]),
                422
            );
        }

        return $next($request);
    }

    /**
     * Determine if the request should be decrypted.
     */
    protected function shouldDecrypt(Request $request): bool
    {
        if (!$request->isJson() && !$request->expectsJson()) {
            return false;
        }

        if ($request->isMethod('GET')) {
            return false;
        }

        if ($request->hasFile(config('secure-crypto.request_payload_key', 'payload'))) {
            return false;
        }

        $contentType = $request->header('Content-Type', '');
        if (str_contains($contentType, 'multipart/form-data')) {
            return false;
        }

        return true;
    }
}
