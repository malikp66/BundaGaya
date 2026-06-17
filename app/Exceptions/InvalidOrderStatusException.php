<?php

namespace App\Exceptions;

use Exception;

class InvalidOrderStatusException extends Exception
{
    protected $message = 'Invalid order status transition';

    public function __construct(string $message = null, int $code = 422, Exception $previous = null)
    {
        parent::__construct($message ?? $this->message, $code, $previous);
    }
}
