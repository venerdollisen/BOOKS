# Authentication Architecture

## System Overview

This bookkeeping system uses a modern, secure authentication architecture based on:
- **Frontend**: Vue 3 with Pinia state management
- **Backend**: Laravel with Sanctum API tokens
- **Communication**: RESTful API with Bearer token authentication
- **Storage**: localStorage/sessionStorage for token persistence

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    Vue 3 Frontend                            │
│  (http://localhost:5173)                                    │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  Login.vue                                           │  │
│  │  - Email/Password form                              │  │
│  │  - Calls authStore.login()                          │  │
│  └──────────────────────────────────────────────────────┘  │
│            ↓                                                 │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  Pinia Auth Store (src/stores/auth.js)              │  │
│  │  - state: user, token, loading                      │  │
│  │  - login(email, password)                           │  │
│  │  - logout()                                         │  │
│  │  - refreshToken()                                   │  │
│  │  - Manages localStorage/sessionStorage              │  │
│  └──────────────────────────────────────────────────────┘  │
│            ↓                                                 │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  API Client (src/services/api.js)                   │  │
│  │  - axios instance                                   │  │
│  │  - Request interceptor: adds Authorization header   │  │
│  │  - Response interceptor: handles 401 errors         │  │
│  └──────────────────────────────────────────────────────┘  │
│            ↓                                                 │
└─────────────────────────────────────────────────────────────┘
          HTTP Request with Bearer Token
          ↓
┌─────────────────────────────────────────────────────────────┐
│                  Laravel Backend                            │
│  (http://localhost:8000)                                    │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  POST /api/login ────────────────────────────────────────   │
│       ↓                                                      │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  AuthController                                     │  │
│  │  - login(email, password)                           │  │
│  │  - Validates credentials                            │  │
│  │  - Creates Sanctum token                            │  │
│  │  - Returns token + user                             │  │
│  └──────────────────────────────────────────────────────┘  │
│       ↓                                                      │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  Database                                           │  │
│  │  - users table                                      │  │
│  │  - personal_access_tokens table (Sanctum)           │  │
│  └──────────────────────────────────────────────────────┘  │
│                                                              │
│  Protected Routes (require token)                          │
│  - GET /api/user ─────→ Returns current user              │
│  - POST /api/logout ──→ Invalidates token                 │
│  - GET /api/accounts ─→ Returns accounts (guarded)         │
│  - etc.                                                     │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

## Data Flow

### Login Flow

```
1. User enters credentials in Login.vue
   └─ Email: admin@example.com
   └─ Password: password123
   └─ Remember: true/false

2. handleLogin() validates form

3. authStore.login(email, password, remember) called
   └─ POST /api/login { email, password }

4. Laravel AuthController.login()
   └─ Find user by email
   └─ Hash password comparison
   └─ Create Sanctum token
   └─ Return { token, user }

5. Token stored in storage
   └─ If remember=true: localStorage.setItem('auth_token', token)
   └─ If remember=false: sessionStorage.setItem('auth_token', token)

6. Set Authorization header
   └─ apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`

7. Redirect to dashboard

8. Router guard validates authentication
   └─ Checks authStore.isAuthenticated
   └─ If false, redirects to login
```

### Protected Request Flow

```
1. Component makes API call
   └─ accountsApi.getAll()
   └─ GET /api/accounts

2. Request Interceptor
   └─ Get token from localStorage/sessionStorage
   └─ Add header: Authorization: Bearer {token}

3. Laravel Middleware: auth:sanctum
   └─ Extract token from Authorization header
   └─ Validate token exists in personal_access_tokens
   └─ Check token not expired
   └─ Authenticate user

4. Controller processes request
   └─ Access via $request->user()
   └─ Query using authenticated user's context

5. Response sent back

6. Response Interceptor
   └─ If 401: Token invalid/expired
   └─ Clear storage
   └─ Redirect to login
   └─ Otherwise return response
```

### Logout Flow

```
1. User clicks logout button
   └─ authStore.logout()

2. POST /api/logout with Bearer token
   └─ Laravel authenticates request
   └─ AuthController.logout()

3. Delete token from personal_access_tokens table
   └─ $request->user()->currentAccessToken()->delete()

4. Frontend clears storage
   └─ localStorage.removeItem('auth_token')
   └─ sessionStorage.removeItem('auth_token')
   └─ Delete Authorization header

5. Redirect to login page
   └─ window.location.href = '/login'
```

## State Management (Pinia)

### Auth Store State
```javascript
{
  user: {              // Current authenticated user
    id: 1,
    name: "Admin",
    email: "admin@example.com",
    created_at: "2024-01-17T00:00:00Z"
  },
  token: "1|abc...",   // Sanctum token
  loading: false,      // API call in progress
  error: null,         // Last error message
  isAuthenticated: true  // Computed: !!token
}
```

### Pinia Actions

| Action | Description | Returns |
|--------|-------------|---------|
| `login(email, password, remember)` | Login with credentials | `{ token, user }` |
| `logout()` | Logout and clear storage | void |
| `refreshToken()` | Get new token | `{ token, user }` |
| `initAuth()` | Initialize from stored token | void |
| `clearError()` | Clear error message | void |

## Security Features

### 1. Token-Based Authentication
- Stateless API - no sessions
- Token stored on client (localStorage/sessionStorage)
- Every request includes token in Authorization header

### 2. Password Security
- Passwords hashed with bcrypt
- Hash comparison on login
- Password never transmitted in plain text (over HTTPS in production)

### 3. CORS Protection
- Backend configured to accept requests only from known origins
- Frontend origin (localhost:5173) whitelisted
- Credentials included in requests (`supports_credentials: true`)

### 4. Token Invalidation
- Tokens deleted from database on logout
- Expired tokens handled by response interceptor
- Invalid tokens trigger automatic logout

### 5. HTTP-Only Cookies (Future)
- Currently storing token in client-side storage
- For production, consider HTTP-only cookies with Sanctum CSRF protection

### 6. Route Guards
- Protected routes require authentication
- Router guard prevents unauthorized access
- Automatic redirect to login if unauthenticated

## API Endpoints

### Authentication Endpoints

#### Login
```http
POST /api/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password123"
}

Response (200):
{
  "message": "Login successful",
  "token": "1|abc123def456",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "created_at": "2024-01-17T00:00:00Z",
    "updated_at": "2024-01-17T00:00:00Z"
  }
}

