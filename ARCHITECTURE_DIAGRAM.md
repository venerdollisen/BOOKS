# System Architecture & API Flow Diagram

## 🏗️ System Architecture

```
┌──────────────────────────────────────────────────────────────────┐
│                         Internet (HTTPS in Production)            │
└──────────────────────────────────────────────────────────────────┘
                               ▲
                               │
                    ┌──────────┴──────────┐
                    │                     │
        ┌───────────▼──────────┐  ┌──────▼────────────────┐
        │   Vue.js Frontend    │  │   Laravel Backend     │
        │  (http://localhost   │  │ (http://localhost:8000)
        │         :5173)       │  │                       │
        │                      │  │                       │
        │  • Login View        │  │  • Auth Controller    │
        │  • Dashboard View    │  │  • User Model         │
        │  • Transaction Form  │  │  • API Routes         │
        │  • Reports View      │  │  • Database (SQLite)  │
        │                      │  │  • CORS Config        │
        │  ┌──────────────┐    │  │                       │
        │  │ Pinia Store  │◄──┼──┤  ┌──────────────────┐  │
        │  │ (Auth State) │   │  │  │ Sanctum Tokens   │  │
        │  └──────────────┘    │  │  │ (DB Table)       │  │
        │                      │  │  └──────────────────┘  │
        │  ┌──────────────┐    │  │  ┌──────────────────┐  │
        │  │ Axios Client │◄──┼──┤  │ Validation       │  │
        │  │ + Interceptor│   │  │  │ Rules            │  │
        │  └──────────────┘    │  │  └──────────────────┘  │
        │                      │  │                       │
        └──────────────────────┘  └───────────────────────┘
```

---

## 🔐 Authentication Flow Diagram

### 1. Login Flow

```
User Interface (Vue)              API Client              Backend (Laravel)
─────────────────                 ──────────             ─────────────────

   User fills form
       │
       ▼
   Validate locally
   (email, password)
       │
       ▼
   POST /api/auth/login
   {email, password}  ────────────────────────────────►  Validate input
                                                          Check if email exists
                                                          Hash password
                                                          Compare hashes
                                                          │
                                                          ├─ Match? YES
                                                          │    └─ Create Token
                                                          │        Store in DB
                                                          │        Return token
                                                          │
                                                          └─ Match? NO
                                                             Return 401 error
       │
       ◄────────────────────────────────────────────────  {token, user}
       │                                                    or {error}
       ▼
   Save token to
   localStorage
       │
       ▼
   Update Pinia store
   (token & user)
       │
       ▼
   Redirect to
   Dashboard
```

### 2. Protected Request Flow

```
Subsequent API Requests
───────────────────────

   User makes
   API request
       │
       ▼
   Axios Interceptor
       │
       ├─ Add header:
       │  "Authorization: Bearer {token}"
       │
       ▼
   POST/GET/PUT /api/protected ──────────────────────►  Check token
                                                        │
                                                        ├─ Valid?
                                                        │   └─ Process request
                                                        │       Return data
                                                        │
                                                        └─ Invalid/Expired?
                                                           Return 401
       │
       ◄─────────────────────────────────────────────── {data}
       │                                                 or {error: 401}
       ▼
   Status 401?
   │
   ├─ YES: Delete token
   │       Clear localStorage
   │       Logout
   │       Redirect to /login
   │
   └─ NO: Use response
          Update UI
```

### 3. Logout Flow

```
   User clicks
   Logout button
       │
       ▼
   Pinia action:
   logout()
       │
       ├─ POST /api/auth/logout ─────────────────────►  Delete token
                (with token)                             from DB
       │
       ◄─────────────────────────────────────────────── {success}
       │
       ▼
   Clear localStorage
   Clear Pinia state
   Delete auth header
       │
       ▼
   Redirect to /login
```

---

## 📊 Request/Response Structure

### Login Request
```
POST /api/auth/login
Content-Type: application/json
Accept: application/json

{
  "email": "user@example.com",
  "password": "securepassword"
}
```

### Successful Login Response (200)
```
HTTP/1.1 200 OK
Content-Type: application/json
CORS Headers: origin allowed

{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "1|abcdef123456789...",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com"
    }
  }
}
```

### Failed Login Response (401)
```
HTTP/1.1 401 Unauthorized
Content-Type: application/json

{
  "success": false,
  "message": "Invalid credentials"
}
```

### Protected Route Request
```
GET /api/auth/user
Content-Type: application/json
Accept: application/json
Authorization: Bearer 1|abcdef123456789...
```

### Protected Route Response (200)
```
HTTP/1.1 200 OK
Content-Type: application/json

{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com"
  }
}
```

### Unauthorized Access (401)
```
HTTP/1.1 401 Unauthorized
Content-Type: application/json

{
  "message": "Unauthenticated"
}
```

---

