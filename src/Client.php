<?php

namespace TextFly\Sdk;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use TextFly\Sdk\Api\ContactListMembershipsClient;
use TextFly\Sdk\Api\ContactListsClient;
use TextFly\Sdk\Api\ContactsClient;
use TextFly\Sdk\Api\ScheduledMessagesClient;
use TextFly\Sdk\Exceptions\ApiException;

class Client
{
    private ClientInterface $httpClient;

    private string $baseUrl;

    private string $apiKey;

    private ContactsClient $contacts;

    private ContactListsClient $contactLists;

    private ContactListMembershipsClient $contactListMemberships;

    private ScheduledMessagesClient $scheduledMessages;

    public function __construct(string $baseUrl, string $apiKey, ?ClientInterface $httpClient = null)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->httpClient = $httpClient ?? new HttpClient();

        $this->initialiseApis();
    }

    public function contacts(): ContactsClient
    {
        return $this->contacts;
    }

    public function contactLists(): ContactListsClient
    {
        return $this->contactLists;
    }

    public function contactListMemberships(): ContactListMembershipsClient
    {
        return $this->contactListMemberships;
    }

    public function scheduledMessages(): ScheduledMessagesClient
    {
        return $this->scheduledMessages;
    }

    public function setHttpClient(ClientInterface $client): self
    {
        $this->httpClient = $client;
        $this->initialiseApis();

        return $this;
    }

    /**
     * @deprecated Use setHttpClient() instead.
     */
    public function setClient(ClientInterface $client): self
    {
        return $this->setHttpClient($client);
    }

    /**
     * @return array|null
     */
    public function request(string $method, string $uri, array $options = []): ?array
    {
        $options['headers'] = array_merge([
            'Authorization' => sprintf('Bearer %s', $this->apiKey),
            'Accept' => 'application/json',
        ], $options['headers'] ?? []);

        try {
            $response = $this->httpClient->request($method, $this->baseUrl . $uri, $options);

            $body = (string) $response->getBody();
            if ($body === '') {
                return null;
            }

            $decoded = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ApiException('Invalid JSON response from API.', $response->getStatusCode());
            }

            return $decoded;
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
            $statusCode = $response ? $response->getStatusCode() : 0;
            $message = $this->resolveErrorMessage($exception);

            throw new ApiException($message, $statusCode, $exception);
        } catch (\Throwable $exception) {
            throw new ApiException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function getContacts(int $accountId, int $page = 1, int $perPage = 10): array
    {
        return $this->contacts()->list($accountId, $page, $perPage);
    }

    public function getContact(int $accountId, int $contactId): array
    {
        return $this->contacts()->get($accountId, $contactId);
    }

    public function findContactByPhone(int $accountId, string $phone): array
    {
        return $this->contacts()->findByPhone($accountId, $phone);
    }

    public function createContact(int $accountId, array $payload): array
    {
        return $this->contacts()->upsert($accountId, $payload);
    }

    public function updateContact(int $accountId, int $contactId, array $payload): array
    {
        return $this->contacts()->update($accountId, $contactId, $payload);
    }

    public function deleteContact(int $accountId, int $contactId): array
    {
        return $this->contacts()->delete($accountId, $contactId);
    }

    public function getContactLists(int $accountId, int $page = 1, int $perPage = 10): array
    {
        return $this->contactLists()->list($accountId, $page, $perPage);
    }

    public function getContactList(int $accountId, int $contactListId): array
    {
        return $this->contactLists()->get($accountId, $contactListId);
    }

    public function createContactList(int $accountId, array $payload): array
    {
        return $this->contactLists()->create($accountId, $payload);
    }

    public function updateContactList(int $accountId, int $contactListId, array $payload): array
    {
        return $this->contactLists()->update($accountId, $contactListId, $payload);
    }

    public function deleteContactList(int $accountId, int $contactListId): void
    {
        $this->contactLists()->delete($accountId, $contactListId);
    }

    public function listContactListMembers(int $accountId, int $contactListId, int $page = 1, int $perPage = 10): array
    {
        return $this->contactListMemberships()->list($accountId, $contactListId, $page, $perPage);
    }

    public function attachContactToList(int $accountId, int $contactId, int $contactListId): array
    {
        return $this->contactListMemberships()->attach($accountId, $contactId, $contactListId);
    }

    public function detachContactFromList(int $accountId, int $contactListId, int $contactId): void
    {
        $this->contactListMemberships()->detach($accountId, $contactListId, $contactId);
    }

    public function listScheduledMessages(int $accountId, int $page = 1, int $perPage = 10): array
    {
        return $this->scheduledMessages()->list($accountId, $page, $perPage);
    }

    public function createScheduledMessage(int $accountId, array $payload): array
    {
        return $this->scheduledMessages()->create($accountId, $payload);
    }

    public function updateScheduledMessage(int $accountId, int $scheduledMessageId, array $payload): array
    {
        return $this->scheduledMessages()->update($accountId, $scheduledMessageId, $payload);
    }

    public function deleteScheduledMessage(int $accountId, int $scheduledMessageId): void
    {
        $this->scheduledMessages()->delete($accountId, $scheduledMessageId);
    }

    public function sendScheduledMessage(int $accountId, int $scheduledMessageId): ?array
    {
        return $this->scheduledMessages()->send($accountId, $scheduledMessageId);
    }

    private function initialiseApis(): void
    {
        $this->contacts = new ContactsClient($this);
        $this->contactLists = new ContactListsClient($this);
        $this->contactListMemberships = new ContactListMembershipsClient($this);
        $this->scheduledMessages = new ScheduledMessagesClient($this);
    }

    private function resolveErrorMessage(RequestException $exception): string
    {
        $response = $exception->getResponse();
        if ($response === null) {
            return $exception->getMessage();
        }

        $body = (string) $response->getBody();
        if ($body === '') {
            return $exception->getMessage();
        }

        $decoded = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            if (isset($decoded['error']) && is_string($decoded['error'])) {
                return $decoded['error'];
            }

            if (isset($decoded['message']) && is_string($decoded['message'])) {
                return $decoded['message'];
            }
        }

        return $body;
    }
}
