<?php

namespace TextFly\Sdk\Api;

class ContactListsClient extends AbstractApi
{
    /**
     * Retrieve contact lists for an account.
     */
    public function list(int $accountId, ?int $page = null, ?int $perPage = null): array
    {
        $query = array_filter([
            'page' => $page,
            'per_page' => $perPage,
        ], static fn (mixed $value) => $value !== null);

        $options = [];
        if (!empty($query)) {
            $options['query'] = $query;
        }

        return $this->request('GET', $this->accountPath($accountId, 'contact-lists'), $options) ?? [];
    }

    /**
     * Fetch a contact list by identifier.
     */
    public function get(int $accountId, int $contactListId): array
    {
        return $this->request('GET', $this->accountPath($accountId, "contact-lists/{$contactListId}")) ?? [];
    }

    /**
     * Create a contact list.
     *
     * @param array $payload Must contain the `name` property.
     */
    public function create(int $accountId, array $payload): array
    {
        return $this->request('PUT', $this->accountPath($accountId, 'contact-lists'), [
            'json' => $payload,
        ]) ?? [];
    }

    /**
     * Update a contact list name.
     */
    public function update(int $accountId, int $contactListId, array $payload): array
    {
        return $this->request('POST', $this->accountPath($accountId, "contact-lists/{$contactListId}"), [
            'json' => $payload,
        ]) ?? [];
    }

    /**
     * Delete a contact list (HTTP 204 expected).
     */
    public function delete(int $accountId, int $contactListId): void
    {
        $this->request('DELETE', $this->accountPath($accountId, "contact-lists/{$contactListId}"));
    }
}
