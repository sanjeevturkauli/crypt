<?php

declare(strict_types=1);

namespace SecureCrypto\Encryption\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use SecureCrypto\Encryption\Facades\ResponseCrypt;

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

        if (!config('secure-crypto.encrypt_response', true)) {
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
        // Check if user wants to disable encryption via header or query param
        if ($this->userWantsPlainResponse($request)) {
            return false;
        }

        if (!ResponseCrypt::shouldEncryptResponse($response)) {
            return false;
        }

        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return false;
        }

        if (!config('secure-crypto.encrypt_web_response', false)) {
            if (!$request->expectsJson() && !$request->is('api/*')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if user wants plain response (no encryption).
     */
    protected function userWantsPlainResponse(Request $request): bool
    {
        // Check via header: X-Disable-Encryption: true
        if ($request->header('X-Disable-Encryption') === 'true') {
            return true;
        }

        // Check via query parameter: ?encrypted=false
        if ($request->query('encrypted') === 'false' || $request->query('encrypted') === '0') {
            return true;
        }

        // Check via Accept header: Accept: application/json (plain)
        // vs Accept: application/vnd.encrypted+json (encrypted)
        $acceptHeader = $request->header('Accept', '');
        if (str_contains($acceptHeader, 'application/json') && 
            !str_contains($acceptHeader, 'encrypted')) {
            // If config allows plain responses via Accept header
            if (config('secure-crypto.allow_plain_via_accept', false)) {
                return true;
            }
        }

        return false;
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

        $excludedKeys = config('secure-crypto.excluded_keys', []);
        $dataToEncrypt = [];
        $unencryptedData = [];

        foreach ($originalData as $key => $value) {
            if (in_array($key, $excludedKeys)) {
                $unencryptedData[$key] = $value;
            } else {
                $dataToEncrypt[$key] = $value;
            }
        }

        $encryptedData = ResponseCrypt::encryptArray($dataToEncrypt);

        // Use custom response structure from config
        $finalResponse = $this->buildCustomResponse(
            $encryptedData['payload'] ?? '',
            $encryptedData['meta'] ?? [],
            $unencryptedData
        );

        // For minimal mode (string response), wrap in data key for JSON compatibility
        if (is_string($finalResponse)) {
            return response()->json($finalResponse, $response->status(), $response->headers->all())
                ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
        }

        return response()->json($finalResponse, $response->status(), $response->headers->all());
    }

    /**
     * Build custom response structure based on config.
     */
    protected function buildCustomResponse(string $payload, array $meta, array $unencryptedData): mixed
    {
        $mode = config('secure-crypto.response_mode', 'minimal');

        // Minimal mode - return only encrypted string
        if ($mode === 'minimal') {
            return $payload;
        }

        // Wrapped mode - standard response with metadata
        if ($mode === 'wrapped') {
            return array_merge($unencryptedData, [
                'success' => true,
                'data' => $payload,
                'encrypted' => true,
                'meta' => $meta,
            ]);
        }

        // Custom mode - use template from config
        $structure = config('secure-crypto.response_structure');

        if (empty($structure)) {
            return $payload; // Fallback to minimal
        }

        // Replace placeholders with actual values
        $response = $this->replacePlaceholders($structure, [
            '{payload}' => $payload,
            '{encrypted}' => true,
            '{algorithm}' => $meta['algorithm'] ?? 'hex',
            '{cipher}' => $meta['cipher'] ?? 'AES-256-CBC',
            '{timestamp}' => $meta['timestamp'] ?? now()->toIso8601String(),
            '{meta}' => $meta,
        ]);

        // Merge with unencrypted keys
        return array_merge($unencryptedData, $response);
    }

    /**
     * Replace placeholders in array recursively.
     */
    protected function replacePlaceholders(array $structure, array $values): array
    {
        $result = [];

        foreach ($structure as $key => $value) {
            // Skip null values (allows disabling fields)
            if ($value === null) {
                continue;
            }

            if (is_array($value)) {
                $result[$key] = $this->replacePlaceholders($value, $values);
            } elseif (is_string($value) && isset($values[$value])) {
                $result[$key] = $values[$value];
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
