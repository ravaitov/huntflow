<?php

namespace App\Exceptions;

class AppException extends \Exception
{
    public function __construct(
        string               $message = "",
        public readonly bool $terminate = false
    )
    {
        parent::__construct($message);
    }
}