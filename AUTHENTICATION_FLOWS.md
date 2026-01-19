# Authentication Flow Diagrams

## 1. Login Flow

```
┌─────────────────────────────────────────────────────────────────┐
│ User Opens http://localhost:5173/login                          │
└─────────────────────────────────┬───────────────────────────────┘
                                  │
                    ┌─────────────▼─────────────┐
                    │ Login Component Displays  │
                    │ - Email input             │
                    │ - Password input          │
                    │ - Remember me checkbox    │
                    │ - Sign In button          │
                    └─────────────┬─────────────┘
                                  │
                    User enters credentials
                                  │
                    ┌─────────────▼─────────────────────────┐
                    │ User clicks "Sign In"                 │
                    │ handleLogin() called                  │
                    │ 1. Validate form                      │
                    │ 2. authStore.login(email, password)   │
                    └─────────────┬─────────────────────────┘
                                  │
                    ┌─────────────▼──────────────────┐
                    │ API Call                       │
                    │ POST /api/login                │
                    │ {                              │
                    │   email: "...",                │
                    │   password: "..."             │
                    │ }                              │
                    └─────────────┬──────────────────┘
                                  │
                                  │ (CORS allows localhost:5173)
                                  │
         ┌────────────────────────▼──────────────────────┐
         │ Laravel Backend                               │
         │ AuthController.login()                        │
         │ 1. Find user by email                         │
         │ 2. Hash::check(password, user.password)       │
         └────────────┬──────────────────────────────────┘
                      │
         ┌────────────▼──────────────────────┐
         │ Validation Result                 │
         ├────────────┬──────────────────────┤
         │ Valid      │ Invalid              │
         │ ✓          │ ✗                    │
         └────┬───────┴──────┬───────────────┘
              │              │
    ┌─────────▼────┐    ┌─────▼──────────────┐
    │ Create Token │    │ Return 422 Error   │
    │ Generate     │    │ Invalid Credentials│
    │ API Token    │    └─────┬──────────────┘
    │ (Sanctum)    │          │
    └─────────┬────┘          │
              │               │
    ┌─────────▼────────────────────────┐
    │ Return Response                  │
    │ {                                │
    │   "token": "1|abc123...",        │
    │   "user": {                      │
    │     "id": 1,                     │
    │     "name": "Admin",             │
    │     "email": "admin@..."         │
    │   }                              │
    │ }                                │
    └─────────┬────────────────────────┘
              │
    ┌─────────▼────────────────────────┐
    │ Frontend Auth Store               │
    │ 1. Save token                     │
    │ 2. Save user data                 │
    │ 3. Store in localStorage or       │
    │    sessionStorage                 │
    │    (based on remember me)         │
    │ 4. Set Auth header:               │
    │    Authorization: Bearer {token}  │
    └─────────┬────────────────────────┘
              │
    ┌─────────▼────────────────────────┐
    │ Redirect to Dashboard             │
    │ router.push('/')                  │
    └─────────┬────────────────────────┘
              │
    ┌─────────▼────────────────────────┐
    │ Router Guard Validates Auth       │
    │ ✓ User authenticated              │
    │ ✓ Allow navigation                │
    │ ✓ Render dashboard                │
    └─────────────────────────────────┘
```

## 2. Protected API Request Flow

```
┌──────────────────────────────────────┐
│ Component Code                       │
│ accountsApi.getAll()                 │
└──────────┬───────────────────────────┘
           │
           ├─ GET /api/accounts
           │
┌──────────▼──────────────────────────────┐
│ Request Interceptor (axios)            │
│ 1. Get token from storage              │
│ 2. Add Authorization header            │
│    Authorization: Bearer {token}       │
└──────────┬───────────────────────────┘
           │
           │ HTTP Request with token in header
           │ ↓
┌──────────▼────────────────────────────┐
│ CORS Check (Backend)                  │
│ Origin: http://localhost:5173         │
│ Allowed? ✓ YES                        │
│ Proceed...                            │
└──────────┬───────────────────────────┘
           │
┌──────────▼──────────────────────────────┐
│ Sanctum Middleware (auth:sanctum)      │
│ 1. Extract token from header           │
│ 2. Find token in personal_access_tokens│
│ 3. Validate token exists & not expired │
│ 4. Load associated user                │
│ 5. Set $request->user()                │
└──────────┬───────────────────────────┘
           │
      ┌────┴──────────┐
      │ Token Valid?  │
      └────┬──────────┘
           │
    ┌──────┴───────┐
    │ Valid ✓      │ Invalid ✗
    │              │
┌───▼────────┐   ┌┴──────────────────┐
│ Controller │   │ Return 401        │
│ AccountCtl │   │ Unauthorized      │
│ index()    │   └┬──────────────────┘
│ 1. Query   │     │
│    accounts│     └──────┬──────────┐
│ 2. Return  │           │          │
│    JSON    │           ↓          │
└───┬────────┘     ┌──────────────┐ │
    │              │ Response     │ │
    │              │ Interceptor  │ │
    │              │ (on frontend)│ │
    │              │ Status: 401  │ │
    │              │ 1. Clear     │ │
    │              │    token     │ │
    │              │ 2. Logout    │ │
    │              │ 3. Redirect  │ │
    │              │    to login  │ │
    │              └──────────────┘ │
    │                               │
┌───▼────────────────────────────┐  │
│ Response (200)                 │  │
│ [                              │  │
│   {                            │  │
│     "id": 1,                   │  │
│     "name": "Cash",            │  │
│     "type": "asset",           │  │
│     "balance": 1000            │  │
│   },                           │  │
│   ...                          │  │
│ ]                              │  │
└───┬────────────────────────────┘  │
    │                               │
┌───▼────────────────────────────┐  │
│ Response Interceptor           │  │
│ Status: 200                    │  │
│ ✓ Success                      │  │
│ Return data to component       │  │
└───┬────────────────────────────┘  │
    │                               │
┌───▼──────────────────────────────┐│
│ Component receives data          ││
│ Update UI with accounts          ││
│ Display in table/list            ││
└────────────────────────────────────┘
```

