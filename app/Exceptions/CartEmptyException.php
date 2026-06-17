<?php

namespace App\Exceptions;

use Exception;

class CartEmptyException extends Exception
{
    protected $message = 'Cart is empty';

    public function __construct(string $message = null, int $code = 422, Exception $previous = null)
    {
        parent::__construct($message ?? $this->message, $code, $previous);
    }
}
