<?php

declare(strict_types=1);

namespace Sanjeev\ResponseCrypt\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Sanjeev\ResponseCrypt\Facades\ResponseCrypt;

class EncryptApiResponse
{
    /**
     * Handle an outgoing response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!ResponseCrypt::isEnabled()) {
            return $response;
        }

        if (!config('response-crypt.encrypt_response', true)) {
            return $response;
        }

        if (ResponseCrypt::shouldSkipRequest($request)) {
            return $response;
        }

        if (!$this->shouldEncrypt($request, $response)) {
            return $response;
        }

        return $this->encryptResponse($response);
    }

    /**
     * Determine if response should be encrypted.
     */
    protected function shouldEncrypt(Request $request, Response $response): bool
    {
        if (!ResponseCrypt::shouldEncryptResponse($response)) {
            return false;
        }

        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return false;
        }

        if (!config('response-crypt.encrypt_web_response', false)) {
            if (!$request->expectsJson() && !$request->is('api/*')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Encrypt the response.
     */
    protected function encryptResponse(JsonResponse $response): JsonResponse
    {
        $originalData = json_decode($response->getContent(), true);

        if (!is_array($originalData)) {
            return $response;
        }

        $excludedKeys = config('response-crypt.excluded_keys', []);
        $dataToEncrypt = [];
        $unencryptedData = [];

        foreach ($originalData as $key => $value) {
            if (in_array($key, $excludedKeys)) {
                $unencryptedData[$key] = $value;
            } else {
                $dataToEncrypt[$key] = $value;
            }
        }

        $encryptedResponse = ResponseCrypt::encryptArray($dataToEncrypt);

        $finalResponse = array_merge($unencryptedData, $encryptedResponse);

        return response()->json($finalResponse, $response->status(), $response->headers->all());
    }
}
