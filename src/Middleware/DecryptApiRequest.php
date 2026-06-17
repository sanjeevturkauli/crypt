<?php

declare(strict_types=1);

namespace Sanjeev\ResponseCrypt\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Sanjeev\ResponseCrypt\Facades\ResponseCrypt;
use Sanjeev\ResponseCrypt\Exceptions\DecryptionFailedException;

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

        if (!config('response-crypt.decrypt_request', true)) {
            return $next($request);
        }

        if (ResponseCrypt::shouldSkipRequest($request)) {
            return $next($request);
        }

        if (!$this->shouldDecrypt($request)) {
            return $next($request);
        }

        try {
            $payloadKey = config('response-crypt.request_payload_key', 'payload');

            if ($request->has($payloadKey)) {
                $decryptedData = ResponseCrypt::decryptArray($request->all());
                $request->merge($decryptedData);
                $request->request->remove($payloadKey);
            }
        } catch (DecryptionFailedException $e) {
            return response()->json(
                config('response-crypt.error_response', [
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

        if ($request->hasFile(config('response-crypt.request_payload_key', 'payload'))) {
            return false;
        }

        $contentType = $request->header('Content-Type', '');
        if (str_contains($contentType, 'multipart/form-data')) {
            return false;
        }

        return true;
    }
}