## 3. Logout Flow

```
┌─────────────────────────────────────────┐
│ User clicks logout button in Navbar     │
│ @click="authStore.logout()"             │
└──────────┬──────────────────────────────┘
           │
┌──────────▼──────────────────────────────┐
│ Auth Store logout() method              │
│ 1. Check if token exists                │
│ 2. Call authApi.logout()                │
└──────────┬──────────────────────────────┘
           │
           ├─ POST /api/logout
           │  Authorization: Bearer {token}
           │
┌──────────▼──────────────────────────────┐
│ Sanctum Middleware                      │
│ 1. Validate token                       │
│ 2. Load user                            │
│ 3. Token valid? ✓ Proceed               │
└──────────┬──────────────────────────────┘
           │
┌──────────▼──────────────────────────────┐
│ Laravel AuthController.logout()         │
│ Delete token from personal_access_tokens│
│ $request->user()->currentAccessToken()  │
│  ->delete()                             │
└──────────┬──────────────────────────────┘
           │
┌──────────▼──────────────────────────────┐
│ Return Response                         │
│ { "message": "Logout successful" }      │
└──────────┬──────────────────────────────┘
           │
┌──────────▼──────────────────────────────┐
│ Frontend (catch block if error)         │
│ Clear all storage:                      │
│ 1. localStorage.removeItem('auth_token')│
│ 2. sessionStorage.removeItem(...)       │
│ 3. Clear authStore.user = null          │
│ 4. Clear authStore.token = null         │
│ 5. Delete Authorization header         │
└──────────┬──────────────────────────────┘
           │
┌──────────▼──────────────────────────────┐
│ Redirect to Login                       │
│ window.location.href = '/login'         │
└──────────┬──────────────────────────────┘
           │
┌──────────▼──────────────────────────────┐
│ User back on Login page                 │
│ All data cleared                        │
│ Ready for new login                     │
└─────────────────────────────────────────┘
```

## 4. Page Refresh - Session Restoration

```
┌───────────────────────────────────────┐
│ User refreshes page                   │
│ F5 / Cmd+R                            │
└────────┬────────────────────────────┬─┘
         │                            │
    ┌────▼──────────┐          ┌──────▼────────────┐
    │ Browser       │          │ App.vue mounted() │
    │ Storage       │          │ Checks storage    │
    │ (localStorage)│          └────────┬──────────┘
    │ has token     │                   │
    └────┬──────────┘         ┌─────────▼────────────────┐
         │                    │ authStore.initAuth()     │
         │                    │ 1. Check localStorage    │
         │                    │ 2. Check sessionStorage  │
         │                    │ 3. Token found? ✓        │
         │                    └─────────┬────────────────┘
         │                              │
         │                    ┌─────────▼────────────────┐
         │                    │ Set token in store       │
         │                    │ Set Authorization header │
         │                    │ GET /api/user            │
         │                    └─────────┬────────────────┘
         │                              │
         │                    ┌─────────▼────────────────┐
         │                    │ Backend validates token  │
         │                    │ Sanctum middleware       │
         │                    │ Token still valid? ✓     │
         │                    └─────────┬────────────────┘
         │                              │
         │                    ┌─────────▼────────────────┐
         │                    │ Return user data         │
         │                    │ { user: {...} }          │
         │                    └─────────┬────────────────┘
         │                              │
         │                    ┌─────────▼────────────────┐
         │                    │ authStore.user = data    │
         │                    │ authStore.token exists   │
         │                    │ isAuthenticated = true   │
         │                    └─────────┬────────────────┘
         │                              │
         │                    ┌─────────▼────────────────┐
         │                    │ Router evaluates route   │
         │                    │ Protected route?         │
         │                    │ isAuthenticated? ✓       │
         │                    │ Allow access             │
         │                    └─────────┬────────────────┘
         │                              │
         └──────────────┬───────────────┘
                        │
         ┌──────────────▼─────────────────┐
         │ Render requested page          │
         │ Dashboard / Account / etc.     │
         │ User doesn't see login page    │
         │ Session persisted!             │
         └────────────────────────────────┘


Alternative: Token Expired or Deleted

┌────────────────────────────────┐
│ User refreshes page            │
│ Token in localStorage exists   │
└────────┬─────────────────────┬─┘
         │                     │
    ┌────▼──────────┐      ┌───▼──────────────────┐
    │ Token in      │      │ GET /api/user        │
    │ storage       │      │ with old token       │
    └────┬──────────┘      └───┬──────────────────┘
         │                     │
         │              ┌──────▼──────────────┐
         │              │ Sanctum validates   │
         │              │ Token not in DB     │
         │              │ ✗ INVALID           │
         │              └──────┬──────────────┘
         │                     │
         │              ┌──────▼──────────────┐
         │              │ Return 401          │
         │              │ Unauthorized        │
         │              └──────┬──────────────┘
         │                     │
         │    ┌────────────────▼───────────┐
         │    │ Response Interceptor       │
         │    │ Status: 401                │
         │    │ 1. Clear token from store  │
         │    │ 2. Clear localStorage      │
         │    │ 3. Clear sessionStorage    │
         │    └────────┬──────────────────┘
         │             │
    ┌────▼─────────────▼────────────┐
    │ Redirect to login page         │
    │ window.location.href = '/login'│
    └───────────────────────────────┘
```

