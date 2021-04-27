<?php

namespace HotMaps\Services\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use HotMaps\Contracts\RequestClient;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleRequestClient implements RequestClient
{
    private Client $client;

    private string $login;
    private string $pass;
    private string $token;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function authorize(string $login, string $pass): ResponseInterface
    {
        $this->login = $login;
        $this->pass = $pass;

        $query = http_build_query([
            'login' => $this->login,
            'pass' => $this->pass,
        ]);

        $request = new Request('GET', '/auth?' . $query);

        return $this->sendRequest($request);
    }


    public function getUserByUsername(string $token, string $username): ResponseInterface
    {
        $this->token = $token;

        $request = new Request('GET', $this->generateUri("/get-user/{$username}"));

        return $this->sendRequest($request);
    }

    public function updateUser(string $token, int $id, string $userData): ResponseInterface
    {
        $this->token = $token;

        $request = new Request('POST', $this->generateUri("/user/{$id}/update"), [], $userData);

        return $this->sendRequest($request);
    }

    protected function generateUri(string $url): string
    {
        $query = http_build_query([
            'token' => $this->token,
        ]);

        return $url . '?' . $query;
    }

    protected function sendRequest(Request $request): ResponseInterface
    {
        return $this->client->sendRequest($request);
    }
}