Response (422):
{
  "message": "The provided credentials are invalid.",
  "errors": {
    "email": ["The provided credentials are invalid."]
  }
}
```

#### Get Current User
```http
GET /api/user
Authorization: Bearer {token}

Response (200):
{
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    ...
  }
}

Response (401):
{
  "message": "Unauthenticated."
}
```

#### Logout
```http
POST /api/logout
Authorization: Bearer {token}

Response (200):
{
  "message": "Logout successful"
}
```

#### Refresh Token
```http
POST /api/refresh-token
Authorization: Bearer {token}

Response (200):
{
  "message": "Token refreshed",
  "token": "2|xyz789abc123",
  "user": { ... }
}
```

## Error Handling

### Common Errors

| Status | Cause | Frontend Action |
|--------|-------|-----------------|
| 401 | Invalid/expired token | Clear storage, redirect to login |
| 422 | Validation error | Show field errors to user |
| 500 | Server error | Show generic error message |

### Error Flow

```javascript
// In axios response interceptor
if (error.response?.status === 401) {
  // Token is invalid
  localStorage.removeItem('auth_token')
  sessionStorage.removeItem('auth_token')
  window.location.href = '/login'
}

// In auth store login method
try {
  const response = await authApi.login(...)
} catch (err) {
  if (err.response?.status === 422) {
    // Validation error - display to user
    error.value = err.response?.data?.message
  } else {
    // Other error
    error.value = err.message || 'Login failed'
  }
}
```

## File Structure

```
src/
├── stores/
│   └── auth.js              # Pinia authentication store
├── services/
│   └── api.js               # API client with interceptors
├── config/
│   └── api.js               # API configuration
├── views/
│   └── Login.vue            # Login page
├── components/
│   └── Layout/
│       └── Navbar.vue       # User profile + logout
└── router/
    └── index.js             # Route guards

backend/
├── app/
│   ├── Models/
│   │   └── User.php         # User model with Sanctum
│   └── Http/
│       └── Controllers/
│           └── Api/
│               └── AuthController.php
├── routes/
│   └── api.php              # API routes
├── config/
│   ├── cors.php             # CORS configuration
│   └── sanctum.php          # Sanctum configuration
└── database/
    ├── migrations/
    │   └── 2024_01_17_000000_create_users_table.php
    └── seeders/
        ├── UserSeeder.php
        └── DatabaseSeeder.php
```

## Best Practices Implemented

✅ **Separation of Concerns** - Auth logic in store, API in services
✅ **Request Interceptors** - Automatically add token to requests
✅ **Response Interceptors** - Handle auth errors centrally
✅ **Error Handling** - Graceful error messages
✅ **Route Guards** - Prevent unauthorized access
✅ **Token Persistence** - Remember me functionality
✅ **Password Hashing** - bcrypt via Laravel
✅ **CORS Configuration** - Whitelist specific origins
✅ **Session Restoration** - Auto-login on page refresh
✅ **Logout on Invalid Token** - Auto-logout on 401

## Testing the System

### Manual Testing Steps

1. **Test Login**
   ```bash
   # Terminal 1: Start backend
   cd backend && php artisan serve
   
   # Terminal 2: Start frontend
   npm run dev
   
   # Browser: http://localhost:5173/login
   # Use: admin@example.com / password123
   ```

2. **Verify Token Storage**
   ```javascript
   // Browser console
   localStorage.getItem('auth_token')  // If remember me
   sessionStorage.getItem('auth_token') // If remember me not checked
   ```

3. **Test Protected Route Access**
   ```javascript
   // Should work
   await api.accountsApi.getAll()  // Requires auth
   
   // Check Authorization header
   // Browser DevTools → Network → Any API call → Headers
   // Should see: Authorization: Bearer {token}
   ```

4. **Test Logout**
   ```javascript
   // Should clear storage and redirect
   authStore.logout()
   ```

5. **Test Token Expiration**
   ```javascript
   // Manually invalidate token in database
   // Make API call → Should get 401
   // Should auto-logout and redirect to login
   ```

## Production Considerations

1. **HTTPS Only** - Use HTTPS in production
2. **Token Expiration** - Set token lifetime in Sanctum config
3. **HTTP-Only Cookies** - Consider moving token to HTTP-only cookie
4. **CSRF Protection** - Enable Sanctum CSRF protection
5. **Rate Limiting** - Add rate limiting to login endpoint
6. **Email Verification** - Verify user email before access
7. **Password Reset** - Implement password reset flow
8. **2FA** - Consider adding two-factor authentication
9. **Activity Logging** - Log authentication events
10. **Token Refresh** - Implement automatic token refresh
