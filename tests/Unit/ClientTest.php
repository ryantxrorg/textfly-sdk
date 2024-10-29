<?php

use PHPUnit\Framework\TestCase;
use Ryantxr\Textfly\Sdk\Client as TextflyClient;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Response;

class ClientTest extends TestCase
{
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testCreateContact()
    {
        // Arrange: Set up the expected response data and the client mock
        $mockResponseData = [
            'id' => 1,
            'phone' => '212-555-6789',
            'first_name' => 'Alice',
            'last_name' => 'Johnson',
            'optin' => 1,
            'optin_at' => '2023-01-01T12:00:00Z',
            'accept_tos' => 1,
            'accept_tos_at' => '2023-01-01T12:00:00Z'
        ];

        $mockResponse = new Response(201, [], json_encode($mockResponseData));

        $httpClientMock = \Mockery::mock(HttpClient::class);
        $httpClientMock->shouldReceive('request')
            ->once()
            ->with('PUT', 'https://textfl8.local/api/v1/req/1/contacts', Mockery::any())
            ->andReturn($mockResponse);

        // Act: Instantiate the Client and call createContact
        $client = new TextflyClient('https://textfl8.local', 'your_api_key');
        $client->setClient($httpClientMock); // Inject mock
        $result = $client->createContact(1, [
            'phone' => '212-555-6789',
            'first_name' => 'Alice',
            'last_name' => 'Johnson',
            'optin' => 1,
            'accept_tos' => 1,
        ]);

        // Assert: Check if the result matches the expected response
        $this->assertEquals($mockResponseData, $result);
    }

    public function testUpdateContact()
    {
        // Arrange: Set up the expected response data and the client mock
        $mockResponseData = [
            'id' => 1,
            'phone' => '212-555-6789',
            'first_name' => 'Alice',
            'last_name' => 'Johnson',
            'optin' => 0,
            'optin_at' => null,
            'accept_tos' => 0,
            'accept_tos_at' => null
        ];

        $mockResponse = new Response(200, [], json_encode($mockResponseData));

        $httpClientMock = \Mockery::mock(HttpClient::class);
        $httpClientMock->shouldReceive('request')
            ->once()
            ->with('POST', 'https://textfl8.local/api/v1/req/1/contacts/1', Mockery::any())
            ->andReturn($mockResponse);

        // Act: Instantiate the Client and call updateContact
        $client = new TextflyClient('https://textfl8.local', 'your_api_key');
        $client->setClient($httpClientMock); // Inject mock
        $result = $client->updateContact(1, 1, [
            'optin' => 0,
            'accept_tos' => 0,
        ]);

        // Assert: Check if the result matches the expected response
        $this->assertEquals($mockResponseData, $result);
    }

    public function _testDeleteContactNotFound1()
    {
        // Arrange: Set up a 404 response and a mock HTTP client
        $mockResponse = new Response(404, [], json_encode(['error' => 'Contact not found.']));

        $httpClientMock = \Mockery::mock(HttpClient::class);
        $httpClientMock->shouldReceive('request')
            ->once()
            ->with('DELETE', 'https://textfl8.local/api/v1/req/1/contacts/999', Mockery::any())
            ->andReturn($mockResponse);

        // Act: Instantiate the Client, set the mock client, and call deleteContact
        $client = new TextflyClient('https://textfl8.local', 'your_api_key');
        $client->setClient($httpClientMock); // Use the new setClient method to inject mock

        // Assert: Expect an ApiException due to 404 Not Found
        $this->expectException(\Ryantxr\Textfly\Sdk\Exceptions\ApiException::class);
        $this->expectExceptionMessage('Contact not found.');

        // Try to delete a non-existent contact
        $client->deleteContact(1, 999);
    }

    public function testDeleteContactNotFound()
    {
        $this->expectException(\Ryantxr\Textfly\Sdk\Exceptions\ApiException::class);
        $this->expectExceptionMessage('Contact not found.');

        $this->performDeleteContactNotFound();
    }

    private function performDeleteContactNotFound()
    {
        // Arrange: Set up a 404 ClientException to be thrown by the mock HTTP client
        $mockResponse = new \GuzzleHttp\Psr7\Response(404, [], json_encode(['error' => 'Contact not found.']));
        $mockRequest = new \GuzzleHttp\Psr7\Request('DELETE', 'https://textfl8.local/api/v1/req/1/contacts/999');
        $clientException = new \GuzzleHttp\Exception\ClientException('Contact not found.', $mockRequest, $mockResponse);
        
        $httpClientMock = \Mockery::mock(HttpClient::class);
        $httpClientMock->shouldReceive('request')
            ->once()
            ->with('DELETE', 'https://textfl8.local/api/v1/req/1/contacts/999', \Mockery::any())
            ->andThrow($clientException); // Throw ClientException instead of returning a response
    
        // Act: Instantiate the Client, set the mock client, and call deleteContact
        $client = new TextflyClient('https://textfl8.local', 'your_api_key');
        $client->setClient($httpClientMock); // Use the new setClient method to inject mock
    
        // Assert: Expect an ApiException due to 404 Not Found
        // $this->expectException(\Ryantxr\Textfly\Sdk\Exceptions\ApiException::class);
        // $this->expectExceptionMessage('Contact not found.');
    
        // Try to delete a non-existent contact, which should throw an ApiException
        /*
        $r = $client->deleteContact(1, 999);
        print_r($r);
        */
        $client->deleteContact(1, 999);
    }    
}
