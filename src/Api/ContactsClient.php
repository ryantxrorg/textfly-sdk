<?php

namespace TextFly\Sdk\Api;

class ContactsClient extends AbstractApi
{
    /**
     * Retrieve paginated contacts for the given account.
     *
     * @param int      $accountId Account identifier.
     * @param int|null $page      Optional page number.
     * @param int|null $perPage   Optional page size (max 100).
     */
    public function list(int $accountId, ?int $page = null, ?int $perPage = null): array
    {
        $query = array_filter([
            'page' => $page,
            'per_page' => $perPage,
        ], static fn ($value) => $value !== null);

        $options = [];
        if (!empty($query)) {
            $options['query'] = $query;
        }

        return $this->request('GET', $this->accountPath($accountId, 'contacts'), $options) ?? [];
    }

    /**
     * Fetch a single contact.
     */
    public function get(int $accountId, int $contactId): array
    {
        return $this->request('GET', $this->accountPath($accountId, "contacts/{$contactId}")) ?? [];
    }

    /**
     * Locate a contact by phone number.
     */
    public function findByPhone(int $accountId, string $phone): array
    {
        return $this->request('GET', $this->accountPath($accountId, "contacts/phone/{$phone}")) ?? [];
    }

    /**
     * Create or update a contact using the documented payload.
     *
     * @param array $payload Contact attributes such as phone, first_name, last_name.
     */
    public function upsert(int $accountId, array $payload): array
    {
        return $this->request('PUT', $this->accountPath($accountId, 'contacts'), [
            'json' => $payload,
        ]) ?? [];
    }

    /**
     * Partially update a contact.
     *
     * @param array $payload Any subset of contact fields.
     */
    public function update(int $accountId, int $contactId, array $payload): array
    {
        return $this->request('POST', $this->accountPath($accountId, "contacts/{$contactId}"), [
            'json' => $payload,
        ]) ?? [];
    }

    /**
     * Soft-delete a contact and return the API message body.
     */
    public function delete(int $accountId, int $contactId): array
    {
        return $this->request('DELETE', $this->accountPath($accountId, "contacts/{$contactId}")) ?? [];
    }
}
