# Caching Best Practices

NOTE : CACHE STORE using file   

## Cache External API Responses

Cache responses from external APIs when the application repeatedly requests
the same data and the external response does not need to be real-time.

For this project, responses from TheSportsDB should be cached to reduce
unnecessary external API requests.

Use Laravel's built-in Cache API instead of implementing a custom caching
mechanism.

The primary caching flow should be:

```text
API Request
    ↓
Service
    ↓
Cache
    ↓
Cache Hit? ── Yes ──→ Return cached response
    │
    No
    ↓
TheSportsDB
    ↓
Store response in cache
    ↓
Return response
Use Cache::remember() for Cache-Aside Reads

Use Cache::remember() when reading data that can be retrieved from a
cache and recomputed when the cache entry does not exist.

Example:

```php
return Cache::remember(
    'thesportsdb.sports',
    now()->addMinutes(10),
    fn () => Http::get($url)->json()
);



Cache::remember() avoids unnecessary manual cache existence checks.

Avoid:

$value = Cache::get('thesportsdb.sports');

if (! $value) {
    $value = $this->fetchSports();

    Cache::put('thesportsdb.sports', $value, 600);
}

Prefer:

$value = Cache::remember(
    'thesportsdb.sports',
    now()->addMinutes(10),
    fn () => $this->fetchSports()
);

```
Do not use truthiness checks to determine whether a cache entry exists,
because valid cached values may be false, 0, an empty string, or another
falsy value.

Keep External API Caching in the Service Layer

Caching for TheSportsDB should be implemented inside the service responsible
for TheSportsDB integration.

Example:

Controller
    ↓
TheSportsDbService
    ↓
Cache
    ↓
Laravel HTTP Client
    ↓
TheSportsDB

Example:

```php
class TheSportsDbService
{
    public function getSports(): array
    {
        return Cache::remember(
            'thesportsdb.sports',
            now()->addMinutes(10),
            fn () => $this->fetchSports()
        );
    }

    private function fetchSports(): array
    {
        return Http::get($this->baseUrl . '/all_sports.php')
            ->json();
    }
}
```

Controllers should not contain the implementation of external API caching.

Do not duplicate the same caching logic across multiple controllers.

Use an Explicit TTL

Every external API cache entry should have an explicit expiration time.

Example:

```php
Cache::remember(
    'thesportsdb.sports',
    now()->addMinutes(10),
    fn () => $this->fetchSports()
);
```

For this technical test, a TTL between 5 and 15 minutes is reasonable unless
the requirements specify a different duration.

The TTL should be long enough to reduce repeated requests but short enough
to avoid serving unnecessarily stale external data.

Do not create permanent cache entries for external API responses.

Use Meaningful Cache Keys

Cache keys should clearly identify the external data being cached.

Good examples:

thesportsdb.sports
thesportsdb.teams.{league}
thesportsdb.team.{teamId}

Avoid vague keys:

data
result
api
cache

If the external response depends on parameters, include those parameters in
the cache key.

Example:

$key = "thesportsdb.teams.{$leagueId}";

```php
return Cache::remember(
    $key,
    now()->addMinutes(10),
    fn () => $this->fetchTeams($leagueId)
);
```

Different requests that produce different data must not share the same
cache key.

Keep Cache Keys Centralized When Appropriate

When multiple methods use related cache keys, avoid duplicating complex key
construction logic.

For example:

private function teamCacheKey(string $teamId): string
{
    return "thesportsdb.team.{$teamId}";
}

Use this only when it improves readability.

Do not create a dedicated cache-key abstraction for a few simple keys.

Cache the Appropriate Data

Cache data that is:

Frequently requested.
Expensive or unnecessary to retrieve repeatedly.
Safe to temporarily store.
Not required to be real-time.

For this project, suitable cache candidates include TheSportsDB responses
such as:

Sports
Teams
Team details
Leagues

depending on which TheSportsDB endpoints are implemented.

Do not automatically cache every database query or every API response.

Caching should solve a specific performance or external-request problem.

Do Not Cache Sensitive User-Specific Data Globally

Be careful when caching data that belongs to an authenticated user.

If user-specific data is cached, the cache key must include the user ID.

Example:

$key = "user:{$user->id}:favorites";

Avoid:

favorites

for data that differs between users.

A shared key can cause one user's cached data to be returned to another
user.

Keep PostgreSQL as the Source of Truth

Cache is temporary storage and must not replace persistent application data.

For this project:

Users
Favorite Teams
        ↓
    PostgreSQL
   Source of Truth

While:

TheSportsDB Response
        ↓
       Cache
  Temporary Storage

Do not rely on the cache as the permanent source of truth for:

Users.
Authentication data.
Favorite teams.
Other persistent application data.
Invalidate Local Data Caches When Necessary

If local database data is cached, invalidate the cache when the underlying
data changes.

For example, if favorite teams are cached:

FavoriteTeam created
        ↓
FavoriteTeamObserver
        ↓
Cache::forget()

Example:

```php
public function created(FavoriteTeam $favoriteTeam): void
{
    Cache::forget(
        "user:{$favoriteTeam->user_id}:favorites"
    );
}

