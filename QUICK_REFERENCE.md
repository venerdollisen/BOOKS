# Quick Reference - Commands & Endpoints

## Development Commands

### Backend

```bash
# Navigate to backend
cd backend

# Run migrations (creates tables)
php artisan migrate

# Seed database with test users
php artisan db:seed

# Start development server
php artisan serve
# Runs on http://localhost:8000

# Reset database
php artisan migrate:refresh --seed

# Create new migration
php artisan make:migration migration_name

# Clear cache
php artisan cache:clear
php artisan config:clear
```

### Frontend

```bash
# Navigate to frontend root
cd path/to/project

# Install dependencies
npm install

# Start development server
npm run dev
# Runs on http://localhost:5173

# Build for production
npm run build

# Preview production build
npm run preview

# Lint and format
npm run lint
```

## API Endpoints

### Authentication (Public)

```bash
# Login
POST http://localhost:8000/api/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password123"
}
```

### Authentication (Protected - requires token)

```bash
# Get current user
GET http://localhost:8000/api/user
Authorization: Bearer {token}

# Logout
POST http://localhost:8000/api/logout
Authorization: Bearer {token}

# Refresh token
POST http://localhost:8000/api/refresh-token
Authorization: Bearer {token}
```

### Accounts (Protected)

```bash
# Get all accounts
GET http://localhost:8000/api/accounts
Authorization: Bearer {token}

# Get single account
GET http://localhost:8000/api/accounts/{id}
Authorization: Bearer {token}

# Create account
POST http://localhost:8000/api/accounts
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Account Name",
  "type": "asset"
}

# Update account
PUT http://localhost:8000/api/accounts/{id}
Authorization: Bearer {token}
Content-Type: application/json

# Delete account
DELETE http://localhost:8000/api/accounts/{id}
Authorization: Bearer {token}
```

### Transactions (Protected)

```bash
# Get all transactions
GET http://localhost:8000/api/transactions
Authorization: Bearer {token}

# Get single transaction
GET http://localhost:8000/api/transactions/{id}
Authorization: Bearer {token}

# Create transaction
POST http://localhost:8000/api/transactions
Authorization: Bearer {token}
Content-Type: application/json

# Update transaction
PUT http://localhost:8000/api/transactions/{id}
Authorization: Bearer {token}

# Delete transaction
DELETE http://localhost:8000/api/transactions/{id}
Authorization: Bearer {token}
```

## Test Credentials

| Field | Value |
|-------|-------|
| Email | admin@example.com |
| Password | password123 |

Alternative test user:
| Field | Value |
|-------|-------|
| Email | test@example.com |
| Password | password123 |

## Browser Storage

### Check Token in Console

```javascript
// Check localStorage
localStorage.getItem('auth_token')

// Check sessionStorage
sessionStorage.getItem('auth_token')

// Clear token manually
localStorage.removeItem('auth_token')
sessionStorage.removeItem('auth_token')
```

## Database

### SQLite (if using SQLite driver)

```bash
# View database file location (in .env)
DB_DATABASE=database/database.sqlite

# SQLite CLI
sqlite3 database/database.sqlite

# In SQLite:
SELECT * FROM users;
SELECT * FROM personal_access_tokens;
SELECT * FROM accounts;
SELECT * FROM transactions;
```

### MySQL

```bash
# Connect to MySQL
mysql -u root -p bookkeeping

# List tables
SHOW TABLES;

# View users
SELECT * FROM users;

# View tokens
SELECT * FROM personal_access_tokens;
```

## Common Issues & Quick Fixes

### Laravel migrations fail
```bash
cd backend
php artisan migrate:rollback
php artisan migrate --seed
```

### Vue can't connect to Laravel
```bash
# Verify Laravel is running
cd backend
php artisan serve

# Check CORS config
cat config/cors.php
# Should include 'localhost:5173'
```

### Token not working
```bash
# Database might be corrupted
cd backend
php artisan migrate:refresh --seed
# This will delete all data and recreate tables
```

### Port already in use
```bash
# For Laravel (default 8000)
php artisan serve --port=8001

# For Vue (default 5173)
npm run dev -- --port 3000
```

## File Locations

```
Books/
├── backend/
│   ├── app/Models/User.php
│   ├── app/Http/Controllers/Api/AuthController.php
│   ├── routes/api.php
│   ├── config/
│   │   ├── cors.php
│   │   └── sanctum.php
│   ├── database/
│   │   ├── migrations/
│   │   │   └── 2024_01_17_000000_create_users_table.php
│   │   └── seeders/
│   │       ├── UserSeeder.php
│   │       └── DatabaseSeeder.php
│   └── .env
├── src/
│   ├── stores/auth.js
│   ├── services/api.js
│   ├── config/api.js
│   ├── views/Login.vue
│   ├── App.vue
│   └── router/index.js
├── AUTHENTICATION_SETUP.md
├── AUTHENTICATION_ARCHITECTURE.md
├── IMPLEMENTATION_SUMMARY.md
└── setup.bat
```

## Useful Laravel Artisan Commands

```bash
# Generate new controller
php artisan make:controller ControllerName

# Generate new model with migration
php artisan make:model ModelName -m

# Generate new migration
php artisan make:migration migration_name

# Generate new seeder
php artisan make:seeder SeederName

# List all routes
php artisan route:list

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Check Laravel version
php artisan --version

# Generate app key
php artisan key:generate
```

## Useful NPM Commands

```bash
# List installed packages
npm list

# Check for outdated packages
npm outdated

# Update packages
npm update

# Check for security vulnerabilities
npm audit

# Fix security vulnerabilities
npm audit fix

# Install specific package
npm install package-name

# Remove package
npm uninstall package-name

# Install dev dependency
npm install --save-dev package-name
```

## URL Quick Links

| Resource | URL |
|----------|-----|
| Frontend | http://localhost:5173 |
| Login | http://localhost:5173/login |
| Dashboard | http://localhost:5173/ |
| Laravel API | http://localhost:8000/api |
| Laravel Tinker | `php artisan tinker` |

## Using Laravel Tinker

```bash
# Start Tinker
php artisan tinker

# Get all users
User::all()

# Find user by email
User::where('email', 'admin@example.com')->first()

# Create new user
User::create(['name' => 'John', 'email' => 'john@example.com', 'password' => Hash::make('password')])

# Get user's tokens
User::find(1)->tokens

# Delete user's tokens
User::find(1)->tokens()->delete()

# Exit
exit
```

## Git Ignore

Key files to ignore (should already be in .gitignore):
```
backend/.env
backend/vendor/
backend/.env.local
node_modules/
dist/
.DS_Store
.env.local
.env.*.local
```

---

For detailed information, see:
- AUTHENTICATION_SETUP.md
- AUTHENTICATION_ARCHITECTURE.md
- IMPLEMENTATION_SUMMARY.md
