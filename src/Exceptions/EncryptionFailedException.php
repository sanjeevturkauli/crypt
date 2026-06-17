<?php

declare(strict_types=1);

namespace Sanjeev\ResponseCrypt\Exceptions;

use Exception;

class EncryptionFailedException extends Exception
{
    protected $message = 'Encryption failed. Unable to encrypt the data.';

    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        if ($message !== '') {
            $this->message = $message;
        }

        parent::__construct($this->message, $code, $previous);
    }
}
