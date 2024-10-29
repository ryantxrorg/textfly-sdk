<?php

namespace Ryantxr\Textfly\Sdk;

use GuzzleHttp\Client as HttpClient;
use Ryantxr\Textfly\Sdk\Exceptions\ApiException;

class Client
{
    protected $httpClient;
    protected $baseUrl;
    protected $apiKey;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->httpClient = new HttpClient();
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
    }

    public function setClient(HttpClient $client)
    {
        $this->httpClient = $client;
        return $this;
    }

    protected function request(string $method, string $uri, array $options = [])
    {
        $options['headers']['Authorization'] = "Bearer {$this->apiKey}";
        $options['headers']['Accept'] = 'application/json';

        try {
            $response = $this->httpClient->request($method, "{$this->baseUrl}{$uri}", $options);
            return json_decode($response->getBody()->getContents(), true);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Catch client exceptions (like 404) and throw ApiException
            // echo "\nCaught \\GuzzleHttp\\Exception\\ClientException with code {$e->getCode()}\n"; // Debugging line
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'Unknown error';
            throw new ApiException($responseBody, $e->getCode(), $e);
        } catch (\Exception $e) {
            // echo "\nCaught Exception with code {$e->getCode()}\n"; // Debugging line
            throw new ApiException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getContacts(int $accountId, int $page = 1, int $perPage = 10)
    {
        return $this->request('GET', "/api/v1/req/{$accountId}/contacts", [
            'query' => [
                'page' => $page,
                'per_page' => $perPage
            ]
        ]);
    }

    public function getContact(int $accountId, int $contactId)
    {
        return $this->request('GET', "/api/v1/req/{$accountId}/contacts/{$contactId}");
    }

    public function createContact(int $accountId, array $data)
    {
        return $this->request('PUT', "/api/v1/req/{$accountId}/contacts", [
            'json' => $data
        ]);
    }

    public function updateContact(int $accountId, int $contactId, array $data)
    {
        return $this->request('POST', "/api/v1/req/{$accountId}/contacts/{$contactId}", [
            'json' => $data
        ]);
    }

    public function deleteContact(int $accountId, int $contactId)
    {
        return $this->request('DELETE', "/api/v1/req/{$accountId}/contacts/{$contactId}");
    }
}
