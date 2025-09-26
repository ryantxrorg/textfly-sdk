<?php

namespace TextFly\Sdk\Api;

class ContactListMembershipsClient extends AbstractApi
{
    /**
     * List contacts attached to a contact list.
     */
    public function list(int $accountId, int $contactListId, ?int $page = null, ?int $perPage = null): array
    {
        $query = array_filter([
            'page' => $page,
            'per_page' => $perPage,
        ], static fn ($value) => $value !== null);

        $options = [];
        if (!empty($query)) {
            $options['query'] = $query;
        }

        $path = $this->accountPath($accountId, "contact-list-join/{$contactListId}");

        return $this->request('GET', $path, $options) ?? [];
    }

    /**
     * Attach a contact to a contact list.
     */
    public function attach(int $accountId, int $contactId, int $contactListId): array
    {
        $payload = [
            'contact_id' => $contactId,
            'contact_list_id' => $contactListId,
        ];

        return $this->request('PUT', $this->accountPath($accountId, 'contact-list-join'), [
            'json' => $payload,
        ]) ?? [];
    }

    /**
     * Detach a contact from a contact list.
     */
    public function detach(int $accountId, int $contactListId, int $contactId): void
    {
        $this->request('DELETE', $this->accountPath($accountId, "contact-list-join/{$contactListId}/{$contactId}"));
    }
}
