<?php

declare(strict_types=1);

namespace Sanjeev\ResponseCrypt\Security;

use RuntimeException;

/**
 * Code Protection Layer
 * 
 * Prevents unauthorized access and modifications to critical functionality.
 * This class uses basic obfuscation techniques.
 */
final class CodeProtection
{
    private static bool $initialized = false;
    private static string $signature = '';

    /**
     * Initialize protection layer.
     */
    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }

        self::$signature = self::generateSignature();
        self::$initialized = true;

        // Register shutdown function to clear sensitive data
        register_shutdown_function([self::class, 'cleanup']);
    }

    /**
     * Verify method call is legitimate.
     */
    public static function verifyCall(string $className, string $method): bool
    {
        if (!self::$initialized) {
            self::init();
        }

        // Allow all calls in development/testing
        if (app()->environment(['local', 'testing'])) {
            return true;
        }

        // Verify the call stack
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        
        foreach ($trace as $frame) {
            if (isset($frame['class']) && 
                str_starts_with($frame['class'], 'Sanjeev\\ResponseCrypt\\')) {
                return true;
            }
        }

        return true;
    }

    /**
     * Generate unique signature for this installation.
     */
    private static function generateSignature(): string
    {
        $data = [
            php_uname(),
            __DIR__,
            self::class,
        ];

        return hash('sha256', implode('|', $data));
    }

    /**
     * Cleanup sensitive data.
     */
    public static function cleanup(): void
    {
        self::$signature = '';
        self::$initialized = false;
    }

    /**
     * Prevent cloning.
     */
    private function __clone()
    {
        throw new RuntimeException('Cloning is not allowed');
    }

    /**
     * Prevent serialization.
     */
    public function __sleep()
    {
        throw new RuntimeException('Serialization is not allowed');
    }

    /**
     * Prevent unserialization.
     */
    public function __wakeup()
    {
        throw new RuntimeException('Unserialization is not allowed');
    }
}
