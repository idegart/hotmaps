<?php

namespace HotMaps\Exceptions;

use Throwable;

class ApiClientResponseNotFoundException extends ApiClientResponseException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message ?: "Not Found", $code, $previous);
    }
}