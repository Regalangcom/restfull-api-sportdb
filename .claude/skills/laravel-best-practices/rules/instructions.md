# Backend Technical Test

## Scope

These instructions apply only to the implementation of the backend Laravel technical test described below.

Do not treat these instructions as general Laravel project-wide conventions outside this technical test.

## Technical Test Requirements

The backend must:

1. Provide user registration and login using Laravel Sanctum .
2. Protect endpoints that require authentication, such as adding favorite teams.
3. Act as the API gateway between the frontend and TheSportsDB.
4. The frontend must not call TheSportsDB directly.
5. Laravel must call TheSportsDB using Laravel HTTP Client.
6. Cache TheSportsDB responses for a period of time to reduce external requests.
7. Use Laravel migrations for the database structure.
8. Define appropriate database relationships.
9. Provide consistent RESTful JSON responses for success and error cases.

## Implementation Direction

For this technical test:

- Use Laravel Sanctum for authentication.
- Use PostgreSQL as the local database.
- Keep the implementation backend/API only.
- Use `routes/api.php` for API routes.
- Use Eloquent for local database entities.
- Keep TheSportsDB HTTP communication outside controllers, preferably in a focused service.
- Keep TheSportsDB caching close to the external API integration.
- Use Laravel's HTTP Client for TheSportsDB requests.
- Use Laravel Cache for TheSportsDB response caching.
- Use `CACHE_STORE=file` for this technical test.
- Use an explicit cache TTL.
- Make cache keys specific to the external request when request parameters affect the response.
- Use validation for client-provided input.
- Ensure authenticated users can only manage their own favorite teams.
- Do not trust a client-provided `user_id` to determine ownership.
- Use database constraints to prevent duplicate favorite teams for the same user where appropriate.
- Keep API responses consistent and JSON-based.
- Handle external API errors and timeouts without exposing internal implementation details.

## Architecture

Prefer a simple separation of responsibilities:

```text
Route
  ↓
Controller
  ↓
Service / Eloquent
  ↓
External API / PostgreSQL

For TheSportsDB:

Controller
  ↓
TheSportsDbService
  ├── Cache
  └── Laravel HTTP Client
          ↓
      TheSportsDB

Controllers should primarily handle HTTP concerns and orchestration.

Do not put the implementation of TheSportsDB HTTP requests directly inside controllers.

Do not introduce unnecessary repositories, DTOs, events, jobs, or other abstractions unless they solve a real problem in this technical test.

Observers are optional. Do not create an Observer merely because caching exists. An Observer is only appropriate when a local Eloquent model lifecycle event requires a side effect, such as invalidating cached local data.

Authentication

Use Laravel Sanctum.

Protected routes should use:

auth:sanctum

For user-owned resources, obtain ownership from the authenticated user:

$request->user()

Do not accept a client-provided user_id as the source of ownership.

Caching

The cache requirement specifically concerns responses from TheSportsDB.

Use:

CACHE_STORE=file

Use Laravel's Cache API, for example:

Cache::remember(
    $cacheKey,
    now()->addMinutes(10),
    fn () => ...
);

The exact TTL may be adjusted if there is a technical reason, but caching must have a finite expiration period.

Do not add Redis or another external cache system for this technical test unless explicitly requested.

API Response Contract

Use one consistent JSON response structure throughout the API.

Example success:

{
    "success": true,
    "message": "Success message",
    "data": {}
}

Example error:

{
    "success": false,
    "message": "Error message",
    "data": null
}

The exact messages may vary, but the response structure should remain consistent.

Before Coding

Before modifying the project:

Inspect the existing Laravel project.
Check the current Laravel and PHP setup.
Check the existing database configuration.
Check Sanctum configuration.
Check existing routes, models, migrations, and controllers.
Identify existing code that can be reused.
Determine the minimum files required for the technical test.
Produce an implementation plan before making substantial changes.