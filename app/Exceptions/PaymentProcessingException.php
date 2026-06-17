<?php

namespace App\Exceptions;

use Exception;

class PaymentProcessingException extends Exception
{
    protected $message = 'Payment processing failed';

    public function __construct(string $message = null, int $code = 500, Exception $previous = null)
    {
        parent::__construct($message ?? $this->message, $code, $previous);
    }
}
