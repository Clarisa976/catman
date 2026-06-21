# CatManReaders API

Base URL local:

```text
http://localhost:8000/api
```

Responses use JSON. Most resource endpoints return a Laravel API Resource shape:

```json
{
  "data": {},
  "links": {},
  "meta": {}
}
```

Validation errors use Laravel's standard shape:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field": ["Error message."]
  }
}
```

## Authentication

`POST /api/register` and `POST /api/login` are public. All other endpoints require Sanctum bearer auth:

```http
Authorization: Bearer TOKEN
Accept: application/json
```

### Register

`POST /api/register`

Auth: public

Body:

```json
{
  "name": "Usagi",
  "email": "usagi@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

Response:

```json
{
  "user": {
    "id": 1,
    "name": "Usagi",
    "email": "usagi@example.com"
  },
  "token": "plain-text-token"
}
```

### Login

`POST /api/login`

Auth: public

Body:

```json
{
  "email": "usagi@example.com",
  "password": "password123"
}
```

Response:

```json
{
  "user": {
    "id": 1,
    "email": "usagi@example.com"
  },
  "token": "plain-text-token"
}
```

### Current User

`GET /api/me`

Auth: required

Response:

```json
{
  "id": 1,
  "name": "Usagi",
  "email": "usagi@example.com"
}
```

### Logout

`POST /api/logout`

Auth: required

Response:

```json
{
  "message": "Logged out."
}
```

## Pagination

Main list endpoints accept:

| Query param | Notes |
| --- | --- |
| `page` | Page number. |
| `per_page` | Items per page. Maximum: `100`. Default: `15`. |

Paginated responses include `data`, `links`, and `meta`.

## Works

Works are common catalog records, not user-private records.

### List Works

`GET /api/works`

Auth: required

Query params:

| Param | Description |
| --- | --- |
| `search` | Searches `title`, `original_title`, and `author`. |
| `query` | Backward-compatible alias for `search`. |
| `type` | `manga`, `manhwa`, `manhua`, `novel`, `other`. |
| `status` | `ongoing`, `completed`, `hiatus`, `unknown`. |
| `author` | Partial author match. |
| `page` | Page number. |
| `per_page` | Max `100`. |

Response:

```json
{
  "data": [
    {
      "id": 1,
      "title": "Haikyuu!!",
      "original_title": null,
      "author": "Haruichi Furudate",
      "type": "manga",
      "status": "completed",
      "total_volumes": null,
      "cover_url": null,
      "notes": null
    }
  ],
  "links": {},
  "meta": {}
}
```

### Create Work

`POST /api/works`

Auth: required

Body:

```json
{
  "title": "Haikyuu!!",
  "original_title": "Haikyu!!",
  "author": "Haruichi Furudate",
  "type": "manga",
  "status": "completed",
  "total_volumes": 45,
  "cover_url": "https://example.test/cover.jpg",
  "notes": "Spanish physical collection target."
}
```

Response:

```json
{
  "data": {
    "id": 1,
    "title": "Haikyuu!!",
    "author": "Haruichi Furudate",
    "type": "manga",
    "status": "completed"
  }
}
```

### Show, Update, Delete Work

| Method | Route | Auth | Notes |
| --- | --- | --- | --- |
| `GET` | `/api/works/{work}` | Required | Includes physical volumes and digital series when loaded. |
| `PUT/PATCH` | `/api/works/{work}` | Required | Same body fields as create, all optional. |
| `DELETE` | `/api/works/{work}` | Required | Deletes the catalog work. Related physical volumes cascade. |

## Physical Volumes

Physical volumes are common catalog records. A user owns or tracks them through `/api/my/physical-collection`.

### List Volumes For A Work

`GET /api/works/{work}/physical-volumes`

Auth: required

Query params:

| Param | Description |
| --- | --- |
| `search` | Searches volume title, ISBN, and EAN. |
| `work_id` | Optional redundant filter. |
| `language` | Exact language match. |
| `country` | Exact country match. |
| `publisher` | Partial publisher match. |
| `release_from` | `YYYY-MM-DD`; release date greater than or equal. |
| `release_to` | `YYYY-MM-DD`; release date less than or equal. |
| `page` / `per_page` | Pagination. |

Response:

```json
{
  "data": [
    {
      "id": 10,
      "work_id": 1,
      "volume_number": "1.00",
      "title": "Volume 1",
      "language": "espanol",
      "country": "Espana",
      "publisher": "Planeta Comic",
      "isbn": "9781234567890",
      "ean": "9781234567890"
    }
  ],
  "links": {},
  "meta": {}
}
```

### Create Physical Volume

`POST /api/works/{work}/physical-volumes`

Auth: required

Body:

```json
{
  "volume_number": 1,
  "title": "Volume 1",
  "language": "espanol",
  "country": "Espana",
  "publisher": "Planeta Comic",
  "edition_name": "Standard",
  "isbn": "9781234567890",
  "ean": "9781234567890",
  "release_date": "2026-01-15",
  "price": 9.95,
  "currency": "EUR",
  "cover_url": "https://example.test/volume.jpg"
}
```

### Show, Update, Delete Physical Volume

| Method | Route | Auth |
| --- | --- | --- |
| `GET` | `/api/physical-volumes/{physicalVolume}` | Required |
| `PUT` | `/api/physical-volumes/{physicalVolume}` | Required |
| `DELETE` | `/api/physical-volumes/{physicalVolume}` | Required |

## My Physical Collection

Collection records are user-private. Users only see, update, and delete their own collection items.

### List My Collection

`GET /api/my/physical-collection`

Auth: required

Query params:

| Param | Description |
| --- | --- |
| `ownership_status` | `owned`, `wishlist`, `reserved`, `pending`, `not_interested`, `sold`, `lent`. |
| `reading_status` | `not_started`, `reading`, `read`, `paused`. |
| `is_travel_memory` | Boolean-like value such as `true`, `false`, `1`, `0`. |
| `language` | Filters through the linked physical volume. |
| `country` | Filters through the linked physical volume. |
| `search` | Searches volume title, ISBN, EAN, or work title. |
| `page` / `per_page` | Pagination. |

Response:

```json
{
  "data": [
    {
      "id": 20,
      "user_id": 1,
      "physical_volume_id": 10,
      "ownership_status": "owned",
      "reading_status": "read",
      "is_travel_memory": false,
      "physical_volume": {
        "id": 10,
        "title": "Volume 1",
        "work": {
          "id": 1,
          "title": "Haikyuu!!"
        }
      }
    }
  ],
  "links": {},
  "meta": {}
}
```

### Add Volume To My Collection

`POST /api/my/physical-collection`

Auth: required

Body:

```json
{
  "physical_volume_id": 10,
  "ownership_status": "owned",
  "reading_status": "not_started",
  "purchase_date": "2026-06-21",
  "purchase_price": 9.95,
  "purchase_currency": "EUR",
  "store": "Local shop",
  "is_travel_memory": false,
  "purchase_country": "Espana",
  "location": "Shelf A",
  "notes": "First edition."
}
```

Security note: route model updates and deletes return `404` for collection items owned by another user.

### Update, Delete My Collection Item

| Method | Route | Auth |
| --- | --- | --- |
| `PUT` | `/api/my/physical-collection/{item}` | Required |
| `DELETE` | `/api/my/physical-collection/{item}` | Required |

## Physical Work Tracking

Physical tracking records are user-private.

### List My Work Tracking

`GET /api/my/work-tracking`

Auth: required

Query params: `page`, `per_page`.

### Create Work Tracking

`POST /api/my/work-tracking`

Auth: required

Body:

```json
{
  "work_id": 1,
  "follow_physical_releases": true,
  "preferred_language": "espanol",
  "preferred_country": "Espana",
  "preferred_publisher": "Planeta Comic",
  "last_owned_volume_number": 4,
  "notify_new_volume": true,
  "notes": "Waiting for next Spanish volume."
}
```

Security note: users can only list, update, and delete their own tracking rows.

### Update, Delete Work Tracking

| Method | Route | Auth |
| --- | --- | --- |
| `PUT` | `/api/my/work-tracking/{tracking}` | Required |
| `DELETE` | `/api/my/work-tracking/{tracking}` | Required |

## Digital Platforms

Platforms are common catalog records.

| Method | Route | Auth | Notes |
| --- | --- | --- | --- |
| `GET` | `/api/digital-platforms` | Required | Lists all platforms ordered by name. |
| `POST` | `/api/digital-platforms` | Required | Creates a platform. |
| `GET` | `/api/digital-platforms/{digital_platform}` | Required | Shows a platform and loaded series. |
| `PUT/PATCH` | `/api/digital-platforms/{digital_platform}` | Required | Updates name or URL. |
| `DELETE` | `/api/digital-platforms/{digital_platform}` | Required | Deletes platform. |

Create body:

```json
{
  "name": "WEBTOON",
  "website_url": "https://www.webtoons.com"
}
```

## Digital Series

Digital series are common catalog records.

### List Digital Series

`GET /api/digital-series`

Auth: required

Query params:

| Param | Description |
| --- | --- |
| `search` | Partial title search. |
| `query` | Backward-compatible alias for `search`. |
| `page` / `per_page` | Pagination. |

### Create Digital Series

`POST /api/digital-series`

Auth: required

Body:

```json
{
  "work_id": 1,
  "platform_id": 1,
  "title": "Under the Oak Tree",
  "platform_url": "https://example.test/series",
  "language": "ingles",
  "status": "ongoing",
  "latest_episode_detected": 90,
  "last_checked_at": "2026-06-21",
  "cover_url": "https://example.test/digital.jpg",
  "notes": "Public metadata only."
}
```

### Show, Update, Delete Digital Series

| Method | Route | Auth |
| --- | --- | --- |
| `GET` | `/api/digital-series/{digital_series}` | Required |
| `PUT/PATCH` | `/api/digital-series/{digital_series}` | Required |
| `DELETE` | `/api/digital-series/{digital_series}` | Required |

## Digital Episodes

Episodes are common catalog records under a digital series.

| Method | Route | Auth | Notes |
| --- | --- | --- | --- |
| `GET` | `/api/digital-series/{digitalSeries}/episodes` | Required | Paginated by `page` and `per_page`. |
| `POST` | `/api/digital-series/{digitalSeries}/episodes` | Required | Creates episode. |
| `PUT` | `/api/digital-episodes/{digitalEpisode}` | Required | Updates episode. |
| `DELETE` | `/api/digital-episodes/{digitalEpisode}` | Required | Deletes episode. |

Create body:

```json
{
  "episode_number": 12,
  "title": "Episode 12",
  "release_date": "2026-06-21",
  "episode_url": "https://example.test/episode-12",
  "is_free": true,
  "is_locked": false
}
```

## My Digital Tracking

Digital tracking records are user-private.

### List My Digital Tracking

`GET /api/my/digital-tracking`

Auth: required

Query params:

| Param | Description |
| --- | --- |
| `reading_status` | `reading`, `paused`, `completed`, `dropped`, `plan_to_read`. |
| `platform_id` | Filters through the linked digital series. |
| `follow_updates` | Boolean-like value. |
| `search` | Searches linked digital series title. |
| `page` / `per_page` | Pagination. |

### Create Digital Tracking

`POST /api/my/digital-tracking`

Auth: required

Body:

```json
{
  "digital_series_id": 1,
  "follow_updates": true,
  "notify_new_episode": true,
  "reading_status": "reading",
  "last_episode_read": 12,
  "last_episode_seen": 15,
  "started_at": "2026-06-21",
  "notes": "Read on the official platform."
}
```

Security note: users can only list, update, and delete their own digital tracking rows.

### Update, Delete Digital Tracking

| Method | Route | Auth |
| --- | --- | --- |
| `PUT` | `/api/my/digital-tracking/{tracking}` | Required |
| `DELETE` | `/api/my/digital-tracking/{tracking}` | Required |

## My Digital Episode Reads

Episode read records are user-private.

| Method | Route | Auth |
| --- | --- | --- |
| `POST` | `/api/my/digital-episode-reads` | Required |
| `PUT` | `/api/my/digital-episode-reads/{read}` | Required |
| `DELETE` | `/api/my/digital-episode-reads/{read}` | Required |

Create body:

```json
{
  "digital_episode_id": 1,
  "read_status": "read",
  "read_at": "2026-06-21T10:00:00Z",
  "notes": "Finished."
}
```

Security note: users cannot update or delete another user's read records.

## Alerts

Alerts are user-private.

### List My Alerts

`GET /api/my/alerts`

Auth: required

Query params:

| Param | Description |
| --- | --- |
| `status` | `unread`, `read`, `dismissed`. |
| `alert_type` | `physical_release`, `digital_update`, `missing_volume`, `system`. |
| `page` / `per_page` | Pagination. |

### Create Alert

`POST /api/my/alerts`

Auth: required

Body:

```json
{
  "alert_type": "system",
  "work_id": 1,
  "physical_volume_id": null,
  "digital_series_id": null,
  "digital_episode_id": null,
  "title": "Welcome",
  "message": "CatManReaders is ready.",
  "status": "unread"
}
```

Security note: users only see, mark, dismiss, and delete their own alerts. Operations on another user's alert return `404`.

### Mark Alert As Read

`PUT /api/my/alerts/{alert}/read`

Auth: required

Response:

```json
{
  "data": {
    "id": 1,
    "status": "read",
    "read_at": "2026-06-21T10:00:00.000000Z"
  }
}
```

### Dismiss, Delete Alert

| Method | Route | Auth |
| --- | --- | --- |
| `PUT` | `/api/my/alerts/{alert}/dismiss` | Required |
| `DELETE` | `/api/my/alerts/{alert}` | Required |

## Book Lookup

Book lookup endpoints are protected. They store public metadata lookup cache in `book_metadata_lookups`. They do not download protected content, bypass logins, or use private APIs.

### Lookup By ISBN/EAN

`GET /api/book-lookup/isbn/{isbn}`

Auth: required

Path param:

| Param | Description |
| --- | --- |
| `isbn` | ISBN-10, ISBN-13, or EAN-13. Spaces and hyphens are accepted and normalized. |

Response:

```json
{
  "data": [
    {
      "title": "Matilda",
      "original_title": null,
      "volume_title": "A Novel",
      "authors": ["/authors/OL34184A"],
      "publisher": "Puffin",
      "published_date": "1988-10-01",
      "language": null,
      "country": null,
      "isbn_10": "0140328726",
      "isbn_13": "9780140328721",
      "ean": "9780140328721",
      "page_count": 240,
      "cover_url": "https://covers.openlibrary.org/b/id/8739161-L.jpg",
      "description": null,
      "provider": "open_library",
      "provider_id": "/books/OL7353617M",
      "provider_url": "https://openlibrary.org/books/OL7353617M",
      "raw_data": {}
    }
  ]
}
```

Invalid ISBN/EAN response:

```json
{
  "message": "ISBN/EAN must be a valid ISBN-10, ISBN-13, or EAN-13 code."
}
```

### Search By Title

`GET /api/book-lookup/search`

Auth: required

Query params:

| Param | Description |
| --- | --- |
| `query` | Required, minimum 2 characters. |
| `author` | Optional provider filter. |
| `publisher` | Optional provider filter. |
| `language` | Optional provider filter. |
| `country` | Optional app-level field for future providers. |

Response:

```json
{
  "data": [
    {
      "title": "Yotsuba&!",
      "authors": ["Kiyohiko Azuma"],
      "publisher": "Yen Press",
      "published_date": "2003",
      "isbn_10": "0316073873",
      "isbn_13": "9780316073875",
      "ean": "9780316073875",
      "provider": "open_library",
      "provider_id": "/works/OL45804W"
    }
  ]
}
```

### Import Book Lookup Result

`POST /api/book-lookup/import`

Auth: required

This endpoint creates a work if needed, creates or reuses a physical volume, and can optionally add the volume to the authenticated user's collection.

Body:

```json
{
  "title": "Yotsuba&!",
  "original_title": null,
  "volume_title": "Volume 1",
  "authors": ["Kiyohiko Azuma"],
  "publisher": "Yen Press",
  "published_date": "2009-09-15",
  "language": "ingles",
  "country": "Estados Unidos",
  "isbn_10": "0316073873",
  "isbn_13": "9780316073875",
  "ean": "9780316073875",
  "page_count": 224,
  "cover_url": "https://example.test/yotsuba.jpg",
  "description": "Public metadata only.",
  "provider": "open_library",
  "provider_id": "/works/OL45804W",
  "provider_url": "https://openlibrary.org/works/OL45804W",
  "raw_data": {},
  "volume_number": 1,
  "work_type": "manga",
  "add_to_collection": true,
  "ownership_status": "owned",
  "reading_status": "not_started",
  "is_travel_memory": false,
  "purchase_country": "Espana"
}
```

Response:

```json
{
  "work": {
    "id": 1,
    "title": "Yotsuba&!"
  },
  "physical_volume": {
    "id": 10,
    "work_id": 1,
    "volume_number": "1.00",
    "isbn": "9780316073875",
    "ean": "9780316073875"
  },
  "collection_item": {
    "id": 20,
    "user_id": 1,
    "physical_volume_id": 10,
    "ownership_status": "owned"
  }
}
```

Import deduplication notes:

- Physical volumes are reused by ISBN/EAN when available.
- If no code matches, the import falls back to work + volume number + language + country + publisher + edition identity.
- Users can edit or correct incoming fields before importing.
