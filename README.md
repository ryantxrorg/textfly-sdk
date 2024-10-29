# Textfly SDK

The TextFly SDK provides a simple interface for interacting with the TextFly API, enabling operations like creating, updating, retrieving, and deleting contacts. This SDK is intended for seamless integration into multi-tenant applications where each account has unique access to contact data.

    composer require ryantxr/textfly-sdk

## Configuration

To use the SDK, you’ll need:

* The base URL of the TextFly API.
* An API key for authenticating requests.

    use Ryantxr\Textfly\Sdk\Client;

    $client = new Client('https://api_endpoint', 'your_api_key');

## Usage

Initialize the Client

Create a new instance of the SDK client by providing the base URL and your API key.

    $client = new Client('https://api_endpoint', 'your_api_key');

## List Contacts

Retrieve a paginated list of contacts for a specific account.

    $accountId = 1;
    $contacts = $client->getContacts($accountId, $page = 1, $perPage = 10);

    print_r($contacts);

## Retrieve a Contact

Get details of a specific contact by its ID.

    $contactId = 123;
    $contact = $client->getContact($accountId, $contactId);

    print_r($contact);

## Create a Contact

Add a new contact to the account.

    $data = [
        'phone' => '212-555-6789',
        'first_name' => 'Alice',
        'last_name' => 'Johnson',
        'optin' => 1,
        'accept_tos' => 1,
    ];

    $contact = $client->createContact($accountId, $data);

    print_r($contact);

## Update a Contact

Update an existing contact’s information.

    $contactId = 123;
    $data = [
        'first_name' => 'Alicia',
        'optin' => 0,
    ];

    $updatedContact = $client->updateContact($accountId, $contactId, $data);

    print_r($updatedContact);

## Delete a Contact

Soft delete a contact, which allows it to be restored in the future if needed.

    $client->deleteContact($accountId, $contactId);
    echo "Contact deleted successfully.";

## Testing

The SDK includes tests to verify each method’s functionality.

There is a small setup required to run some tests.

    cd tests/Feature
    cp sample.client-config.json client-config.json

Edit client-config.json and add a testing api key and a testing url.
These are not unit tests. They actually connect to a backend and send real api calls.

To run all the tests, use:

    vendor/bin/phpunit tests

To run the unit tests only, use:

    vendor/bin/phpunit tests/Unit

## Contributing

Feel free to submit issues, feature requests, or pull requests to improve the SDK.

1. Fork the repository
2. Create your feature branch (git checkout -b feature/YourFeature)
3. Commit your changes (git commit -am 'Add YourFeature')
4. Push to the branch (git push origin feature/YourFeature)
5. Open a Pull Request

## License

This SDK is open-sourced software licensed under the MIT license.
