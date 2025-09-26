<?php

namespace TextFly\Sdk\Api;

use TextFly\Sdk\Client;

abstract class AbstractApi
{
    protected Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    protected function request(string $method, string $uri, array $options = []): mixed
    {
        return $this->client->request($method, $uri, $options);
    }

    protected function accountPath(int $accountId, string $resource): string
    {
        return "/api/v1/req/{$accountId}/{$resource}";
    }
}
