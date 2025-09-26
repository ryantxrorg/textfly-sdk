# ⚠️ **Warning: SDK is in Alpha** ⚠️

> **This SDK is currently in a alpha phase and is not even close to production-ready.**
> **Don't use this library at least until it gets to beta. Breaking changes and incomplete functionality may be present.**
> Contributions and feedback are welcome as we work toward a stable release.

# Textfly SDK

The TextFly SDK wraps the `/api/v1/req/{accountId}` endpoints documented in `docs/api.md` and exposes typed helpers for contacts, contact lists, list memberships, and scheduled SMS campaigns.

    composer require ryantxr/textfly-sdk

## Requirements

- PHP 7.4 or newer
- Guzzle 7 (installed automatically via Composer)

## Quick Start

```php
use TextFly\Sdk\Client;

$client = new Client('https://api.textfly.local', 'your_api_key');
```

The base URL should point at the host that serves `/api/v1`. The SDK automatically adds the `Authorization: Bearer` and `Accept: application/json` headers specified by the API reference.

## Services

- Contacts: list, fetch, find by phone, upsert, patch update, delete.
- Contact lists: list, fetch, create, update, delete using the hyphenated routes (`contact-lists`).
- Contact list memberships: list members, attach, and detach contacts via `contact-list-join`.
- Scheduled messages: list, create, update, delete, and trigger the `/send` action.

Each service is available through a dedicated accessor on the client (e.g. `$client->contacts()`), while backward-compatible shortcuts such as `$client->getContacts()` remain available.

## Usage Examples

### Contacts

```php
$contacts = $client->contacts()->list($accountId, 1, 25);
$contact = $client->contacts()->get($accountId, $contactId);
$byPhone = $client->contacts()->findByPhone($accountId, '2125556789');

$contact = $client->contacts()->upsert($accountId, [
    'phone' => '2125556789',
    'first_name' => 'Alice',
    'last_name' => 'Johnson',
]);

$updated = $client->contacts()->update($accountId, $contact['id'], [
    'optin' => true,
]);

$client->contacts()->delete($accountId, $contact['id']);
```

### Contact Lists

```php
$lists = $client->contactLists()->list($accountId);
$list = $client->contactLists()->create($accountId, ['name' => 'VIP Customers']);

$client->contactLists()->update($accountId, $list['id'], ['name' => 'VIPs']);
$client->contactLists()->delete($accountId, $list['id']);
```

### Contact List Memberships

```php
$members = $client->contactListMemberships()->list($accountId, $listId);

$client->contactListMemberships()->attach($accountId, $contactId, $listId);
$client->contactListMemberships()->detach($accountId, $listId, $contactId);
```

### Scheduled Messages

```php
$message = $client->scheduledMessages()->create($accountId, [
    'title' => 'Weekly Update',
    'body' => 'See you on Friday!',
    'contact_list_id' => $listId,
    'scheduled_at' => '2024-10-18T15:00:00Z',
    'is_scheduled' => true,
]);

$client->scheduledMessages()->update($accountId, $message['id'], [
    'title' => 'Updated Weekly Update',
]);

$client->scheduledMessages()->send($accountId, $message['id']); // returns null on HTTP 202
$client->scheduledMessages()->delete($accountId, $message['id']);
```

## Error Handling

All helpers throw `TextFly\Sdk\Exceptions\ApiException` when the API responds with an error or invalid JSON. The exception code carries the HTTP status when available.

```php
use TextFly\Sdk\Exceptions\ApiException;

try {
    $client->contacts()->upsert($accountId, ['phone' => 'invalid']);
} catch (ApiException $e) {
    // Log $e->getMessage() and $e->getCode()
}
```

## Testing

Install dependencies with `composer install` and run the PHPUnit suite:

    ./vendor/bin/phpunit tests/Unit

Unit tests rely on Guzzle's `MockHandler` and cover request construction for the documented endpoints. Optional feature tests live under `tests/Feature`; copy `sample.client-config.json` to `client-config.json` and populate it with valid credentials before running them against a live environment.

    ./vendor/bin/phpunit tests/Feature --filter TextFlyClientTest

## Contributing

Feel free to submit issues, feature requests, or pull requests to improve the SDK.

1. Fork the repository
2. Create your feature branch (git checkout -b feature/YourFeature)
3. Commit your changes (git commit -am 'Add YourFeature')
4. Push to the branch (git push origin feature/YourFeature)
5. Open a Pull Request

## License

This SDK is open-sourced software licensed under the MIT license.
