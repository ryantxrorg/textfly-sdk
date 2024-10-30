<?php

use PHPUnit\Framework\TestCase;
use Ryantxr\Textfly\Sdk\Client as TextflyClient;

/*
This is to test the actual backend.
*/
class TextFlyClientTest extends TestCase
{
    protected $config; //'set me in client-config.json';
    public function testGetContacts()
    {
        $client = new TextflyClient($this->config->url, $this->config->api_key);
        $contacts = $client->getContacts(1);
print_r($contacts);
        $this->assertIsArray($contacts);
        $this->assertArrayHasKey('data', $contacts);
    }

    public function testGetContact()
    {
        $client = new TextflyClient($this->config->url, $this->config->api_key);
        $contact = $client->getContact(1, 1);
        $this->assertIsArray($contact);
        $this->assertArrayHasKey('phone', $contact);
    }

    /**
     * phpunit tests/Feature/TextFlyClientTest.php --filter=testFindContact
     */
    public function testFindContact()
    {
        $client = new TextflyClient($this->config->url, $this->config->api_key);
        $phone = '7282441010';
        $contact = $client->findContactByPhone(1, $phone);
        $this->assertIsArray($contact);
        $this->assertArrayHasKey('phone', $contact);
    }

    public function testCreateContact()
    {
        $accountId = 1;
        $data = [
            'phone' => '7282441010',
            'first_name' => 'Scotty',
            'last_name' => 'Clarke',
        ];
        $client = new TextflyClient($this->config->url, $this->config->api_key);
        $contact = $client->createContact($accountId, $data);
        // print_r($contact);
        $this->assertIsArray($contact);
        $this->assertEquals('Clarke', $contact['last_name']);
        $this->assertArrayHasKey('phone', $contact);
        $contactId = $contact['id'];

        $data['optin'] = 1;
        $contact = $client->updateContact($accountId, $contactId, $data);
        // createContact($accountId, $data);
        // print_r($contact);
        $this->assertIsArray($contact);
        $this->assertEquals('Clarke', $contact['last_name']);
        $this->assertEquals('1', $contact['optin']);
        $this->assertNotNull($contact['optin_at']);
        $this->assertEquals(0, $contact['accept_tos']);
        $this->assertNull($contact['accept_tos_at']);
        $this->assertArrayHasKey('phone', $contact);
    }

    public function testDeleteContact()
    {
        $accountId = 1;
        $data = [
            'phone' => '7282441010',
            'first_name' => 'Scotty',
            'last_name' => 'Clarke',
        ];
        $client = new TextflyClient($this->config->url, $this->config->api_key);
        $contact = $client->createContact($accountId, $data);
        // print_r($contact);
        $this->assertIsArray($contact);
        $this->assertEquals('Clarke', $contact['last_name']);
        $this->assertArrayHasKey('phone', $contact);
        $contactId = $contact['id'];
        
        $contact = $client->getContact($accountId, $contactId);
        $this->assertIsArray($contact);
        $this->assertArrayHasKey('phone', $contact);
        
        $client->deleteContact($accountId, $contactId);
        
        try {
            $client->getContact($accountId, $contactId);
            $this->fail('Expected ApiException was not thrown'); // Fail if no exception is thrown
        } catch (Ryantxr\Textfly\Sdk\Exceptions\ApiException $e) {
            // throws a 404 exception, handle it, expect it, test it.
            $this->assertEquals(404, $e->getCode()); // Assert that the exception code is 404
            $this->assertStringContainsString('Contact not found', $e->getMessage()); // Verify the error message
        }
    }

    public function setup(): void
    {
        $configFile = __DIR__ . '/' . 'client-config.json';
        if ( ! file_exists($configFile) ) {
            die("Config file {$configFile} not found");
        }
        $this->config = json_decode(file_get_contents($configFile));
        if ( !$this->config ) die("JSON error");
        if ( !isset($this->config->api_key) ) die("api_key missing from config file.");
        if ( !isset($this->config->url) ) die("url missing from config file.");
    }
}
