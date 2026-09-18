# Eloquent Best Practices

## Use Eloquent for Local Application Data

Use Eloquent Models for application data stored in the local database.

For this project, Eloquent is used for local models such as:

- User
- FavoriteTeam

Example:

```php
$user = User::find($id);

$favorites = FavoriteTeam::where('user_id', $user->id)->get();

Prefer Eloquent over raw SQL for normal model-backed application queries.

Use the query builder or raw SQL only when lower-level database behavior is genuinely required.

Define Precise Model Relationships

Define relationships that match the actual database associations and use concrete return types.

Example:

// User.php

use Illuminate\Database\Eloquent\Relations\HasMany;

public function favoriteTeams(): HasMany
{
    return $this->hasMany(FavoriteTeam::class);
}
// FavoriteTeam.php

use Illuminate\Database\Eloquent\Relations\BelongsTo;

public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

If FavoriteTeam stores a relationship to another local model, define the corresponding relationship explicitly.

Do not create relationships for external API data that is not stored locally.

Keep Eloquent Models Focused on Local Data

Models should primarily define:

database relationships
casts
model configuration
reusable query scopes
model-level behavior

Do not place external API communication inside Eloquent models.

Do not put TheSportsDB HTTP requests inside models.

Do not put external API caching inside models.

External API communication and caching belong in the dedicated service layer.

Example:

class TheSportsDbService
{
    public function getTeams(): array
    {
        // HTTP request + caching
    }
}
Use Eloquent Relationships Instead of Repeating Foreign Key Logic

Prefer relationships when accessing related local data.

Instead of repeatedly querying manually:

FavoriteTeam::where('user_id', $user->id)->get();

The relationship can be used when appropriate:

$user->favoriteTeams()->get();

This keeps relationship knowledge inside the model.

Use whereBelongsTo() for Relationship-Aware Queries

When supported by the query pattern, use whereBelongsTo() to express a relationship constraint.

Example:

FavoriteTeam::whereBelongsTo($user)->get();

This is preferable to manually repeating the foreign key when the relationship itself is the important concept.

However, simple explicit foreign-key queries are still acceptable when they are clearer for the endpoint.

Do not use whereBelongsTo() merely for the sake of using it.

Use Local Scopes for Reusable Query Constraints

Use local scopes when the same query constraint is genuinely reused.

Example:

#[Scope]
protected function active(Builder $query): Builder
{
    return $query->where('is_active', true);
}

Usage:

Team::active()->get();

Do not create scopes for one-off queries.

Avoid adding abstractions that make simple queries harder to understand.

Avoid Global Scopes Unless the Constraint Is Universal

Global scopes modify every query against a model.

Use them sparingly and only for constraints that should apply universally.

For example, soft deletes are appropriate because Laravel provides explicit mechanisms to include deleted records when required.

Do not use global scopes for normal API filtering such as:

user's favorite teams
team search
sport filtering
request-specific conditions

Prefer explicit query constraints or local scopes.

Define Attribute Casts When Needed

Use the model's casts() method for attributes that require automatic type conversion.

Example:

protected function casts(): array
{
    return [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];
}

Only define casts that correspond to actual database fields used by the application.

Do not add casts speculatively.

Cast Custom Date and Time Attributes When Needed

Laravel automatically handles the conventional:

created_at
updated_at

For custom datetime columns, define an appropriate cast.

Example:

protected function casts(): array
{
    return [
        'synced_at' => 'datetime',
    ];
}

This allows application code to work with the value as a Carbon instance.

Use Eloquent Model Events Carefully

Eloquent provides lifecycle events such as:

creating
created
updating
updated
deleting
deleted

When model lifecycle side effects are required, use an Observer rather than putting unrelated side-effect logic directly into the model.

Example use case:

FavoriteTeam created
        ↓
FavoriteTeamObserver
        ↓
Invalidate local favorites cache

Observers should be used for model lifecycle side effects such as:

cache invalidation
audit logging
synchronization triggered by model changes

Do not use an Observer for TheSportsDB API caching.

TheSportsDB caching belongs in:

TheSportsDbService
Protect User-Owned Data Through Relationships and Authorization

Favorite teams are user-owned data.

Queries for favorites must be scoped to the authenticated user.

Example:

$user = $request->user();

$favorites = $user->favoriteTeams()->get();

Do not retrieve a favorite belonging to another user and rely only on controller logic afterward.

When using route model binding, ensure authorization prevents users from accessing another user's favorite.

Example:

$user->favoriteTeams()
    ->whereKey($favorite->id)
    ->firstOrFail();

Or use an appropriate Policy when authorization rules become more complex.

Use Database Constraints for Data Integrity

Eloquent validation is not a replacement for database constraints.

For user favorites, the database should prevent duplicate relationships.

Example migration:

$table->foreignId('user_id')
    ->constrained()
    ->cascadeOnDelete();

$table->string('team_id');

$table->unique(['user_id', 'team_id']);

The unique constraint ensures that the same user cannot favorite the same team more than once, even if concurrent requests occur.

Use Eager Loading When Relationships Are Required

Avoid N+1 queries when returning related local data.

Example:

$users = User::with('favoriteTeams')->get();

Only eager load relationships that are actually needed by the API response.

Do not eager load relationships automatically everywhere.

Select Only Required Columns When It Provides a Real Benefit

When an endpoint only needs specific columns, selecting those columns can reduce unnecessary database work.

Example:

FavoriteTeam::query()
    ->select(['id', 'user_id', 'team_id'])
    ->whereBelongsTo($user)
    ->get();

When using partial column selection with relationships, make sure required primary and foreign key columns are still selected.

Do not sacrifice readability for negligible optimization.

Prefer Simple Eloquent Queries

Keep queries readable and proportional to the application's requirements.

Example:

$favorites = $user->favoriteTeams()->get();

is preferable to introducing complex subqueries when the simple relationship query is sufficient.

Do not introduce:

complex subqueries
raw SQL
unnecessary joins
advanced query abstractions

without a real requirement.

Keep Database Queries Out of External API Services

Keep responsibilities separate.

Local database:

Controller
    ↓
Eloquent Model
    ↓
PostgreSQL

External API:

Controller
    ↓
TheSportsDbService
    ↓
Cache
    ↓
HTTP Client
    ↓
TheSportsDB

Do not mix PostgreSQL queries and TheSportsDB HTTP communication inside the same model.

Do Not Use Eloquent for External API Data

TheSportsDB data is external API data unless the application explicitly persists it locally.

Do not create an Eloquent Model simply to represent temporary TheSportsDB responses.

Use a service for external API communication:

class TheSportsDbService
{
    public function getSports(): array
    {
        // Cache + HTTP Client
    }
}

If external data is intentionally synchronized into PostgreSQL, then Eloquent can be used for the persisted local representation.

Use Explicit Table Names in Migrations

Migrations should reference database tables directly.

Example:

Schema::create('favorite_teams', function (Blueprint $table) {
    $table->id();

    $table->foreignId('user_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->string('team_id');

    $table->unique(['user_id', 'team_id']);

    $table->timestamps();
});

Do not depend on application Eloquent Models inside migrations.

Migrations represent historical database structure and should remain stable even if models change later.

Avoid Premature Eloquent Abstractions

Do not create:

repositories
generic base models
unnecessary scopes
unnecessary interfaces
query objects

unless the project actually requires them.

For this project, normal Eloquent Models and relationships are sufficient for local database operations.

Recommended Eloquent Responsibilities
User Model

Responsible for:

user configuration
authentication relationship
favorite team relationship

Example:

public function favoriteTeams(): HasMany
{
    return $this->hasMany(FavoriteTeam::class);
}
FavoriteTeam Model

Responsible for:

favorite team database representation
relationship to User
casts if required
model-level behavior if required

Example:

public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
TheSportsDbService

Responsible for:

TheSportsDB HTTP requests
external API response handling
caching external API responses
external API-related transformations when necessary
FavoriteTeamObserver

Responsible for:

model lifecycle side effects
local favorite cache invalidation if such a cache exists
General Rule

Use Eloquent as the primary ORM for local application data.

Keep:

Models responsible for local data and relationships.
Controllers responsible for HTTP concerns.
Form Requests responsible for validation.
Services responsible for substantial application logic and external API integration.
Observers responsible for model lifecycle side effects.
PostgreSQL responsible for persistent data integrity.
Cache responsible for temporary cached data.

Prefer simple, readable Eloquent code over unnecessary abstraction or optimization.


### Kenapa versi ini lebih cocok buat test lu?

Karena sekarang `eloquent.md` nyambung langsung dengan architecture yang kita bikin:

```text
                    API Request
                         │
                         ▼
                    Controller
                    /         \
                   /           \
                  ▼             ▼
          Form Request      Service
                               │
                               ▼
                         TheSportsDB
                               │
                         Cache::remember()

Sedangkan data lokal:

Controller
    │
    ▼
User / FavoriteTeam
    │
    ▼
Eloquent
    │
    ▼
PostgreSQL

Dan kalau ada cache untuk favorite lokal:

FavoriteTeam
     │
  created/deleted
     │
     ▼
Observer
     │
     ▼
Cache::forget()