## 🔄 Data Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    Frontend (Vue.js)                         │
│                                                              │
│  ┌────────────────────────────────────────────────────────┐ │
│  │                  Login Component                       │ │
│  │  • form.email                                          │ │
│  │  • form.password                                       │ │
│  │  • errors                                              │ │
│  │  • loading state                                       │ │
│  └────────────┬─────────────────────────────────────────┘ │
│               │ (4) emit login action                      │
│               ▼                                             │
│  ┌────────────────────────────────────────────────────────┐ │
│  │          Pinia Auth Store                              │ │
│  │  State:                                                │ │
│  │  • user: {id, name, email}                            │ │
│  │  • token: "1|abc..."                                  │ │
│  │  • loading: false                                      │ │
│  │  • error: null                                         │ │
│  │                                                        │ │
│  │  Actions:                                              │ │
│  │  • login(email, password)                             │ │
│  │  • logout()                                            │ │
│  │  • register(data)                                      │ │
│  │  • initAuth()                                          │ │
│  └────────────┬─────────────────────────────────────────┘ │
│               │ (5) call authApi.login()                   │
│               ▼                                             │
│  ┌────────────────────────────────────────────────────────┐ │
│  │          Axios API Client                              │ │
│  │  • baseURL: http://localhost:8000/api                 │ │
│  │  • Request Interceptor:                                │ │
│  │    - Add Bearer token to header                        │ │
│  │  • Response Interceptor:                               │ │
│  │    - Handle 401 errors                                 │ │
│  └────────────┬─────────────────────────────────────────┘ │
│               │ (6) HTTP POST request                      │
└───────────────┼──────────────────────────────────────────┘
                │
                │ /api/auth/login
                │ {email, password}
                │
┌───────────────┼──────────────────────────────────────────┐
│               ▼                                           │
│                  Backend (Laravel)                        │
│                                                           │
│  ┌────────────────────────────────────────────────────┐  │
│  │       AuthController                              │  │
│  │                                                    │  │
│  │  login() method:                                  │  │
│  │  1. Validate input (email, password format)       │  │
│  │  2. Find user by email                            │  │
│  │  3. Hash::check password vs stored hash           │  │
│  │  4. Create token: $user->createToken()            │  │
│  │  5. Return token + user data                      │  │
│  └────────────┬───────────────────────────────────┘  │
│               │ (7) HTTP 200 response                  │
└───────────────┼──────────────────────────────────────┘
                │
                │ {token, user}
                │
┌───────────────┼──────────────────────────────────────────┐
│               ▼                                           │
│  (8) Store token in localStorage                         │
│  (9) Set Pinia state                                     │
│  (10) Set Axios default header                           │
│  (11) Redirect to /dashboard                             │
│                                                           │
│  Future Requests:                                        │
│  All API calls include:                                  │
│  "Authorization: Bearer {token}"                         │
│                                                           │
│  Backend validates token via:                            │
│  auth:sanctum middleware                                 │
└───────────────────────────────────────────────────────────┘
```

---

## 🛡️ Security Boundaries

```
┌────────────────────────────────────────────────────────────┐
│  PUBLIC ZONE (No Authentication Required)                 │
│                                                            │
│  • POST /api/auth/login      ◄── User credentials here   │
│  • POST /api/auth/register   ◄── New user registration   │
│                                                            │
│  ✓ Input validation enforced                              │
│  ✓ Password hashed with bcrypt                            │
│  ✓ CORS protection enabled                                │
│  ✗ No token required                                      │
└────────────────────────────────────────────────────────────┘
                         ▲
                         │
                    AUTH TOKEN
                    (Sanctum)
                         │
                         ▼
┌────────────────────────────────────────────────────────────┐
│  PROTECTED ZONE (Authentication Required)                 │
│                                                            │
│  Middleware: auth:sanctum                                 │
│                                                            │
│  • GET /api/auth/user       ◄── Get current user          │
│  • POST /api/auth/logout    ◄── Revoke token              │
│  • GET /api/dashboard/*     ◄── Dashboard data            │
│  • GET /api/transactions/*  ◄── Transaction data          │
│  • etc...                                                  │
│                                                            │
│  ✓ Bearer token validated                                 │
│  ✓ Token stored in DB (personal_access_tokens)            │
│  ✓ Token automatically injected by Axios                  │
│  ✓ 401 auto-logout on invalid token                       │
└────────────────────────────────────────────────────────────┘
```

---

## 📈 Error Handling Flow

```
API Request
    │
    ▼
Response Received
    │
    ├─ Status 200 (Success)
    │  └─ Extract data & use normally
    │
    ├─ Status 401 (Unauthorized)
    │  └─ Response Interceptor catches
    │     ├─ Delete localStorage token
    │     ├─ Clear Pinia auth state
    │     ├─ Clear Axios default headers
    │     └─ Redirect to /login
    │
    ├─ Status 422 (Validation Error)
    │  └─ Display validation errors to user
    │
    └─ Status 5xx (Server Error)
       └─ Display generic error message
          Log error to console
```

---

## 🔑 Token Lifecycle

```
┌─────────────────────────────────────────────────────────┐
│                    Token Lifecycle                       │
└─────────────────────────────────────────────────────────┘

1. CREATED
   └─ User logs in
      └─ AuthController.login()
         └─ $user->createToken('auth-token')
            └─ Stored in personal_access_tokens table

2. STORED
   └─ Sent to frontend in login response
   └─ Frontend saves to localStorage
   └─ Browser keeps in memory while session active

3. USED
   └─ Axios interceptor adds to request headers
      └─ Authorization: Bearer {token}
   └─ Laravel validates via auth:sanctum middleware
   └─ Request processed if valid

4. INVALIDATED (Logout)
   └─ POST /api/auth/logout
      └─ AuthController.logout()
         └─ $request->user()->currentAccessToken()->delete()
   └─ Removed from database
   └─ Frontend clears localStorage
   └─ No longer valid for future requests

5. EXPIRED (Optional - not implemented yet)
   └─ Can set expiration time: expires_at
   └─ Returns 401 if expired
   └─ Requires re-login
```

---

**This architecture ensures secure, scalable authentication for the Books accounting system.**
