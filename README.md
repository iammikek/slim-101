# Getting Fast at Slim

A step-by-step **[Slim 4](https://www.slimframework.com/)** port of the *-101 items/categories API — same JSON contract as [framework-x-101](https://github.com/iammikek/framework-x-101) and [laravel-101](https://github.com/iammikek/laravel-101), **API-only** (no `/shop` UI).

**Audience:** PHP developers comparing micro-frameworks: Slim is the classic PSR-7 / PSR-15 path (FastRoute + middleware stack), while Framework X is the ReactPHP twin with a built-in shop.

**API-only by design:** Other PHP *-101 projects (Laravel, Symfony, Framework X) include a server-rendered `/shop`. Slim has no session/templating story comparable to Blade — this repo focuses on a lean JSON REST API. Pair with [react-101](https://github.com/iammikek/react-101), [vue-101](https://github.com/iammikek/vue-101), or curl.

---

## What's Included

1. **Slim 4** — invokable controllers, FastRoute, PSR-15 middleware
2. **PHP-DI** — constructor injection for services and middleware
3. **PDO + SQLite** — schema in `database/schema.sql`, no ORM
4. **`UserService`** — register/login/me, JWT (`firebase/php-jwt`)
5. **`CategoryService` + `ItemService`** — same business logic as framework-x-101
6. **Pagination** — `{ items, total, skip, limit }`
7. **Filtering** — `min_price`, `max_price`, `category_id`, `name_contains`
8. **Item stats** — `GET /items/stats/summary`
9. **JWT auth** — Bearer tokens on write endpoints + `/auth/me`
10. **Exception middleware** — domain errors → JSON `{ detail, code }`
11. **SQLite locally** — same in Docker (port **8016**)
12. **Tests** — PHPUnit feature tests (`$app->handle($request)`)
13. **CI** — GitHub Actions

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

Open **http://127.0.0.1:8016/** — hello message  
**http://127.0.0.1:8016/items** — JSON list

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
| GET | `/` | — | Hello message |
| GET | `/health` | — | Health check |
| POST | `/auth/register` | — | Register user |
| POST | `/auth/login` | — | Login (form or JSON) |
| GET | `/auth/me` | JWT | Current user |
| GET | `/categories` | — | List categories |
| GET | `/categories/{id}` | — | Show category |
| POST/PATCH/DELETE | `/categories` | JWT | Manage categories |
| GET | `/items` | — | List items (paginated, filterable) |
| GET | `/items/stats/summary` | — | Item statistics |
| GET | `/items/{id}` | — | Show item |
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

| Laravel / Framework X | Slim 4 (this repo) |
|-----------------------|--------------------|
| `routes/api.php` / `$app->get(...)` | `bootstrap/routes.php` |
| Container / Framework X `Container` | PHP-DI |
| `auth:api` / JwtAuth callable | PSR-15 `JwtAuthMiddleware` |
| ReactPHP `Response` | `Slim\Psr7\Response` |
| `$app($request)` test client | `$app->handle($request)` |
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
