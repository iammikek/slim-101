# Getting Fast at Slim

A step-by-step **[Slim 4](https://www.slimframework.com/)** port of [laravel-101](https://github.com/iammikek/laravel-101) for Laravel developers learning Slim. Same items/categories JSON API, JWT auth, pagination, filters, and stats. **API-only** (no `/shop` UI).

**Audience:** You already know routes, Eloquent, migrations, middleware, and validation. Invokable controllers and PHP-DI stand in for Laravel's HTTP layer and container; PDO services stand in for Eloquent; PSR-15 middleware stands in for the HTTP kernel.

**API-only by design:** laravel-101 also includes a Blade shop at `/shop`. Slim has no session/templating story comparable to Blade, so this repo keeps the JSON API. Pair it with [react-101](https://github.com/iammikek/react-101), [vue-101](https://github.com/iammikek/vue-101), or curl.

---

## What's Included

1. **Slim 4:** invokable controllers, FastRoute, PSR-15 middleware
2. **PHP-DI:** constructor injection for services and middleware
3. **PDO + SQLite:** schema in `database/schema.sql`, no ORM
4. **`UserService`:** register/login/me, JWT (`firebase/php-jwt`)
5. **`CategoryService` + `ItemService`:** same business logic as laravel-101
6. **Pagination:** `{ items, total, skip, limit }`
7. **Filtering:** `min_price`, `max_price`, `category_id`, `name_contains`
8. **Item stats:** `GET /items/stats/summary`
9. **JWT auth:** Bearer tokens on write endpoints + `/auth/me`
10. **Exception middleware:** domain errors become JSON `{ detail, code }`
11. **SQLite locally:** same in Docker (port **8016**)
12. **Tests:** PHPUnit feature tests (`$app->handle($request)`)
13. **CI:** GitHub Actions

---

## Quick Start

### Local PHP (SQLite)

```bash
cd slim-101
cp .env.example .env
composer install
make migrate
make serve
```

Open **http://127.0.0.1:8016/** for the hello message.
**http://127.0.0.1:8016/items** returns the JSON list.

### Docker (SQLite)

```bash
docker compose up --build
```

API on **http://localhost:8016**.

### Tests

```bash
make test
```

---

## Project Structure

```
slim-101/
├── bootstrap/
│   ├── app.php          # Slim app + PHP-DI container
│   └── routes.php       # Route map
├── public/index.php     # Front controller
├── src/
│   ├── Controller/      # Invokable controllers
│   ├── Database/        # PDO helpers
│   ├── Exception/       # Domain errors
│   ├── Middleware/      # JWT + exception mapping (PSR-15)
│   ├── Service/         # User / Category / Item
│   └── Support/         # Http, Jwt, Validator, ApiSerializer
├── database/
│   ├── schema.sql
│   └── migrate.php
└── tests/Feature/       # PHPUnit feature tests
```

---

## API endpoints

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/` | No | Hello message |
| GET | `/health` | No | Health check |
| POST | `/auth/register` | No | Register user |
| POST | `/auth/login` | No | Login (form or JSON) |
| GET | `/auth/me` | JWT | Current user |
| GET | `/categories` | No | List categories |
| GET | `/categories/{id}` | No | Show category |
| POST/PATCH/DELETE | `/categories` | JWT | Manage categories |
| GET | `/items` | No | List items (paginated, filterable) |
| GET | `/items/stats/summary` | No | Item statistics |
| GET | `/items/{id}` | No | Show item |
| POST/PATCH/DELETE | `/items` | JWT | Manage items |

Write operations require `Authorization: Bearer <token>`.

---

## Try it with curl

```bash
curl http://127.0.0.1:8016/
curl http://127.0.0.1:8016/health

curl -X POST http://127.0.0.1:8016/auth/register \
  -H 'Content-Type: application/json' \
  -d '{"email":"ada@example.com","password":"password123"}'

TOKEN=$(curl -s -X POST http://127.0.0.1:8016/auth/login \
  -d 'username=ada@example.com&password=password123' \
  | php -r 'echo json_decode(stream_get_contents(STDIN))->access_token;')

curl -X POST http://127.0.0.1:8016/categories \
  -H "Authorization: Bearer $TOKEN" -H 'Content-Type: application/json' \
  -d '{"name":"Tools","description":"Hand tools"}'

curl -X POST http://127.0.0.1:8016/items \
  -H "Authorization: Bearer $TOKEN" -H 'Content-Type: application/json' \
  -d '{"name":"Widget","price":9.99}'

curl 'http://127.0.0.1:8016/items?skip=0&limit=10&min_price=1&name_contains=wid'
curl http://127.0.0.1:8016/items/stats/summary
```

---

## Environment variables

| Variable | Default | Description |
|----------|---------|-------------|
| `PORT` | `8016` | Listen port |
| `APP_HOST` | `127.0.0.1` | Bind address |
| `DATABASE_PATH` | `database/database.sqlite` | SQLite file path |
| `JWT_SECRET` | `change-me-in-production` | JWT signing secret |

---

## Framework maps

| Laravel | Slim 4 (this repo) |
|---------|---------------------|
| `routes/api.php` | `bootstrap/routes.php` |
| Service container | PHP-DI |
| Eloquent model | PDO + `Service` classes |
| `artisan migrate` | `make migrate` (`database/schema.sql`) |
| `auth` middleware | PSR-15 `JwtAuthMiddleware` |
| `Validator::make()` | `Support\Validator` |
| PHPUnit feature tests | `$app->handle($request)` |
| Catalog Shop `/shop` | Omitted (API-only) |

---

## Quick Reference

| Task | Command |
|------|---------|
| Install | `composer install` |
| Migrate | `make migrate` |
| Run local | `make serve` → http://127.0.0.1:8016 |
| Tests | `make test` |
| Docker | `make docker-up` |

---

## *-101 Family

Full family list, ports, and clone-with-submodules: **[learning-101](https://github.com/iammikek/learning-101)**. Site catalogue: [automica.io/learning-101](https://automica.io/learning-101.html).
