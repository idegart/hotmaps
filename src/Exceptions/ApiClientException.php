<?php

namespace HotMaps\Exceptions;

use Exception;
use Throwable;

class ApiClientException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message ?: "Server Error", $code, $previous);
    }
}