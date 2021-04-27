<?php

namespace HotMaps\Contracts;

use Psr\Http\Message\ResponseInterface;

interface RequestClient
{
    public const RESPONSE_STATUS_OK = "OK";
    public const RESPONSE_STATUS_ERROR = "Error";
    public const RESPONSE_STATUS_NOT_FOUND = "Not found";

    public function authorize(string $login, string $pass): ResponseInterface;
    public function getUserByUsername(string $token, string $username): ResponseInterface;
    public function updateUser(string $token, int $id, string $userData): ResponseInterface;
}