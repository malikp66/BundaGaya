<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    protected $message = 'Insufficient stock available';

    public function __construct(string $message = null, int $code = 422, Exception $previous = null)
    {
        parent::__construct($message ?? $this->message, $code, $previous);
    }
}
