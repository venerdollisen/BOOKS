@echo off
REM Quick setup script for Books Accounting System

echo.
echo ====================================
echo Books Accounting System - Quick Setup
echo ====================================
echo.

REM Check if backend directory exists
if not exist "backend" (
    echo Error: backend directory not found!
    exit /b 1
)

REM Check if node_modules exists
if not exist "node_modules" (
    echo Installing frontend dependencies...
    call npm install
)

REM Setup backend
cd backend

REM Check if vendor exists
if not exist "vendor" (
    echo Installing Laravel dependencies...
    call composer install
)

REM Check if .env exists
if not exist ".env" (
    echo Creating .env file...
    copy .env.example .env
    echo Please update .env with your database configuration
)

REM Run migrations and seeders
echo.
echo Running database migrations...
call php artisan migrate

echo.
echo Seeding test users...
call php artisan db:seed

echo.
echo ====================================
echo Setup Complete!
echo ====================================
echo.
echo To start development:
echo 1. Backend: php artisan serve
echo 2. Frontend: npm run dev
echo.
echo Test Credentials:
echo   Email: admin@example.com
echo   Password: password123
echo.
echo See AUTHENTICATION_SETUP.md for complete guide
echo.

cd ..
pause
