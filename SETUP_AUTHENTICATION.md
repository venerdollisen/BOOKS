# Secure Authentication Setup Guide

## Overview
This guide helps you set up and test the complete Vue.js + Laravel authentication system with secure API connections.

## System Architecture

### Backend (Laravel)
- **API Framework**: Laravel 12 with REST API
- **Authentication**: Laravel Sanctum (Token-based)
- **Database**: SQLite (development)
- **CORS**: Configured for localhost development

### Frontend (Vue.js)
- **Framework**: Vue 3 with Vite
- **State Management**: Pinia (auth store)
- **HTTP Client**: Axios with interceptors
- **Security**: Token storage in localStorage, automatic token refresh on 401

## Setup Instructions

### 1. Backend Setup (Laravel)

#### Step 1: Install Dependencies
```bash
cd backend
composer install
```

#### Step 2: Environment Configuration
The `.env` file is already configured with:
- `APP_URL=http://localhost:8000`
- `FRONTEND_URL=http://localhost:5173`
- SQLite database

#### Step 3: Database Setup
```bash
# Generate app key (if not already done)
php artisan key:generate

# Run migrations to create tables
php artisan migrate

# Seed test user (optional)
php artisan db:seed
```

#### Step 4: Install Sanctum (if not already installed)
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

#### Step 5: Start Laravel Development Server
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

The backend API will be available at: `http://localhost:8000/api`

### 2. Frontend Setup (Vue.js)

#### Step 1: Install Dependencies
```bash
cd .. # Go back to root directory
npm install
```

#### Step 2: Environment Configuration
The `.env.local` file is already configured with:
- `VITE_API_BASE_URL=http://localhost:8000/api`
- `VITE_APP_NAME=Books Accounting`

#### Step 3: Start Development Server
```bash
npm run dev
```

The frontend will be available at: `http://localhost:5173`

## API Endpoints

### Authentication Endpoints (Public)

#### Login
```
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}

Response:
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "1|...",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com"
    }
  }
}
```

#### Register
```
POST /api/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "user@example.com",
  "password": "password",
  "password_confirmation": "password"
}

Response:
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "token": "1|...",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com"
    }
  }
}
```

### Protected Endpoints (Require Authentication)

Include the token in the Authorization header:
```
Authorization: Bearer {token}
```

#### Get Current User
```
GET /api/auth/user

Response:
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com"
  }
}
```

#### Logout
```
POST /api/auth/logout

Response:
{
  "success": true,
  "message": "Logout successful"
}
```

## Testing the Authentication Flow

### 1. Manual Testing with Postman/curl

#### Test Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password"
  }'
```

#### Test Get User (Protected)
```bash
curl -X GET http://localhost:8000/api/auth/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

### 2. Testing in Vue.js Application

1. Open `http://localhost:5173` in your browser
2. Click "Sign In" button
3. Enter test credentials:
   - **Email**: Use any email address (system creates user on registration)
   - **Password**: Use any password (min 6 characters)
4. Click "Sign In"
5. You should be redirected to the dashboard
6. Token is automatically stored in localStorage
7. Token is automatically sent with all API requests via Authorization header

### 3. Test Data / Seed Users

To create test users, you can:

**Option A: Use the Registration Form**
1. Go to `http://localhost:5173/register` (if available)
2. Fill in the form and register a new account
3. This automatically logs you in

**Option B: Database Seeding (Direct)**
1. Edit `backend/database/seeders/DatabaseSeeder.php`:
```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);
    }
}
```

2. Run the seeder:
```bash
php artisan db:seed
```

3. Login with:
   - Email: `test@example.com`
   - Password: `password`

## Security Features Implemented

### Backend (Laravel)
✅ **Password Hashing**: Using bcrypt (BCRYPT_ROUNDS=12)
✅ **Token-based Auth**: Laravel Sanctum with personal access tokens
✅ **CORS Protection**: Configured for localhost development
✅ **Input Validation**: Email and password validation on all endpoints
✅ **Error Handling**: Secure error messages without exposing internals
✅ **Environment Variables**: Sensitive config in .env files

### Frontend (Vue.js)
✅ **Secure Token Storage**: Tokens in localStorage
✅ **Automatic Token Injection**: Axios interceptor adds token to requests
✅ **Token Refresh on 401**: Automatic logout if token expires
✅ **Form Validation**: Client-side validation before submission
✅ **HTTPS Ready**: Configuration supports HTTPS in production
✅ **XSS Protection**: Vue 3 templates auto-escape content

## Production Deployment

### Backend Changes for Production
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
FRONTEND_URL=https://your-domain.com

DB_CONNECTION=mysql  # Change to MySQL/PostgreSQL
DB_HOST=your-host
DB_DATABASE=your-db
DB_USERNAME=your-user
DB_PASSWORD=your-pass

CORS allowed_origins updated to production domain
```

### Frontend Changes for Production
```env
VITE_API_BASE_URL=https://your-domain.com/api
```

### Additional Security for Production
1. Enable HTTPS/SSL certificates
2. Use environment-specific .env files
3. Run `php artisan config:cache` to cache configurations
4. Use a production-grade database (PostgreSQL recommended)
5. Set up proper error logging
6. Enable CSRF protection for web routes
7. Implement rate limiting on authentication endpoints
8. Set up automated backups

## Troubleshooting

### Issue: CORS Error
**Solution**: Check that your frontend URL is in `config/cors.php` allowed_origins

### Issue: 401 Unauthorized
**Cause**: Token missing or expired
**Solution**: Login again to get a fresh token

### Issue: 422 Validation Error
**Cause**: Invalid request data
**Solution**: Check request payload matches the API schema

### Issue: Database Connection Failed
**Solution**: 
- Ensure `database.sqlite` exists in `backend/database/`
- Run `php artisan migrate`
- Check file permissions

### Issue: Module Not Found (Sanctum)
**Solution**: 
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

## Files Modified/Created

### Backend
- `app/Http/Controllers/AuthController.php` - New authentication controller
- `routes/api.php` - Updated with authentication routes
- `config/cors.php` - New CORS configuration
- `app/Models/User.php` - Added Sanctum trait
- `config/auth.php` - Added sanctum guard

### Frontend
- `src/services/api.js` - Updated with secure API client
- `src/stores/auth.js` - Improved auth store with register method
- `src/views/Login.vue` - Login component
- `.env.local` - Frontend environment variables
- `src/config/api.js` - API configuration

## Next Steps

1. ✅ **Authentication**: Complete (login, register, logout)
2. 📋 **Dashboard**: Create main dashboard view
3. 📊 **Charts**: Add financial charts
4. 💰 **Transactions**: Implement transaction management
5. 📈 **Reports**: Add financial reports

## Support & Documentation

- Laravel Sanctum Docs: https://laravel.com/docs/sanctum
- Vue 3 Docs: https://vuejs.org/
- Axios Docs: https://axios-http.com/
- Pinia Docs: https://pinia.vuejs.org/
