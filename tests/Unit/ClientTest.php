<?php

namespace TextFly\Sdk\Tests\Unit;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use TextFly\Sdk\Client;
use TextFly\Sdk\Exceptions\ApiException;

class ClientTest extends TestCase
{
    private const BASE_URL = 'https://api.example.test';

    private function createClient(array $responses, array &$history): Client
    {
        $history = [];

        $mock = new MockHandler($responses);
        $historyMiddleware = Middleware::history($history);

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($historyMiddleware);

        $httpClient = new HttpClient(['handler' => $handlerStack]);

        return new Client(self::BASE_URL, 'api-key', $httpClient);
    }

    public function testContactsListSendsPaginationQuery(): void
    {
        $expected = ['data' => [['id' => 1]]];
        $history = [];
        $client = $this->createClient([
            new Response(200, [], json_encode($expected)),
        ], $history);

        $result = $client->getContacts(42, 2, 50);

        $this->assertSame($expected, $result);
        $this->assertCount(1, $history);

        /** @var Request $request */
        $request = $history[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/v1/req/42/contacts', $request->getUri()->getPath());
        parse_str($request->getUri()->getQuery(), $queryParams);
        $this->assertSame(['page' => '2', 'per_page' => '50'], $queryParams);
    }

    public function testContactListCreateUsesHyphenatedEndpoint(): void
    {
        $expected = ['id' => 10, 'name' => 'Welcome'];
        $history = [];
        $client = $this->createClient([
            new Response(201, [], json_encode($expected)),
        ], $history);

        $result = $client->createContactList(7, ['name' => 'Welcome']);

        $this->assertSame($expected, $result);
        $this->assertCount(1, $history);

        /** @var Request $request */
        $request = $history[0]['request'];
        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame('/api/v1/req/7/contact-lists', $request->getUri()->getPath());
        $this->assertSame('application/json', $request->getHeaderLine('Content-Type'));

        $body = (string) $request->getBody();
        $this->assertSame(['name' => 'Welcome'], json_decode($body, true));
    }

    public function testAttachContactToListSendsDocumentedPayload(): void
    {
        $history = [];
        $client = $this->createClient([
            new Response(201, [], json_encode(['message' => 'Contact added to contact list'])),
        ], $history);

        $result = $client->attachContactToList(3, 9, 21);

        $this->assertSame(['message' => 'Contact added to contact list'], $result);
        $this->assertCount(1, $history);

        /** @var Request $request */
        $request = $history[0]['request'];
        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame('/api/v1/req/3/contact-list-join', $request->getUri()->getPath());

        $payload = json_decode((string) $request->getBody(), true);
        $this->assertSame([
            'contact_id' => 9,
            'contact_list_id' => 21,
        ], $payload);
    }

    public function testSendScheduledMessageHandlesEmptyResponse(): void
    {
        $history = [];
        $client = $this->createClient([
            new Response(202, []),
        ], $history);

        $result = $client->sendScheduledMessage(11, 5);

        $this->assertNull($result);
        $this->assertCount(1, $history);

        /** @var Request $request */
        $request = $history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/v1/req/11/scheduled-messages/5/send', $request->getUri()->getPath());
    }

    public function testThrowsApiExceptionWithValidationMessage(): void
    {
        $request = new Request('PUT', self::BASE_URL . '/api/v1/req/2/contacts');
        $response = new Response(422, [], json_encode(['message' => 'The given data was invalid.']));

        $mock = new MockHandler([
            static function () use ($request, $response) {
                throw new RequestException('Unprocessable', $request, $response);
            },
        ]);

        $handlerStack = HandlerStack::create($mock);
        $httpClient = new HttpClient(['handler' => $handlerStack]);

        $client = new Client(self::BASE_URL, 'api-key', $httpClient);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('The given data was invalid.');
        $this->expectExceptionCode(422);

        $client->createContact(2, ['phone' => 'abc']);
    }
}
