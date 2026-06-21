# CatManReaders

Backend Laravel para gestionar una coleccion personal de manga fisico y lecturas digitales. Esta primera fase no incluye frontend ni scraping: solo API REST, PostgreSQL, Sanctum, migraciones, modelos, seeders y una base intercambiable para proveedores de metadatos de libros.

## Stack

- Laravel 12
- PostgreSQL 16
- Laravel Sanctum
- Docker Compose

## Arranque local

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan test
```

La API queda en `http://localhost:8000/api`.

Adminer queda disponible en `http://localhost:8080`.

## Docker, migraciones y tests

Levantar servicios:

```bash
docker compose up -d --build
```

Ejecutar migraciones y seeders:

```bash
docker compose exec app php artisan migrate --seed
```

Recrear la base local desde cero:

```bash
docker compose exec app php artisan migrate:fresh --seed
```

Ejecutar tests:

```bash
php artisan test
docker compose exec app php artisan test
```

Para forzar la suite contra PostgreSQL dentro de Docker:

```bash
docker compose exec -e DB_CONNECTION=pgsql -e DB_HOST=db -e DB_PORT=5432 -e DB_DATABASE=catmanreaders -e DB_USERNAME=catman -e DB_PASSWORD=secret app php artisan test
```

Credenciales de base de datos por defecto:

- Servidor: `db`
- Base de datos: `catmanreaders`
- Usuario: `catman`
- Password: `secret`

## Autenticacion

```http
POST /api/register
POST /api/login
POST /api/logout
GET /api/me
```

Todas las rutas salvo `register` y `login` usan `auth:sanctum`. En clientes API envia el token como:

```http
Authorization: Bearer <token>
```

## Endpoints principales

- `GET|POST /api/works`
- `GET|PUT|DELETE /api/works/{work}`
- `GET|POST /api/works/{work}/physical-volumes`
- `GET|PUT|DELETE /api/physical-volumes/{physicalVolume}`
- `GET /api/book-lookup/isbn/{isbn}`
- `GET /api/book-lookup/search?query=...`
- `POST /api/book-lookup/import`
- `GET|POST /api/my/physical-collection`
- `PUT|DELETE /api/my/physical-collection/{item}`
- `GET|POST /api/my/work-tracking`
- `PUT|DELETE /api/my/work-tracking/{tracking}`
- `GET|POST /api/digital-platforms`
- `GET|POST /api/digital-series`
- `GET|POST /api/digital-series/{digitalSeries}/episodes`
- `GET|POST /api/my/digital-tracking`
- `POST|PUT|DELETE /api/my/digital-episode-reads`
- `GET /api/my/alerts`
- `POST /api/my/alerts`
- `PUT /api/my/alerts/{alert}/read`
- `PUT /api/my/alerts/{alert}/dismiss`

## Metadatos de libros

La capa inicial vive en `app/Services/BookMetadata`:

- `BookMetadataProviderInterface`
- `OpenLibraryMetadataProvider`
- `ManualMetadataProvider`
- `BookLookupService`

Los proveedores activos se configuran con:

```env
BOOK_METADATA_PROVIDERS=open_library,manual
GOOGLE_BOOKS_API_KEY=
```

Open Library se consulta solo para metadatos publicos. No se descargan contenidos protegidos ni se usan APIs privadas.

## Documentacion

- API completa: `docs/API.md`
- Flujos de uso: `docs/FLOWS.md`
- Coleccion Postman: `docs/postman/CatManReaders.postman_collection.json`
