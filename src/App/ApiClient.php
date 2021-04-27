<?php

namespace HotMaps\App;

use HotMaps\Contracts\RequestClient;
use HotMaps\Exceptions\ApiClientException;
use HotMaps\Exceptions\ApiClientResponseException;
use HotMaps\Exceptions\ApiClientResponseNotFoundException;
use HotMaps\Generators\UserGenerator;
use HotMaps\Models\User;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ApiClient
{
    private RequestClient $requestClient;

    private string $login;
    private string $pass;
    private string $token;

    public function __construct(string $login, string $pass, RequestClient $requestClient)
    {
        $this->requestClient = $requestClient;

        $this->login = $login;
        $this->pass = $pass;
    }

    public function authorize(): string
    {
        $response = $this->requestClient->authorize($this->login, $this->pass);

        $content = $this->formatResponse($response);

        $token = $content['token'] ?? null;

        if (!$token) {
            throw new ApiClientResponseException("Token not provided");
        }

        $this->token = $token;

        return $token;
    }

    public function getUserByUsername(string $username): User
    {
        try {
            $response = $this->requestClient->getUserByUsername($this->ensureToken(), $username);
        } catch (Throwable $exception) {
            throw new ApiClientResponseException();
        }

        $content = $this->formatResponse($response);

        return UserGenerator::fromArray($content);
    }

    public function updateUser(User $user): bool
    {
        try {
            $response = $this->requestClient->updateUser(
                $this->ensureToken(),
                $user->getId(),
                UserGenerator::toJson($user)
            );
        } catch (Throwable $exception) {
            throw new ApiClientResponseException();
        }

        $this->formatResponse($response);

        return true;
    }

    protected function ensureToken(): string
    {
        if (!isset($this->token)) {
            $this->authorize();
        }

        return $this->token;
    }

    protected function formatResponse(ResponseInterface $response): array
    {
        $content = $response->getBody()->getContents();

        try {
            $contentData = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            throw new ApiClientException();
        }

        if (!isset($contentData['status']) || $contentData['status'] === RequestClient::RESPONSE_STATUS_ERROR) {
            throw new ApiClientResponseException();
        }

        if ($contentData['status'] === RequestClient::RESPONSE_STATUS_NOT_FOUND) {
            throw new ApiClientResponseNotFoundException();
        }

        return $contentData;
    }
}