public function deleted(FavoriteTeam $favoriteTeam): void
{
    Cache::forget(
        "user:{$favoriteTeam->user_id}:favorites"
    );
}
```

Use an Eloquent Observer when cache invalidation is naturally triggered by
model lifecycle events.

Do not use an Observer for TheSportsDB response caching.

TheSportsDB response caching belongs in TheSportsDbService.

Invalidate Only When Necessary

Do not invalidate unrelated cache entries.

For example, when a user's favorite team changes, invalidate only the
cache entries affected by that user.

Prefer:

Cache::forget("user:{$favoriteTeam->user_id}:favorites");

over flushing an entire cache store.

Avoid:

Cache::flush();

unless intentionally clearing all application cache is required.

Handle Cache Misses Transparently

Application code should not need to know whether data came from the cache or
from TheSportsDB.

The service should return the same data structure in both cases.

Example:

```php
public function getSports(): array
{
    return Cache::remember(
        'thesportsdb.sports',
        now()->addMinutes(10),
        fn () => $this->fetchSports()
    );
}
```

The controller simply consumes the service:

$sports = $this->theSportsDbService->getSports();

The controller should not contain separate cache-hit and cache-miss logic.

Handle External API Failures Separately from Cache Logic

Caching does not eliminate the possibility of an external API failure.

The service should handle:

Connection failures.
Timeouts.
Non-success HTTP responses.
Invalid or unexpected responses.

Example:

$response = Http::timeout(10)
    ->get($url);

$response->throw();

return $response->json();

Do not expose raw external API exceptions directly to API consumers.

Cache successful responses only when appropriate.

Do not cache a failed external API response as if it were valid application
data.

Use Laravel's HTTP Client with Caching

Use Laravel's HTTP Client for TheSportsDB requests.

Example:

$response = Http::timeout(10)
    ->get($url);

$response->throw();

return $response->json();

Combine it with Cache::remember():

```php
return Cache::remember(
    'thesportsdb.sports',
    now()->addMinutes(10),
    function () use ($url) {
        $response = Http::timeout(10)->get($url);

        $response->throw();

        return $response->json();
    }
);
```

This ensures the external request only occurs when the cache entry is
missing or expired.

Choose a Simple Cache Store

For local development and a small technical-test application, the file
cache store is sufficient unless the project explicitly requires another
store.

Example:

CACHE_STORE=file

The application should use Laravel's Cache abstraction so the underlying
cache store can be changed later without changing application-level caching
logic.

Do not introduce Redis solely to satisfy the caching requirement.

Cache Store Compatibility

Do not assume that every Laravel cache feature is supported by every cache
driver.

For example, cache tags are not supported by all cache stores.

Before using an advanced cache feature, verify that the configured cache
driver supports it.

For this project, avoid advanced cache features unless there is a clear
requirement for them.

Do Not Over-Engineer Caching

Use the simplest caching mechanism that satisfies the requirement.

For this project, prefer:

Cache::remember()

Do not introduce the following unless there is a demonstrated requirement:

Cache::flexible()
Cache::memo()
Cache tags
Cache::lock()
Cache::add() for synchronization
Failover cache stores
Redis
Complex cache warming
Distributed locking
Custom cache abstractions

These features solve more specialized problems and are not necessary for
basic TheSportsDB response caching.

Avoid Duplicate External Requests Where Practical

The primary purpose of caching TheSportsDB responses is to reduce repeated
requests to the external API.

If multiple endpoints request the same external data, reuse the same cache
key and service method where appropriate.

Example:

GET /api/sports
        ↓
thesportsdb.sports

GET /api/sports
        ↓
thesportsdb.sports

The second request should use the cached response while the cache entry is
still valid.

Do not create multiple cache keys for the exact same external resource
without a reason.

Do Not Use Caching as a Substitute for Database Optimization

Caching should not be used to hide inefficient database queries.

For local database operations:

Use appropriate indexes.
Define correct relationships.
Avoid unnecessary queries.
Use eager loading when relationships are actually needed.

Use caching when it provides a clear benefit.

Do Not Use Advanced Concurrency Controls Without a Requirement

Cache::remember() does not prevent multiple concurrent requests from
computing the same missing cache value.

For example:

Request A ──→ Cache miss ──→ TheSportsDB
Request B ──→ Cache miss ──→ TheSportsDB

This can happen when multiple requests arrive at the same time before the
cache has been populated.

For this technical test, this behavior is acceptable unless the
requirements specifically require protection against duplicate concurrent
requests.

Do not introduce Cache::lock() or distributed locking solely to prevent
this scenario.

Do Not Use Cache::memo() Without a Specific Need

Cache::memo() is intended to avoid repeated cache-store lookups within a
single execution.

It is not a replacement for persistent application caching.

For this project, normal Cache::remember() is sufficient.

Use Cache::memo() only when repeated cache-store access within the same
request or job has been identified as a real concern.

Do Not Use Cache::flexible() Without a Specific Requirement

Cache::flexible() provides stale-while-revalidate behavior.

It can be useful for frequently accessed data where serving slightly stale
data is acceptable.

However, the technical test only requires storing TheSportsDB responses for
a period of time.

Prefer:

Cache::remember(
    'thesportsdb.sports',
    now()->addMinutes(10),
    fn () => $this->fetchSports()
);

Do not use stale-while-revalidate behavior unless the application actually
requires it.

Do Not Use Cache Tags Without a Specific Requirement

Cache tags can be useful when groups of cache entries need to be invalidated
together.

They are not supported by every cache driver.

For this project, meaningful individual cache keys and Cache::forget()
are sufficient.

Avoid introducing cache tags unless grouped invalidation is actually needed
and the configured cache store supports them.

Do Not Use Cache::add() as a Lock

Cache::add() performs an atomic conditional write, but it should not be
used as a substitute for proper locking when lock ownership and safe
release are required.

For this project, do not use Cache::add() for synchronization unless
there is an explicit concurrency requirement.

If locking is genuinely required, use Laravel's dedicated cache lock
mechanism.

Do Not Use Failover Caching Without a Production Requirement

Failover cache stores can provide resilience when a primary cache store
fails.

They are unnecessary for this small technical-test application unless
production infrastructure explicitly requires them.

Do not introduce failover caching simply because Laravel supports it.

Keep Cache Configuration in Environment Configuration

Cache store selection should be controlled through Laravel configuration and
environment variables.

Example:

CACHE_STORE=file

Do not hard-code the cache store throughout application code.

Application code should interact with:

Cache::remember(...)

rather than depending directly on a specific cache implementation.

Recommended Caching Structure

For this project, use a structure similar to:

app/
└── Services/
    └── TheSportsDbService.php
            │
            ├── getSports()
            ├── getTeams()
            └── getTeam()
                    │
                    ▼
                 Cache
                    │
                    ▼
              TheSportsDB

Example service:

```php
class TheSportsDbService
{
    public function getSports(): array
    {
        return Cache::remember(
            'thesportsdb.sports',
            now()->addMinutes(10),
            fn () => $this->fetchSports()
        );
    }

