<?php

namespace Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use HotMaps\App\ApiClient;
use HotMaps\Contracts\RequestClient;
use HotMaps\Exceptions\ApiClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use HotMaps\Exceptions\ApiClientResponseNotFoundException;
use HotMaps\Models\User;
use PHPUnit\Framework\TestCase;
use HotMaps\Services\Http\GuzzleRequestClient;

class ClientTest extends TestCase
{
    /** @test * */
    public function can_authorize(): void
    {
        $handlerStack = HandlerStack::create(
            new MockHandler([
                new Response(200, [], json_encode([
                    'status' => RequestClient::RESPONSE_STATUS_OK,
                    'token' => $token = 'secret_token'
                ], JSON_THROW_ON_ERROR)),
            ])
        );

        $client = new Client(['handler' => $handlerStack]);

        $apiClient = new ApiClient('test', '12345', new GuzzleRequestClient($client));

        $this->assertSame($token, $apiClient->authorize());
    }


    /** @test * */
    public function can_handle_api_get_user_exception(): void
    {
        $this->expectException(ApiClientException::class);
        $this->expectExceptionMessage("Server Error");

        $handlerStack = HandlerStack::create(
            new MockHandler([
                new RequestException("Bad response", new Request("GET", "")),
            ])
        );

        $client = new Client(['handler' => $handlerStack]);

        $apiClient = new ApiClient('test', '12345', new GuzzleRequestClient($client));

        $apiClient->getUserByUsername('test');
    }

    /** @test * */
    public function can_handle_api_get_user_not_found(): void
    {
        $this->expectException(ApiClientResponseNotFoundException::class);
        $this->expectExceptionMessage("Not Found");

        $handlerStack = HandlerStack::create(
            new MockHandler([
                new Response(200, [], json_encode([
                    'status' => RequestClient::RESPONSE_STATUS_OK,
                    'token' => $token = 'secret_token'
                ], JSON_THROW_ON_ERROR)),
                new Response(200, [], json_encode([
                    'status' => RequestClient::RESPONSE_STATUS_NOT_FOUND,
                ], JSON_THROW_ON_ERROR))
            ])
        );

        $client = new Client(['handler' => $handlerStack]);

        $apiClient = new ApiClient('test', '12345', new GuzzleRequestClient($client));

        $apiClient->getUserByUsername('test');
    }

    /** @test * */
    public function can_handle_api_get_user_data(): void
    {
        $handlerStack = HandlerStack::create(
            new MockHandler([
                new Response(200, [], json_encode([
                    'status' => RequestClient::RESPONSE_STATUS_OK,
                    'token' => $token = 'secret_token'
                ], JSON_THROW_ON_ERROR)),
                new Response(200, [], json_encode([
                    'status' => RequestClient::RESPONSE_STATUS_OK,
                    'active' => $isActive = 1,
                    'blocked' => $isBlocked = false,
                    'created_at' => $created = 1587457590,
                    'id' => $id = 23,
                    'name' => $name = 'Ivanov Ivan',
                    'permissions' => $permissions = [
                        [
                            "id" => 1,
                            "permission" => "comment"
                        ],
                        [
                            "id" => 2,
                            "permission" => "upload photo"
                        ],
                        [
                            "id" => 3,
                            "permission" => "add event"
                        ],
                    ]
                ], JSON_THROW_ON_ERROR)),
            ])
        );

        $client = new Client(['handler' => $handlerStack]);

        $apiClient = new ApiClient('test', '12345', new GuzzleRequestClient($client));

        $user = $apiClient->getUserByUsername('test');

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame($id, $user->getId());
        $this->assertSame($name, $user->getName());
        $this->assertSame($created, $user->getCreatedAt());
        $this->assertSame($isBlocked, $user->isBlocked());
        $this->assertSame($isActive, $user->getActive());
        $this->assertCount(count($permissions), $user->getPermissions());
    }

    /** @test * */
    public function can_update_user_successful(): void
    {
        $user = new User();
        $user->setId(100)
            ->setName('Test')
            ->setBlocked(false)
            ->setActive(1)
            ->setCreatedAt(1);

        $handlerStack = HandlerStack::create(
            new MockHandler([
                new Response(200, [], json_encode([
                    'status' => RequestClient::RESPONSE_STATUS_OK,
                    'token' => $token = 'secret_token'
                ], JSON_THROW_ON_ERROR)),
                new Response(200, [], json_encode([
                    'status' => RequestClient::RESPONSE_STATUS_OK,
                ], JSON_THROW_ON_ERROR)),
            ])
        );

        $client = new Client(['handler' => $handlerStack]);

        $apiClient = new ApiClient('test', '12345', new GuzzleRequestClient($client));

        $this->assertSame(true, $apiClient->updateUser($user));
    }
}