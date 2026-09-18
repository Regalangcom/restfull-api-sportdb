
# Laravel REST API Architecture Best Practices

## Follow Laravel Conventions

Follow Laravel's established conventions and framework patterns.

Prefer Laravel's built-in features over custom implementations when they
already solve the problem.

Use conventional locations and responsibilities for:

- Routes
- Controllers
- Form Requests
- Services
- Eloquent Models
- Observers
- API Resources
- Migrations
- Configuration

Do not introduce custom architectural patterns unless they provide a clear
benefit for the application.

---

## Apply Separation of Concerns

Each component should have a focused responsibility.

Use the following separation:

- Routes define API endpoints and middleware.
- Controllers handle HTTP concerns and coordinate application operations.
- Form Requests handle request validation and authorization when needed.
- Services handle substantial application logic and external API integration.
- Eloquent Models represent local database entities and relationships.
- Observers handle model lifecycle side effects.
- API Resources handle API response representation when transformation is needed.
- Migrations define and maintain database structure.

Do not put unrelated responsibilities into a single class.

For example, a controller should not contain:

- External API implementation
- Complex caching logic
- Large database orchestration
- Model lifecycle side effects
- Repeated validation logic

Keep each responsibility in the layer where it belongs.

---

## Keep Controllers Focused

Controllers should coordinate HTTP requests rather than contain substantial
business logic.

A controller should generally:

1. Receive the request.
2. Validate or receive validated input.
3. Call the appropriate service or model operation.
4. Return an HTTP response.

Example:

```php
public function index(TheSportsDbService $service)
{
    $sports = $service->getSports();

    return response()->json([
        'success' => true,
        'data' => $sports,
    ]);
}
