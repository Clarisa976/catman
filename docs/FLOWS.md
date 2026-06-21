# CatManReaders Usage Flows

Base URL:

```text
http://localhost:8000/api
```

All flows after login use:

```http
Authorization: Bearer TOKEN
Accept: application/json
```

## Flow 1: Add A Physical Manga Manually

1. Login.

```http
POST /api/login
```

```json
{
  "email": "usagi@example.com",
  "password": "password123"
}
```

2. Create the work.

```http
POST /api/works
```

```json
{
  "title": "Haikyuu!!",
  "author": "Haruichi Furudate",
  "type": "manga",
  "status": "completed"
}
```

Save `data.id` as `work_id`.

3. Create the physical volume.

```http
POST /api/works/{work_id}/physical-volumes
```

```json
{
  "volume_number": 1,
  "title": "Volume 1",
  "language": "espanol",
  "country": "Espana",
  "publisher": "Planeta Comic",
  "isbn": "9781234567890",
  "ean": "9781234567890",
  "price": 9.95,
  "currency": "EUR"
}
```

Save `data.id` as `physical_volume_id`.

4. Add the volume to my collection.

```http
POST /api/my/physical-collection
```

```json
{
  "physical_volume_id": 1,
  "ownership_status": "owned",
  "reading_status": "not_started",
  "is_travel_memory": false,
  "location": "Shelf A"
}
```

## Flow 2: Add A Physical Manga By ISBN/EAN

1. Login.

```http
POST /api/login
```

2. Lookup the ISBN/EAN.

```http
GET /api/book-lookup/isbn/9780316073875
```

Review the first result in `data[0]`. The user can correct fields before import.

3. Import the result and add it to the collection in one request.

```http
POST /api/book-lookup/import
```

```json
{
  "title": "Yotsuba&!",
  "volume_title": "Volume 1",
  "authors": ["Kiyohiko Azuma"],
  "publisher": "Yen Press",
  "published_date": "2009-09-15",
  "language": "ingles",
  "country": "Estados Unidos",
  "isbn_13": "9780316073875",
  "ean": "9780316073875",
  "volume_number": 1,
  "provider": "open_library",
  "provider_id": "/works/OL45804W",
  "provider_url": "https://openlibrary.org/works/OL45804W",
  "raw_data": {},
  "add_to_collection": true,
  "ownership_status": "owned",
  "reading_status": "not_started"
}
```

The response contains `work`, `physical_volume`, and `collection_item`.

Alternative: import without `add_to_collection`, then call `POST /api/my/physical-collection` with the returned physical volume id.

## Flow 3: Follow A Physical Work

1. Create or reuse a work.

```http
POST /api/works
```

```json
{
  "title": "Slam Dunk",
  "author": "Takehiko Inoue",
  "type": "manga",
  "status": "completed"
}
```

2. Create physical tracking for that work.

```http
POST /api/my/work-tracking
```

```json
{
  "work_id": 1,
  "follow_physical_releases": true,
  "preferred_language": "espanol",
  "preferred_country": "Espana",
  "preferred_publisher": "Ivrea",
  "last_owned_volume_number": 4,
  "notify_new_volume": true
}
```

Later phases can generate alerts for new physical volumes using this tracking data. Current phase only stores tracking preferences and progress metadata.

## Flow 4: Register A Digital Reading

1. List or create a platform.

```http
GET /api/digital-platforms
```

```http
POST /api/digital-platforms
```

```json
{
  "name": "WEBTOON",
  "website_url": "https://www.webtoons.com"
}
```

2. Create the digital series.

```http
POST /api/digital-series
```

```json
{
  "platform_id": 1,
  "title": "Under the Oak Tree",
  "platform_url": "https://example.test/series",
  "language": "ingles",
  "status": "ongoing",
  "latest_episode_detected": 90
}
```

3. Create digital tracking.

```http
POST /api/my/digital-tracking
```

```json
{
  "digital_series_id": 1,
  "reading_status": "reading",
  "follow_updates": true,
  "notify_new_episode": true,
  "last_episode_read": 12,
  "last_episode_seen": 15
}
```

4. Update the last episode read.

```http
PUT /api/my/digital-tracking/{tracking}
```

```json
{
  "last_episode_read": 13,
  "last_episode_seen": 15,
  "reading_status": "reading"
}
```

Optional: create individual episode read rows with `POST /api/my/digital-episode-reads` after adding digital episode catalog records.

## Flow 5: Manage Alerts

1. List alerts.

```http
GET /api/my/alerts?status=unread
```

2. Mark an alert as read.

```http
PUT /api/my/alerts/{alert}/read
```

3. Dismiss an alert.

```http
PUT /api/my/alerts/{alert}/dismiss
```

4. Delete an alert.

```http
DELETE /api/my/alerts/{alert}
```

Security note: alert operations are scoped to the authenticated user. Trying to mark, dismiss, or delete another user's alert returns `404`.
