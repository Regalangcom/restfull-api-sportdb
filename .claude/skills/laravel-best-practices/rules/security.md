# Security Best Practices

## Protect Mass Assignment

Define `$fillable` for models that receive request-derived data.

Example:

```php
class FavoriteTeam extends Model
{
    protected $fillable = [
        'team_id',
    ];
}

For User:

class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}

Do not use:

protected $guarded = [];

for models that receive untrusted request data unless there is a deliberate reason and the assigned data is fully controlled.

Mass-assignment protection does not replace:

validation
authentication
authorization

---

## Validate All Request Data

Validate data coming from API requests before using it in application operations.

Use Form Requests when validation is substantial or reused.

Example:

```php
public function rules(): array
{
    return [
        'team_id' => ['required', 'string'],
    ];
}

Do not assume that authenticated requests are automatically trusted.

Authentication identifies the user.

Validation verifies the request data.

Authorization determines whether the user is allowed to perform the operation.

These are separate security concerns.

Protect User-Owned Resources

Favorite teams belong to authenticated users.

Never allow a user to access or modify another user's favorite simply because they know the favorite's ID.

Bad:

$favorite = FavoriteTeam::findOrFail($id);
$favorite->delete();

If the resource is user-owned, ensure ownership is enforced.

Example:

$favorite = $request->user()
    ->favoriteTeams()
    ->whereKey($id)
    ->firstOrFail();

$favorite->delete();

Alternatively, use a Policy when authorization logic becomes more complex.

Authentication with Sanctum

Use Laravel Sanctum for API authentication.

Protected routes should use:

Route::middleware('auth:sanctum')->group(function () {
    // Protected endpoints
});

When authenticating a user:

$token = $user->createToken('api-token')->plainTextToken;

Clients should send the token using:

Authorization: Bearer <token>

Do not expose authentication tokens in API responses except when intentionally issuing the token during authentication.

Revoke Tokens on Logout

Logout should revoke the authenticated token when using personal access tokens.

Example:

$request->user()->currentAccessToken()?->delete();

This invalidates the token used for the current authenticated session.

Do not delete every token unless the application's logout behavior explicitly requires logging the user out from all devices.

Hash User Passwords

Never store plain-text passwords.

Use Laravel's password hashing facilities.

Example:

use Illuminate\Support\Facades\Hash;

$user = User::create([
    'name' => $request->string('name'),
    'email' => $request->string('email'),
    'password' => Hash::make($request->string('password')),
]);

Do not manually implement password hashing.

Do not return the password or password hash in API responses.

Hide Sensitive Model Attributes

Sensitive model attributes should not be exposed through JSON serialization.

For User:

protected $hidden = [
    'password',
    'remember_token',
];

Do not include:

password
password hash
personal access tokens
secrets

in normal API responses.

Authorize Protected Actions

Authentication alone does not establish authorization.

For user-owned resources, verify that the authenticated user is allowed to access the resource.

Use:

relationship-based authorization
Policies
Gates
Form Request authorization

depending on complexity.

For this project, simple ownership can often be enforced through the authenticated user's relationship:

$request->user()
    ->favoriteTeams()
    ->whereKey($favorite->id)
    ->firstOrFail();

Use a Policy when the authorization rules become more complex.

Do not duplicate authorization logic unnecessarily across controllers.

Prevent SQL Injection

Never interpolate untrusted request values directly into SQL.

Bad:

DB::select(
    "SELECT * FROM users WHERE name = '{$request->name}'"
);

Prefer Eloquent:

User::where('name', $request->string('name'))->get();

Or parameter bindings:

DB::select(
    'SELECT * FROM users WHERE name = ?',
    [$request->string('name')->toString()]
);

Use allow-lists when users can influence SQL identifiers such as:

column names
sort directions
table names

Bindings protect values but do not make arbitrary SQL identifiers safe.

Do Not Trust Client-Supplied Ownership

Do not determine ownership from a request field such as:

{
    "user_id": 123
}

when the authenticated user is already available.

Prefer:

$user = $request->user();

$user->favoriteTeams()->create([
    'team_id' => $request->string('team_id'),
]);

The authenticated user should determine ownership.

Do not allow a client to submit another user's user_id to create or modify that user's resources.

Protect API Endpoints with Appropriate Middleware

Use middleware at the route boundary.

Example:

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('favorites', FavoriteTeamController::class)
        ->only(['index', 'store', 'destroy']);
});

Keep public endpoints public only when the API contract requires them to be public.

Rate Limit Sensitive Endpoints

Apply rate limiting to endpoints that are expensive or abuse-prone.

Potential candidates include:

login
registration
external API-backed endpoints
other publicly accessible expensive operations

Example:

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:login');

If a named limiter is required, define it with Laravel's RateLimiter.

Rate limiting is separate from authentication and authorization.

Protect External API Usage

TheSportsDB is an external dependency.

Do not allow arbitrary user input to become an unrestricted external URL.

Use a configured base URL:

$url = config('services.thesportsdb.base_url');

Build requests using known endpoints and validated parameters.

Do not accept a complete external URL from the client and request it directly.

This prevents the application from becoming an unintended proxy to arbitrary destinations.

