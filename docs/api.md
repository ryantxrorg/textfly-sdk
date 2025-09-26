# TextFly API Reference (v1)

The SDK targets the `/api/v1/req/{accountId}` endpoints secured by `auth:sanctum`. All responses are JSON.

## Authentication

- Send the `Authorization: Bearer {token}` header (Laravel Sanctum personal access token).
- Include `Accept: application/json`.

## Contacts

### List Contacts

```
GET /api/v1/req/{accountId}/contacts
```

Query parameters:

| Name | Type | Description |
| ---- | ---- | ----------- |
| `per_page` | int (optional, default 10, max 100) | Page size |
| `page` | int (optional) | Page number |

Response: Laravel paginator JSON with `data` array containing objects with `phone`, `first_name`, `last_name`.

### Show Contact

```
GET /api/v1/req/{accountId}/contacts/{id}
```

Response: contact fields `id`, `phone`, `first_name`, `last_name`, `active`, `user1`, `optin`, `optin_at`, `accept_tos`, `accept_tos_at`.

### Find By Phone

```
GET /api/v1/req/{accountId}/contacts/phone/{phone}
```

- `phone` may be raw digits; server normalises to E.164.
- Returns 422 for invalid phone format.

### Create or Update Contact (idempotent)

```
PUT /api/v1/req/{accountId}/contacts
Content-Type: application/json
```

Payload:

| Field | Type | Required |
| ----- | ---- | -------- |
| `phone` | string | yes (validated and coerced to E.164) |
| `first_name` | string | yes |
| `last_name` | string | yes |
| `active` | bool | no |
| `user1` | string (max 20) | no |
| `optin` | bool | no (updates `optin_at`) |
| `accept_tos` | bool | no (updates `accept_tos_at`) |

Response `201 Created` with the contact body described above.

### Update Contact (non-idempotent)

```
POST /api/v1/req/{accountId}/contacts/{id}
Content-Type: application/json
```

Payload: any subset of fields from create endpoint (all optional). Validation matches create, with partial updates allowed. Returns 404 if contact not in account.

Response: updated contact JSON.

### Delete Contact

```
DELETE /api/v1/req/{accountId}/contacts/{id}
```

Soft deletes the contact. Returns `{ "message": "Contact deleted successfully." }` with HTTP 200.

## Contact Lists

### List Contact Lists

```
GET /api/v1/req/{accountId}/contact-lists
```

Query parameters: `per_page` (default 10, max 100), `page`.

Response: paginator JSON with `data` entries containing `id`, `name`.

### Show Contact List

```
GET /api/v1/req/{accountId}/contact-lists/{id}
```

Response: `{ "id": ..., "name": ... }`. 404 if not found within account.

### Create Contact List

```
PUT /api/v1/req/{accountId}/contact-lists
Content-Type: application/json
```

Payload: `{ "name": string }` (required).

Response: `201 Created` with `{ "id": ..., "name": ... }`.

### Update Contact List

```
POST /api/v1/req/{accountId}/contact-lists/{id}
Content-Type: application/json
```

Payload: `{ "name": string }` (optional, max 255). Returns updated list JSON.

### Delete Contact List

```
DELETE /api/v1/req/{accountId}/contact-lists/{id}
```

Response: HTTP `204 No Content`.

## Contact List Memberships

### List Members

```
GET /api/v1/req/{accountId}/contact-list-join/{contactListId}
```

Query: `per_page` (default 10, max 100), `page`.

Response: paginator JSON of contacts in the list. Each entry includes the same contact fields as in `contacts.show` (except `account_id`).

### Attach Contact

```
PUT /api/v1/req/{accountId}/contact-list-join
Content-Type: application/json
```

Payload: `{ "contact_id": int, "contact_list_id": int }`.

- Both IDs must belong to the authenticated account.
- `syncWithoutDetaching` prevents duplicate rows.

Response: `201 Created` with `{ "message": "Contact added to contact list" }`.

### Detach Contact

```
DELETE /api/v1/req/{accountId}/contact-list-join/{contactListId}/{contactId}
```

Response: HTTP `204 No Content`.

## Scheduled Messages

### List Scheduled Messages

```
GET /api/v1/req/{accountId}/scheduled-messages
```

Query parameters: `per_page` (default 10, max 100), `page`.

Response: paginator JSON of scheduled messages containing fields such as `id`, `title`, `body`, `contact_list_id`, `scheduled_at`, `scheduled_message_status_id`, timestamps, and metadata.

### Create Scheduled Message

```
POST /api/v1/req/{accountId}/scheduled-messages
Content-Type: application/json
```

Payload:

| Field | Type | Required |
| ----- | ---- | -------- |
| `title` | string (max 50) | yes |
| `body` | string (max 2500) | yes |
| `contact_list_id` | integer | yes |
| `scheduled_at` | ISO 8601 datetime | yes |
| `is_scheduled` | boolean | yes |
| `optout_text` | string (max 255) | no |

Response: `201 Created` with the scheduled message JSON payload.

### Update Scheduled Message

```
PUT /api/v1/req/{accountId}/scheduled-messages/{id}
Content-Type: application/json
```

Payload: any subset of the create fields (all optional). Returns the updated scheduled message JSON.

### Delete Scheduled Message

```
DELETE /api/v1/req/{accountId}/scheduled-messages/{id}
```

Response: HTTP `204 No Content`.

### Send Scheduled Message

```
POST /api/v1/req/{accountId}/scheduled-messages/{id}/send
```

Queues the scheduled message for sending. Response: HTTP `202 Accepted`.

## Error Codes

- `401 Unauthorized`: missing or invalid token.
- `403 Forbidden`: account middleware denies access.
- `404 Not Found`: resource not in account scope.
- `422 Unprocessable Entity`: validation failure (invalid phone, missing name, etc.).
- `500 Internal Server Error`: unexpected errors; inspect response body for details.

All error responses return JSON `{"error": "..."}` when issued by the API controllers.

## Pagination Format

List endpoints leverage Laravel's paginator. Typical structure:

```json
{
  "data": [],
  "current_page": 1,
  "per_page": 10,
  "from": 1,
  "to": 10,
  "total": 123,
  "last_page": 13,
  "next_page_url": "...",
  "prev_page_url": null,
  "first_page_url": "...",
  "last_page_url": "...",
  "links": [ ... ]
}
```

Use `per_page` to control payload size; the server caps it at 100.