    private function fetchSports(): array
    {
        $response = Http::timeout(10)
            ->get($this->baseUrl . '/all_sports.php');

        $response->throw();

        return $response->json();
    }
}

```
Recommended Cache Responsibilities
TheSportsDbService

Responsible for:

Calling TheSportsDB.
Building external API requests.
Handling external API responses.
Caching TheSportsDB responses.
Defining appropriate cache keys and TTLs.
Controller

Responsible for:

Receiving the HTTP request.
Calling the service.
Returning the API response.

The controller should not implement TheSportsDB caching.

Eloquent Observer

Responsible for:

Invalidating cache related to local model lifecycle changes when needed.

The Observer should not contain external API integration.

PostgreSQL

Responsible for:

Persistent application data.
Users.
Favorite teams.
Database constraints and relationships.
Cache

Responsible for:

Temporary copies of frequently requested data.
Reducing repeated external API requests.
Improving response performance where appropriate.
General Rule

For this project:

Cache repeated TheSportsDB responses.
Use Cache::remember().
Give external responses an explicit TTL.
Use meaningful cache keys.
Include parameters in cache keys when the response varies by parameter.
Keep TheSportsDB caching inside TheSportsDbService.
Do not put external API caching inside controllers.
Include user identifiers in user-specific cache keys.
Invalidate local cached data when its source data changes.
Use Observers for model-lifecycle cache invalidation when appropriate.
Keep PostgreSQL as the source of truth for persistent data.
Prefer simple caching over unnecessary advanced mechanisms.
Do not introduce Redis or distributed locking without a real requirement.
Keep caching behavior predictable, testable, and easy to understand.