Use HTTP Timeouts for External Requests

External HTTP requests should have a reasonable timeout.

Example:

Http::timeout(10)
    ->get($url);

Do not allow external API requests to wait indefinitely.

Handle external API failures separately from local application/database failures.

Keep API Credentials and Secrets Out of Source Code

Do not hard-code:

API keys
database passwords
Sanctum secrets
external service credentials
other private configuration

Store environment-specific secrets in .env or an appropriate secret-management system.

Example:

THESPORTSDB_API_KEY=...

Access configuration through Laravel configuration:

config('services.thesportsdb.api_key');

Do not commit populated .env files to source control.

Use Configuration for External Services

Define external service configuration in:

config/services.php

Example:

'thesportsdb' => [
    'base_url' => env('THESPORTSDB_BASE_URL'),
    'api_key' => env('THESPORTSDB_API_KEY'),
],

Application code should use:

config('services.thesportsdb.base_url');

rather than repeatedly reading environment variables directly.

Do Not Expose Internal Errors

API responses should not expose:

database credentials
SQL statements
stack traces
filesystem paths
environment variables
API keys
internal implementation details

In development, detailed errors may be useful for debugging.

Production responses should return the application's consistent error format.

Use Secure API Error Responses

Return errors using the project's consistent API response structure.

Example:

return response()->json([
    'success' => false,
    'message' => 'Unauthorized.',
    'data' => null,
], 401);

Do not expose sensitive exception details to API clients.

Log detailed exceptions server-side when appropriate.

Validate External API Response Assumptions

Do not blindly assume that TheSportsDB always returns the expected structure.

Handle:

HTTP errors
timeouts
malformed responses
missing expected fields
empty results

External API data should be treated as untrusted input.

Example:

$response = Http::timeout(10)->get($url);

$response->throw();

$data = $response->json();

Validate or normalize the data before returning it through the application's API when necessary.

Protect Database Integrity

Use database constraints in addition to application validation.

For favorite teams:

$table->foreignId('user_id')
    ->constrained()
    ->cascadeOnDelete();

$table->string('team_id');

$table->unique(['user_id', 'team_id']);

The unique constraint prevents duplicate favorites even if concurrent requests bypass application-level checks.

Do not rely solely on:

if (! $user->favoriteTeams()->where('team_id', $teamId)->exists()) {
    // create
}

for data integrity.

Avoid Trusting Client-Controlled Sensitive Fields

Do not allow clients to directly control fields such as:

user_id
ownership fields
authorization flags
admin flags
internal status fields
timestamps
system-generated identifiers

unless explicitly required by the API contract.

Set server-controlled values from authenticated context or application logic.

Audit Dependencies

Check dependencies for known security vulnerabilities.

Run:

composer audit

Review reported vulnerabilities and update affected dependencies when appropriate.

Do not ignore dependency security warnings without understanding their impact.

CSRF and API Authentication

This project is an API backend.

Do not add Blade-specific CSRF instructions to API controllers or API route implementations.

For token-authenticated API endpoints using Sanctum personal access tokens, authentication is handled through the API authentication mechanism and auth:sanctum.

Do not disable security middleware simply to make an endpoint work.

If the application later uses Sanctum's cookie-based SPA authentication, follow Laravel's corresponding CSRF requirements for that architecture.

File Upload Security

File upload security is not applicable unless this API introduces file uploads.

If file uploads are added later:

validate content type
validate file size
validate extensions
generate server-controlled filenames
use Laravel's storage APIs
avoid trusting client filenames
store files in an appropriate location

Do not add upload-specific code to this project unless the API contract requires it.

Encryption of Sensitive Attributes

Encrypt sensitive database attributes only when the application actually stores recoverable secrets or sensitive values that require encryption at rest.

Example:

protected function casts(): array
{
    return [
        'api_secret' => 'encrypted',
    ];
}

Do not introduce encrypted attributes unless the project actually stores such sensitive values.

Encryption does not replace authorization.

Security Responsibilities
Authentication

Handled by:

Laravel Sanctum
Authorization

Handled by:

Policies / Gates / ownership checks
Validation

Handled by:

Form Requests / validation rules
Mass Assignment

Handled by:

$fillable
Database Integrity

Handled by:

PostgreSQL constraints
External API Security

Handled by:

configured URLs
timeouts
validated parameters
safe error handling
secret management
API Error Security

Handled by:

consistent error responses
without exposing internal details
General Rule

Security is a layered responsibility.

For this project:

* Authenticate protected API routes with Sanctum.
* Authorize access to user-owned favorites.
* Validate request data.
* Protect models from unsafe mass assignment.
* Never trust client-supplied ownership fields.
* Hash passwords.
* Hide sensitive model attributes.
* Use parameter binding/Eloquent for database queries.
* Rate-limit sensitive or abuse-prone endpoints.
* Keep API credentials and secrets out of source code.
* Protect external TheSportsDB requests.
* Use timeouts for external HTTP requests.
* Do not expose internal exception details.
* Use database constraints for important data integrity.
* Audit Composer dependencies.
* Avoid security mechanisms that are unrelated to the actual API architecture.
