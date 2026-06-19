<?php

declare(strict_types=1);

namespace SecureCrypto\Encryption\Exceptions;

use Exception;

class DecryptionFailedException extends Exception
{
    protected $message = 'Decryption failed. The payload may be invalid or corrupted.';

    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        if ($message !== '') {
            $this->message = $message;
        }

        parent::__construct($this->message, $code, $previous);
    }
}
