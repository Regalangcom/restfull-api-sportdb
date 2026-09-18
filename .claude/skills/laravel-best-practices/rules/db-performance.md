# Database Performance Best Practices

## Avoid N+1 Queries

When accessing an Eloquent relationship for multiple models, use eager
loading with `with()` to avoid unnecessary queries.

Example:

```php
$users = User::with('favoriteTeams')->get();

Then:

foreach ($users as $user) {
    foreach ($user->favoriteTeams as $team) {
        // ...
    }
}

Avoid repeatedly loading the relationship inside a loop:

$users = User::all();

foreach ($users as $user) {
    $user->favoriteTeams;
}

Use eager loading when the relationship will actually be accessed.

Do not eager load relationships that are not needed by the endpoint.

Use Eager Loading for Required API Relationships

If an API endpoint returns a resource together with its related data, load
the relationship as part of the query.

Example:

$users = User::with('favoriteTeams')->get();

For nested relationships:

$users = User::with([
    'favoriteTeams.team',
])->get();

Only load relationships required by the API response.

Prevent Lazy Loading in Development

Consider enabling Laravel's lazy-loading prevention during development to
detect accidental N+1 queries.

Example:

use Illuminate\Database\Eloquent\Model;

public function boot(): void
{
    Model::preventLazyLoading(! app()->isProduction());
}

This is a development aid and should not be enabled blindly if existing
application behavior intentionally relies on lazy loading.

Use it when it improves detection of inefficient relationship access.

Select Only Needed Columns When Appropriate

Select only the columns required by the operation when this provides a
meaningful performance or memory benefit.

Example:

FavoriteTeam::query()
    ->select([
        'id',
        'user_id',
        'team_id',
        'created_at',
    ])
    ->where('user_id', $user->id)
    ->get();

Do not aggressively limit columns when the additional complexity provides
no meaningful benefit.

When using Eloquent relationships, make sure all required primary keys and
foreign keys are selected.

For example:

User::select([
    'id',
    'name',
])->with('favoriteTeams')->get();

The id column must remain available so Eloquent can correctly match the
relationship.

Add Indexes for Actual Query Patterns

Add indexes based on queries that are frequently executed or performance
sensitive.

For this project, user-specific favorite-team queries are likely to filter
by user_id.

Example:

$table->foreignId('user_id')
    ->constrained()
    ->index();

However, check whether the database or migration already creates an index
for the foreign key before adding a duplicate index.

Do not add indexes to every column automatically.

Indexes have storage and write-maintenance costs.

Use Composite Indexes When the Query Pattern Requires Them

If the application frequently queries multiple columns together, consider a
composite index.

Example query:

FavoriteTeam::where('user_id', $user->id)
    ->where('team_id', $teamId)
    ->first();

A suitable database constraint/index can be:

$table->unique([
    'user_id',
    'team_id',
]);

This provides both:

Database-level protection against duplicate favorites.
An index useful for lookups using user_id and team_id.

Use a unique constraint when the business rule requires that a user cannot
favorite the same team more than once.

Do not add composite indexes without considering the actual query patterns.

Use Database Constraints for Data Integrity

Performance optimization should not replace database integrity.

Use database constraints for rules that must always hold.

Examples:

$table->foreignId('user_id')
    ->constrained()
    ->cascadeOnDelete();

$table->unique([
    'user_id',
    'team_id',
]);

Database constraints protect data even when records are created outside a
specific controller or service.

Use Eloquent Relationships Instead of Repeated Queries

Define relationships on models and reuse them.

Example:

class User extends Authenticatable
{
    public function favoriteTeams()
    {
        return $this->hasMany(FavoriteTeam::class);
    }
}

Then use:

$user->favoriteTeams;

or:

$user->favoriteTeams()->get();

Avoid duplicating equivalent relationship queries throughout controllers.

Query Only the Data Required by the Endpoint

API endpoints should retrieve only the data required for their response.

Example:

FavoriteTeam::query()
    ->where('user_id', $user->id)
    ->latest()
    ->get();

Avoid loading unrelated tables, relationships, or columns when they are not
needed.

Use Pagination for Potentially Large API Collections

If an endpoint can return a large number of database records, use
pagination rather than returning an unbounded collection.

Example:

FavoriteTeam::query()
    ->where('user_id', $user->id)
    ->latest()
    ->paginate(20);

For a small technical-test dataset, pagination is not mandatory unless the
API contract requires it.

Do not add pagination solely for the sake of optimization when the endpoint
has a clearly small and bounded dataset.

Process Large Data Sets Incrementally

Use chunk(), chunkById(), lazy(), or lazyById() when processing a
large number of records would otherwise exceed the application's practical
memory budget.

Example:

User::where('active', true)
    ->chunkById(200, function ($users) {
        foreach ($users as $user) {
            // Process user.
        }
    });

These techniques are not necessary for normal CRUD operations involving
small datasets.

Do not use chunking merely because it is available.

Use withCount() When Only Relationship Counts Are Needed

Use withCount() when the endpoint only needs the number of related records
and does not need to load the related models.

Example:

User::withCount('favoriteTeams')->get();

Then:

$user->favorite_teams_count;

Do not load the complete relationship just to calculate a count.

This is optional for the current technical test and should only be used if
an endpoint actually requires relationship counts.

Avoid Unnecessary Database Queries

Do not execute additional queries when the required information is already
available.

For example, avoid:

$user = auth()->user();

$favoriteTeams = FavoriteTeam::where(
    'user_id',
    $user->id
)->get();

if the existing Eloquent relationship is appropriate:

$favoriteTeams = $user->favoriteTeams()->get();

The goal is not to eliminate every query, but to keep database access
intentional and easy to understand.

Avoid Queries in Presentation Logic

This project is a REST API and does not require Blade views.

Do not place database queries inside:

API response formatting logic.
Blade templates.
Serialization callbacks.
Loops that repeatedly execute queries.

Prepare the required data before constructing the API response.

Example:

$favoriteTeams = $user->favoriteTeams()->get();

return response()->json([
    'success' => true,
    'data' => $favoriteTeams,
]);
Prefer Simple Queries Over Premature Optimization

Do not replace a simple readable query with a complicated query unless there
is a demonstrated performance reason.

For example:

FavoriteTeam::where('user_id', $user->id)->get();

is preferable to introducing complex subqueries when the simple query
satisfies the requirement.

Optimize based on:

Actual query patterns.
Dataset size.
Measured performance.
Database query plans.
Do Not Over-Optimize a Small Dataset

This technical test is a small REST API application.

Do not introduce advanced database optimization without a real requirement.

Avoid adding:

Complex subqueries.
Raw SQL without a reason.
Unnecessary composite indexes.
Database partitioning.
Query caching for every database operation.
Complex query builders.
Chunking for small datasets.

Keep the database layer simple and maintainable.

Database Performance and Caching Are Separate Concerns

Do not use database caching as a substitute for the required TheSportsDB
response caching.

For this project:

TheSportsDB
     ↓
Cache
     ↓
Laravel Service

is responsible for reducing repeated external API requests.

While:

Laravel
     ↓
Eloquent
     ↓
PostgreSQL

is responsible for persistent local application data.

Use caching for external API responses where required.

Use database indexes and efficient queries for local PostgreSQL data.

Recommended Database Performance for This Project

Prioritize the following:

Avoid N+1 relationship queries.
Use eager loading when relationships are required.
Add appropriate indexes.
Use unique constraints for data integrity.
Query only the required data.
Use Eloquent relationships.
Use pagination when collections can become large.
Process large datasets incrementally when actually necessary.
Keep PostgreSQL queries simple and readable.
Avoid premature optimization.
General Rule

Optimize database access based on actual application requirements.

For this project, focus on:

User
  ↓
FavoriteTeam
  ↓
PostgreSQL

Use appropriate:

Eloquent relationships.
Eager loading.
Indexes.
Foreign keys.
Unique constraints.
Query filtering.

Do not introduce advanced database optimization unless the application
actually needs it.


### Jadi status `db-performance.md` lu

**🔴 Yang gue anggap penting:**
- N+1 / eager loading
- indexes
- foreign key
- unique `(user_id, team_id)`
- query hanya data yang diperlukan

**🟡 Optional:**
- `preventLazyLoading`
- pagination
- `withCount`
- chunking

**❌ Buang dari rule aktif:**
- Blade query rules
- optimasi query kompleks
- subquery advanced
- optimasi untuk dataset besar yang belum ada
- optimasi database berlebihan

Dan satu yang penting buat schema favorite lu:

```php
$table->unique(['user_id', 'team_id']);