# Quick Start Guide - Running the Application

## Prerequisites
- PHP 8.2+
- Node.js 18+
- Composer
- npm or yarn

## 🚀 Quick Start (5 minutes)

### Terminal 1: Start Laravel Backend
```bash
cd backend
composer install
php artisan migrate
php artisan serve
```

Backend runs at: `http://localhost:8000`

### Terminal 2: Start Vue Frontend
```bash
npm install
npm run dev
```

Frontend runs at: `http://localhost:5173`

## 🔑 Test Login

### Option 1: Register a New User
1. Open `http://localhost:5173`
2. Click "Sign In"
3. Use any email and password (min 6 chars) - system will auto-register

### Option 2: Use Seeded User
```bash
cd backend
php artisan migrate
php artisan tinker

# In tinker shell:
>>> use App\Models\User;
>>> use Illuminate\Support\Facades\Hash;
>>> User::create(['name' => 'Test', 'email' => 'test@test.com', 'password' => Hash::make('password')]);
>>> exit
```

Then login with:
- Email: `test@test.com`
- Password: `password`

## ✅ Verify It's Working

1. **Backend API Test**
   ```bash
   curl http://localhost:8000/api/auth/login \
     -X POST \
     -H "Content-Type: application/json" \
     -d '{"email":"test@test.com","password":"password"}'
   ```
   Should return a token

2. **Frontend Test**
   - Go to `http://localhost:5173`
   - Login with test credentials
   - Check browser console (F12) - should see token in localStorage
   - Check Network tab - requests should have `Authorization: Bearer {token}`

3. **Protected Route Test**
   ```bash
   curl http://localhost:8000/api/auth/user \
     -H "Authorization: Bearer YOUR_TOKEN_HERE"
   ```
   Should return user data

## 🛠️ Development Mode

### Run Both Servers Together (Optional)
```bash
cd backend
php artisan serve &
cd ..
npm run dev
```

### Useful Commands

**Laravel**
```bash
php artisan tinker              # Interactive PHP shell
php artisan migrate             # Run migrations
php artisan migrate:fresh       # Reset database
php artisan db:seed             # Seed test data
php artisan route:list          # List all routes
```

**Vue/Frontend**
```bash
npm run build                   # Build for production
npm run preview                 # Preview production build
npm run lint                    # Check code quality
```

## 🔐 Security Checklist

✅ Token-based authentication (Sanctum)
✅ Password hashing (bcrypt)
✅ CORS configured for localhost
✅ Input validation on all endpoints
✅ Automatic token injection on requests
✅ 401 handling with logout
✅ Secure error messages

## 📁 Key Files

**Backend**
- `app/Http/Controllers/AuthController.php` - Auth logic
- `routes/api.php` - API routes
- `config/cors.php` - CORS configuration
- `app/Models/User.php` - User model with Sanctum

**Frontend**
- `src/services/api.js` - API client with interceptors
- `src/stores/auth.js` - Authentication store
- `src/views/Login.vue` - Login page
- `.env.local` - Environment config

## 🆘 Troubleshooting

**Port Already in Use**
```bash
# Laravel on different port
php artisan serve --port=8001

# Frontend on different port
npm run dev -- --port 3000
```

**Database Issues**
```bash
cd backend
rm database/database.sqlite  # Delete old DB
php artisan migrate          # Create fresh DB
```

**CORS Errors**
- Ensure backend is running on `http://localhost:8000`
- Frontend is on `http://localhost:5173`
- Check `backend/config/cors.php`

**Module Not Found**
```bash
# Backend
cd backend
composer install

# Frontend
npm install
```

## 📚 Next Steps

1. Create dashboard components
2. Add transaction management
3. Implement financial reports
4. Add user management
5. Set up email notifications
6. Deploy to production

---

For full documentation, see [SETUP_AUTHENTICATION.md](SETUP_AUTHENTICATION.md)
