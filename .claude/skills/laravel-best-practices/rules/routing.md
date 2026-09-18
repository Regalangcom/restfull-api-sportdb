# Laravel REST API Routing Best Practices

## Use API Routes

This project is a RESTful API backend.

Define API endpoints in:

```php
routes/api.php

Do not create Blade, HTML, or web-oriented routes unless explicitly required.

Example:

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FavoriteTeamController;
use App\Http\Controllers\SportController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/sports', [SportController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('favorites', FavoriteTeamController::class)
        ->only(['index', 'store', 'destroy']);
});

Keep authentication and user-specific routes protected with auth:sanctum.

Use apiResource() for RESTful Resources

Use Route::apiResource() when an endpoint represents standard CRUD operations.

Example:

Route::apiResource('favorites', FavoriteTeamController::class)
    ->only(['index', 'store', 'destroy']);

apiResource() is appropriate for API resources because it excludes the HTML-oriented create and edit routes.

Do not use apiResource() simply because it is available.

Use explicit routes when the endpoint does not represent a standard resource operation.

Use Explicit Routes for Non-CRUD Operations

Use explicit routes for operations that do not naturally map to resource controller actions.

Examples:

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Authentication operations such as register, login, and logout do not need to be forced into a resource controller.

Keep endpoint naming predictable and REST-oriented.

Organize Controllers Around Resources

Prefer focused controllers that represent a single resource or application boundary.

For this project, examples include:

AuthController
SportController
FavoriteTeamController

A controller should not accumulate unrelated responsibilities.

Example:

class FavoriteTeamController extends Controller
{
    public function index()
    {
        // Return authenticated user's favorites.
    }

    public function store()
    {
        // Create a favorite.
    }

    public function destroy(FavoriteTeam $favorite)
    {
        // Delete a favorite.
    }
}

Use standard resource actions when they fit the endpoint:

index
show
store
update
destroy

Do not create unnecessary custom controller methods when an existing resource action already represents the operation.

Keep Controllers Focused on HTTP Concerns

Controllers should coordinate:

HTTP request input
authentication
authorization
validation
application/service operations
HTTP response

Controllers should not contain substantial external API communication or caching logic.

For example, do not put TheSportsDB HTTP requests directly inside SportController.

Prefer:

Request
   ↓
Controller
   ↓
TheSportsDbService
   ↓
Cache
   ↓
TheSportsDB

Example:

public function index(TheSportsDbService $service)
{
    $sports = $service->getSports();

    return response()->json([
        'success' => true,
        'data' => $sports,
    ]);
}

The service owns the external API communication and caching.

Use Implicit Route Model Binding

Use implicit route model binding when Laravel's default model resolution is appropriate.

Example:

Route::delete('/favorites/{favorite}', [
    FavoriteTeamController::class,
    'destroy',
]);

Controller:

public function destroy(FavoriteTeam $favorite)
{
    // ...
}

Laravel resolves the FavoriteTeam model automatically.

Prefer this over:

public function destroy(int $id)
{
    $favorite = FavoriteTeam::findOrFail($id);
}

when default route model binding is sufficient.

Scope Nested Bindings When Appropriate

Use scoped bindings when a nested resource must belong to its parent.

Example:

Route::get('/users/{user}/favorites/{favorite}', function (
    User $user,
    FavoriteTeam $favorite
) {
    // The favorite belongs to the user.
})->scopeBindings();

Scoped bindings constrain model resolution.

They do not replace authorization.

For user-owned resources, prefer routes and queries that naturally scope data to the authenticated user.

Example:

$user->favoriteTeams()
    ->whereKey($favorite->id)
    ->firstOrFail();

Use a Policy when authorization rules become more complex.

Protect Private Routes with auth:sanctum

Routes that access or modify authenticated-user data must use Sanctum authentication middleware.

Example:

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('favorites', FavoriteTeamController::class)
        ->only(['index', 'store', 'destroy']);
});

Public endpoints such as register, login, and public TheSportsDB data endpoints do not need this middleware unless the application requirements explicitly require authentication.

Prefer Controller Routes Over Route Closures

Use controller classes for application endpoints.

Prefer:

Route::get('/sports', [SportController::class, 'index']);

over putting application logic directly inside:

Route::get('/sports', function () {
    // Application logic...
});

Route closures may be acceptable for extremely small infrastructure or diagnostic endpoints, but normal API functionality should live in controllers.

Use Middleware at the Appropriate Boundary

Use route middleware for cross-cutting HTTP concerns such as authentication and rate limiting.

Example:

Route::middleware('auth:sanctum')->group(function () {
    // Protected API endpoints.
});

Avoid putting authentication checks manually inside every controller method when middleware can enforce the boundary.

Use Rate Limiting When the Endpoint Requires It

Rate limiting can protect public or sensitive endpoints such as authentication or external API-backed endpoints.

Use Laravel's named rate limiter when the project requires a specific policy.

Example:

Route::middleware('throttle:api')->group(function () {
    // API routes.
});

Do not introduce custom rate-limit policies unless the application requirement calls for them.

Rate limiting is separate from caching.

Caching reduces repeated external API requests.

Rate limiting controls how frequently clients can call the API.

Keep Route Definitions Simple

Routes should primarily define:

HTTP method
URI
controller action
middleware
route names when useful
resource behavior

Avoid placing business logic inside route definitions.

Example:

Route::get('/sports', [SportController::class, 'index']);

Business logic belongs in the appropriate controller/service/model layer.

Use Query Parameters for Simple Filtering

Use query parameters when an endpoint represents the same resource with different filtering or sorting options.

Example:

GET /api/teams?sport=football

Controller:

public function index(Request $request)
{
    $sport = $request->string('sport');

    // ...
}

Do not create separate routes for every simple filter.

Use Route Parameters for Resource Identity

Use route parameters when identifying a specific resource.

Example:

GET /api/favorites/{favorite}
DELETE /api/favorites/{favorite}

Use query parameters for filtering:

GET /api/teams?sport=football

Keep resource identity and filtering semantics distinct.

Keep External API Endpoints Separate From Local Resources

TheSportsDB is an external data source.

Routes that expose TheSportsDB data should delegate to the external API service.

Example:

GET /api/sports
        ↓
SportController
        ↓
TheSportsDbService
        ↓
Cache::remember()
        ↓
TheSportsDB

Do not make routes directly call the external HTTP client.

Avoid Unnecessary Resource Controllers

Not every endpoint requires apiResource().

For example:

Route::post('/login', [AuthController::class, 'login']);

is clearer than attempting to represent authentication as a CRUD resource.

Use the simplest route structure that accurately represents the API contract.

Recommended Route Structure

For this technical test:

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/sports', [SportController::class, 'index']);
Route::get('/teams', [TeamController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('favorites', FavoriteTeamController::class)
        ->only(['index', 'store', 'destroy']);
});

The exact endpoint names may change according to the API contract.

General Rule

Use Laravel's routing conventions where they improve clarity.

For this REST API:

Define API endpoints in routes/api.php.
Use apiResource() for genuine REST resources.
Use explicit routes for authentication and non-CRUD operations.
Keep controllers focused on HTTP concerns.
Use implicit route model binding where appropriate.
Protect authenticated resources with auth:sanctum.
Use scoped bindings or authorization when resource ownership requires it.
Keep TheSportsDB communication inside TheSportsDbService.
Keep caching inside the external API service.
Keep route definitions free of business logic.
Avoid unnecessary controllers, services, or abstractions.

### Perubahan paling penting dari versi lama

Versi lama punya contoh:

```php
public function show(Post $post): View

dan:

return redirect()->route(...)

Itu web application oriented, sedangkan project lu API backend.

Sekarang fokusnya:

routes/api.php
      ↓
Controller
      ↓
Service / Eloquent
      ↓
response()->json()

Dan untuk project lu, pola route yang paling masuk akal adalah:

PUBLIC
POST   /api/register
POST   /api/login
GET    /api/sports
GET    /api/teams

PROTECTED
POST   /api/logout
GET    /api/favorites
POST   /api/favorites
DELETE /api/favorites/{favorite}