## 5. Authentication State Machine

```
                          ┌─────────────────┐
                          │   No Session    │
                          │  (Initial Load) │
                          └────────┬────────┘
                                   │
                           ┌───────▼────────┐
                           │ Check Storage  │
                           └───────┬────────┘
                                   │
                    ┌──────────────┬──────────────┐
                    │              │              │
            ┌───────▼─┐      ┌─────▼────┐   ┌────▼──────┐
            │ No Token│      │Token Found│   │Initialize │
            │ Found   │      └─────┬─────┘   │   Auth    │
            │         │            │         └────┬──────┘
            └────┬────┘            │              │
                 │         ┌───────▼─────────┐   │
                 │         │GET /api/user    │   │
                 │         └───────┬─────────┘   │
                 │                 │             │
            ┌────▼──────────────┐  │  ┌──────────▼────────────┐
            │  Show Login Page  │  │  │  Valid Response?      │
            │                  │  │  └──────────┬─────────────┘
            └────┬──────────────┘  │             │
                 │          ┌──────┴─────┐       │
                 │          │ ✓ Yes      │ ✗ No │
                 │    ┌─────▼──┐    ┌────▼──┐   │
                 │    │Store   │    │Clear  │   │
                 │    │token   │    │token  │   │
                 │    └────┬───┘    └──┬────┘   │
                 │         │           │       │
                 │  ┌──────▼─────────┐ │       │
                 │  │   LOGGED IN    │ │       │
                 │  │   State        │ │       │
                 │  └──────┬─────────┘ │       │
                 │         │           │       │
                 │ ┌───────▼───────────┴──┐    │
            ┌────▼─┤ User enters email/pw  │    │
            │      └────────┬──────────────┘    │
            │       POST /api/login             │
            │               │                   │
            │      ┌────────▼─────────┐        │
            │      │Validation Result │        │
            │      └────────┬─────────┘        │
            │               │                   │
            │      ┌────────┴────────┐         │
            │      │ ✓ Valid ✗ Invalid │       │
            │      │                │         │
            │      │         ┌──────▼────┐   │
            │      │         │Show error  │   │
            │      │         │message     │   │
            │      │         │Stay on login│  │
            │      │         └────────────┘   │
            │      │                │         │
        ┌───▼──────▼────────────────┘         │
        │ Save token + user data              │
        │ Set Authorization header            │
        │ Redirect to dashboard               │
        └────┬───────────────────────────────┘
             │
     ┌───────▼──────────────┐
     │  LOGGED IN - ACTIVE  │
     │  Can access all      │
     │  protected routes    │
     └───────┬──────────────┘
             │
      ┌──────┴──────┐
      │             │
   ┌──▼──┐    ┌────▼─────────┐
   │API  │    │User clicks    │
   │call │    │logout button  │
   │     │    └────┬──────────┘
   │     │         │
   │ ┌───▼──────────▼────────────┐
   │ │POST /api/logout           │
   │ │Delete token from DB       │
   │ │Clear frontend storage     │
   │ └───┬─────────────────────┬─┘
   │     │                     │
   │ ┌───▼─────┐          ┌────▼──────────┐
   │ │Success  │          │401 response   │
   │ │         │          │(token invalid)│
   │ └───┬─────┘          └────┬──────────┘
   │     │                     │
   └─────┴────┬─────────────────┘
              │
     ┌────────▼──────────────┐
     │ LOGGED OUT            │
     │ Clear all storage     │
     │ Redirect to login     │
     │ Return to initial     │
     │ state                 │
     └───────────────────────┘
```

---

These diagrams show the complete authentication flow in your system. Every interaction follows these paths, ensuring secure and consistent behavior throughout the application.
