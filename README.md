# Sport API

REST API built with Laravel 13 that acts as a gateway between a frontend and [TheSportsDB](https://www.thesportsdb.com/), with user authentication (Laravel Sanctum) and per-user favorite teams stored in PostgreSQL.

- The frontend never calls TheSportsDB directly. Laravel calls it with the HTTP Client and caches the responses for 10 minutes (`file` cache store).
- Consistent JSON envelope for every response: `{ "success": bool, "message": string, "data": mixed }`.
- Match times are converted to Indonesian Western Time (WIB, `Asia/Jakarta`).

## Branches

This repository has 2 branches. They differ only in **how the Sanctum token reaches the client**.

| Branch | Authentication behavior |
|---|---|
| `master` | Login/register return the Sanctum token in the JSON body (`data.token`). The client sends it as `Authorization: Bearer <token>`. |
| `cookies` | Login/register set the token as an **HttpOnly cookie** (`api_token`), so it never reaches JavaScript or `localStorage`. Adds a cookie-auth middleware, a CSRF-style guard (`X-Requested-With` header on POST/PUT/PATCH/DELETE), and a CORS config restricted to `FRONTEND_URL`. Also adds `docs/frontend-contract.md`. |

Switch with:

```bash
git checkout master     # Bearer token in the response body
git checkout cookies    # HttpOnly cookie authentication
```

Everything else (endpoints, database, caching, TheSportsDB integration) is the same on both branches.

## Requirements

- PHP 8.3+ with extensions `pdo_pgsql`, `pdo_sqlite` (used by the test suite), `mbstring`, `openssl`, `curl`
- Composer 2
- PostgreSQL 14+ (local database)
- Internet access (for TheSportsDB)

Node.js is **not** required (backend/API only).

## Installation

1. **Clone and enter the project**

   ```bash
   git clone <repository-url> restfull-api-sport
   cd restfull-api-sport
   git checkout master        # or: git checkout cookies
   ```

2. **Install PHP dependencies**

   ```bash
   composer install
   ```

3. **Create the environment file and app key**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   (On Windows PowerShell: `Copy-Item .env.example .env`.)

4. **Create the PostgreSQL database**

   ```sql
   CREATE DATABASE sportdb;
   ```

5. **Edit `.env`** — the example file defaults to SQLite and the `database` cache store, so change these values:

   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=sportdb
   DB_USERNAME=postgres
   DB_PASSWORD=your_password

   CACHE_STORE=file

   THESPORTSDB_BASE_URL=https://www.thesportsdb.com/api/v1/json
   THESPORTSDB_API_KEY=123
   ```

   On the `cookies` branch also set:

   ```env
   FRONTEND_URL=http://localhost:5174   # exact origin of your frontend
   SANCTUM_TOKEN_EXPIRATION=10080       # minutes (7 days), matches the cookie lifetime
   ```

   `THESPORTSDB_API_KEY=123` is TheSportsDB's public free key (limited: 30 requests/minute and some restricted data).

6. **Run the migrations**

   ```bash
   php artisan migrate
   ```

## Running the application

```bash
php artisan serve
```

The API is now available at `http://localhost:8000/api`.

If you use Laragon, you can instead point a virtual host at the `public/` directory, and use that host name in place of `localhost:8000`.

Quick check (public endpoint, no login needed):

```bash
curl http://localhost:8000/api/sports
```

### API documentation

| What | Where |
|---|---| 
| OpenAPI spec | `docs/openapi.yaml` (the served copy is `public/docs/openapi.yaml`; keep both in sync) |
| Postman collection | `docs/postman_collection.json` (File > Import in Postman) |
| Frontend contract (`cookies` branch) | `docs/frontend-contract.md` |

## Endpoints

| Method | Path | Auth | Description |
|---|---|---|---|
| POST | `/api/register` | public | Register a user |
| POST | `/api/login` | public | Log in |
| GET | `/api/me` | required | Current user profile |
| POST | `/api/logout` | required | Log out (revokes the current token) |
| GET | `/api/sports` | public | List sports |
| GET | `/api/leagues?sport=Soccer` | public | List leagues (name and logo `strBadge`) |
| GET | `/api/leagues/{id}/teams` | public | Teams in a league |
| GET | `/api/leagues/{id}/table` | public | League standings (`?season=2024-2025` optional) |
| GET | `/api/teams/{id}` | public | Team details |
| GET | `/api/teams/{id}/previous-matches` | public | Previous matches (times in WIB) |
| GET | `/api/favorites` | required | List the user's favorite teams |
| POST | `/api/favorites` | required | Add a favorite team |
| DELETE | `/api/favorites/{id}` | required | Remove a favorite team |

### Authenticating requests

**`master` branch** — copy `data.token` from the login/register response and send it on protected requests:

```
Authorization: Bearer <token>
Accept: application/json
```

**`cookies` branch** — the token is stored in an HttpOnly cookie automatically. Clients must send credentials (`withCredentials: true` in axios, or Postman's cookie jar) and, on POST/PUT/PATCH/DELETE, the header:

```
X-Requested-With: XMLHttpRequest
```

Example register body:

```json
{
    "name": "Jane Doe",
    "email": "jane@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

## Caching

TheSportsDB responses are cached with Laravel's `Cache::remember()` for 10 minutes (`CACHE_STORE=file`, files in `storage/framework/cache/data`). Inspect or clear the cache:

```bash
php artisan tinker
>>> Cache::has('thesportsdb.leagues.Soccer');
php artisan cache:clear
```

## Running the tests

```bash
php artisan test
```

The suite uses an in-memory SQLite database and fake HTTP responses, so it needs neither PostgreSQL nor internet access.

## Code style

```bash
vendor/bin/pint
```

## Troubleshooting

- **`could not find driver`** — enable `pdo_pgsql` (and `pdo_sqlite` for tests) in `php.ini`. i am using laragon for running pgsql
- **`SQLSTATE ... password authentication failed`** — check `DB_USERNAME` / `DB_PASSWORD` in `.env`, then `php artisan config:clear`.
- **502 from the API** — TheSportsDB is unreachable, or the free-key rate limit (30 requests/minute) was hit. Wait a minute and retry.
- **Browser CORS or 401/403 errors (`cookies` branch)** — `FRONTEND_URL` must exactly match the frontend origin, requests must use `withCredentials`, and unsafe methods need the `X-Requested-With` header. Restart `php artisan serve` after editing `.env`.
- **`/docs` shows Not Found** — restart `php artisan serve` so new routes are loaded.

## License

MIT
