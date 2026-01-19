# Authentication Setup - Complete Guide

This document provides instructions for setting up and testing the Vue + Laravel authentication system using Sanctum.

## Backend Setup

### 1. Environment Configuration
Ensure your `.env` file contains:
```
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bookkeeping
DB_USERNAME=root
DB_PASSWORD=
```

### 2. Run Database Migrations
```bash
cd backend
php artisan migrate
```

This will create the `users` table.

### 3. Seed Test Users
```bash
php artisan db:seed
```

This will create two test users:
- Email: `admin@example.com` | Password: `password123`
- Email: `test@example.com` | Password: `password123`

### 4. Start Laravel Development Server
```bash
php artisan serve
```

The backend will run on `http://localhost:8000`

## Frontend Setup

### 1. Install Dependencies
```bash
npm install
```

### 2. Start Vue Development Server
```bash
npm run dev
```

The frontend will run on `http://localhost:5173`

## Testing the Authentication

### 1. Navigate to Login Page
Go to `http://localhost:5173/login`

### 2. Login with Test Credentials
- Email: `admin@example.com`
- Password: `password123`
- Click "Remember me" (optional)
- Click "Sign In"

### 3. Verify Login Success
- You should be redirected to the dashboard
- The auth token should be stored in localStorage (if "Remember me" was checked) or sessionStorage
- You can check this in browser DevTools > Application > Storage

### 4. Test Protected Routes
- All routes except `/login` require authentication
- If you try to access a protected route without auth, you'll be redirected to login
- If your token expires or becomes invalid, you'll be automatically logged out

### 5. Test Logout
- Click the logout button in the navbar
- You should be redirected to the login page
- The token should be cleared from storage

## API Endpoints

All endpoints are prefixed with `/api/`

### Public Endpoints
- `POST /login` - Login with email and password

### Protected Endpoints (require Authorization header with Bearer token)
- `GET /user` - Get current authenticated user
- `POST /logout` - Logout and invalidate token
- `POST /refresh-token` - Get a new token
- `GET /accounts` - Get all accounts
- `GET /transactions` - Get all transactions
- ... (all other resource endpoints)

## How It Works

### Frontend (Vue)
1. User enters credentials on login page
2. `handleLogin()` calls `authStore.login(email, password)`
3. Auth store makes API call to `POST /api/login`
4. Backend validates credentials and returns token + user data
5. Token is stored in localStorage/sessionStorage
6. Auth store sets the Authorization header for all future requests
7. User is redirected to dashboard
8. On app startup, `App.vue` calls `authStore.initAuth()` to restore session from stored token

### Backend (Laravel)
1. Login endpoint validates email and password
2. Creates a Sanctum token for the user
3. Returns token and user data
4. Protected routes are guarded by `auth:sanctum` middleware
5. Each request must include `Authorization: Bearer {token}` header
6. When user logs out, the token is deleted from the database

### Request Flow
```
Vue Component
    ↓
API Service (apiClient)
    ↓
Request Interceptor (adds Authorization header)
    ↓
Laravel Backend
    ↓
Sanctum Middleware (validates token)
    ↓
Controller
    ↓
Response
    ↓
Response Interceptor (handles 401 errors)
    ↓
Vue Component
```

## Key Features

✅ **Secure Token-Based Authentication** - Using Laravel Sanctum
✅ **Remember Me Functionality** - Tokens stored in localStorage when checked
✅ **Auto Logout on Invalid Token** - 401 responses trigger logout
✅ **Protected Routes** - Navigation guard prevents unauthorized access
✅ **Session Persistence** - Auth state restored on page refresh
✅ **Error Handling** - User-friendly error messages
✅ **CORS Configured** - Frontend and backend can communicate
✅ **Token Refresh** - Ability to get new tokens

## Troubleshooting

### "Login failed" Error
- Check if Laravel backend is running on `http://localhost:8000`
- Check browser console for CORS errors
- Verify database migrations have been run
- Verify test users have been seeded

### Token Not Persisting
- Check if localStorage/sessionStorage is enabled in browser
- Verify "Remember me" checkbox state
- Check browser DevTools > Application > Storage

### 401 Unauthorized on Protected Routes
- Token may have been invalidated
- Try logging out and logging in again
- Check if server was restarted (tokens may be invalidated)

### CORS Issues
- Backend CORS config includes necessary origins (see config/cors.php)
- Ensure you're using http://localhost (not 127.0.0.1)
- Check browser console for specific CORS error messages

## Files Modified/Created

### Backend
- `app/Models/User.php` - User model with Sanctum
- `app/Http/Controllers/Api/AuthController.php` - Authentication controller
- `routes/api.php` - API routes with auth middleware
- `config/sanctum.php` - Sanctum configuration
- `database/migrations/2024_01_17_000000_create_users_table.php` - Users table
- `database/seeders/UserSeeder.php` - Test user seeder
- `database/seeders/DatabaseSeeder.php` - Main seeder

### Frontend
- `src/stores/auth.js` - Pinia auth store with real API calls
- `src/views/Login.vue` - Login component (updated error handling)
- `src/App.vue` - Added auth initialization on mount
- `src/router/index.js` - Updated route guards
- `src/config/api.js` - Updated API configuration
- `src/services/api.js` - API client with interceptors (already configured)

## Next Steps

1. Customize user model with additional fields (phone, role, etc.)
2. Implement role-based access control (RBAC)
3. Add email verification
4. Implement password reset functionality
5. Add token refresh logic in response interceptor
6. Set token expiration times if needed
