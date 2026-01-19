# Laravel Backend Setup Guide

This document explains how to set up your Laravel backend to work with this Vue.js frontend.

## Authentication Routes

The frontend expects the following authentication endpoints. Update `src/config/api.js` if your routes differ.

### Option 1: Laravel Sanctum (Recommended)

If you're using Laravel Sanctum, add these routes to your `routes/api.php`:

```php
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
```

**LoginController Example:**

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => $user,
            ]);
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
```

### Option 2: Custom Auth Routes

If you prefer custom routes like `/api/auth/login`, update `src/config/api.js`:

```javascript
auth: {
  login: '/auth/login',
  logout: '/auth/logout',
  user: '/auth/user',
  refresh: '/auth/refresh',
}
```

Then in your Laravel `routes/api.php`:

```php
Route::prefix('auth')->group(function () {
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');
});
```

## Expected Response Format

### Login Response

```json
{
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com"
  }
}
```

**Alternative format (if using different field names):**

```json
{
  "access_token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com"
  }
}
```

The frontend will automatically handle both `token` and `access_token` field names.

### User Response

```json
{
  "id": 1,
  "name": "Admin User",
  "email": "admin@example.com",
  "created_at": "2024-01-01T00:00:00.000000Z"
}
```

## CORS Configuration

Make sure your Laravel backend allows requests from the frontend. Update `config/cors.php`:

```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_origins' => ['http://localhost:3000'], // Your Vue dev server
'allowed_headers' => ['*'],
'allowed_methods' => ['*'],
'supports_credentials' => true,
```

## Sanctum Setup

1. Install Sanctum:
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

2. Add Sanctum middleware to `bootstrap/app.php` or `app/Http/Kernel.php`:
```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    // ... other middleware
],
```

3. Update `config/sanctum.php`:
```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost,127.0.0.1')),
```

## Testing

You can test the login endpoint with:

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
```

## Troubleshooting

### Route Not Found (404)

- Check that your routes are in `routes/api.php` (not `routes/web.php`)
- Verify the route prefix matches your API configuration
- Run `php artisan route:list` to see all registered routes

### 401 Unauthorized

- Check that the token is being sent in the Authorization header
- Verify Sanctum middleware is properly configured
- Ensure the token hasn't expired

### CORS Errors

- Verify CORS configuration in `config/cors.php`
- Check that `allowed_origins` includes your frontend URL
- Ensure `supports_credentials` is set to `true` if using cookies
