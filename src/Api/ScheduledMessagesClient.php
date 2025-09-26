<?php

namespace TextFly\Sdk\Api;

class ScheduledMessagesClient extends AbstractApi
{
    /**
     * Retrieve scheduled messages for the account.
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

        return $this->request('GET', $this->accountPath($accountId, 'scheduled-messages'), $options) ?? [];
    }

    /**
     * Create a scheduled message using the documented payload.
     */
    public function create(int $accountId, array $payload): array
    {
        return $this->request('POST', $this->accountPath($accountId, 'scheduled-messages'), [
            'json' => $payload,
        ]) ?? [];
    }

    /**
     * Update a scheduled message.
     */
    public function update(int $accountId, int $scheduledMessageId, array $payload): array
    {
        return $this->request('PUT', $this->accountPath($accountId, "scheduled-messages/{$scheduledMessageId}"), [
            'json' => $payload,
        ]) ?? [];
    }

    /**
     * Delete a scheduled message (HTTP 204).
     */
    public function delete(int $accountId, int $scheduledMessageId): void
    {
        $this->request('DELETE', $this->accountPath($accountId, "scheduled-messages/{$scheduledMessageId}"));
    }

    /**
     * Trigger immediate send for a scheduled message.
     */
    public function send(int $accountId, int $scheduledMessageId): ?array
    {
        return $this->request('POST', $this->accountPath($accountId, "scheduled-messages/{$scheduledMessageId}/send"));
    }
}
