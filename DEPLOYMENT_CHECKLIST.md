# Production Deployment Checklist

## Backend Setup

### Environment Variables (.env)

- [ ] `APP_URL=https://yourdomain.com` (use HTTPS in production)
- [ ] `APP_DEBUG=false` (disable debug mode)
- [ ] `APP_KEY=base64:...` (generate with `php artisan key:generate`)
- [ ] Database credentials configured
- [ ] `DB_CONNECTION=mysql` (or your production database)
- [ ] `SANCTUM_STATEFUL_DOMAINS` includes your frontend domain
- [ ] Mail configuration (if needed)
- [ ] Session driver (redis recommended for scale)

### Database

- [ ] Run migrations: `php artisan migrate --env=production`
- [ ] Run seeders if needed: `php artisan db:seed`
- [ ] Backup database before deployment
- [ ] Verify users table has correct data
- [ ] Test login with production credentials

### Security

- [ ] SSL/TLS certificate installed
- [ ] Force HTTPS in web server config
- [ ] Disable HTTP access
- [ ] Set secure cookie flags in `config/session.php`:
  ```php
  'secure' => true,        // HTTPS only
  'http_only' => true,     // Not accessible via JavaScript
  'same_site' => 'lax',    // CSRF protection
  ```
- [ ] Update CORS allowed origins:
  ```php
  'allowed_origins' => [
    'https://yourdomain.com',
    'https://www.yourdomain.com',
  ]
  ```
- [ ] Enable CSRF protection
- [ ] Rate limiting on login endpoint:
  ```php
  Route::post('/login', [AuthController::class, 'login'])->throttle('6,1');
  ```

### Caching

- [ ] Configure cache driver (redis/memcached)
- [ ] Test cache functionality
- [ ] Set up cache clearing in deployment script

### Storage & Logs

- [ ] Configure file storage (S3 or local)
- [ ] Set up log rotation
- [ ] Monitor error logs
- [ ] Ensure `storage/` directory writable
- [ ] Ensure `bootstrap/cache/` directory writable

### Server Configuration

- [ ] PHP version >= 8.1
- [ ] Required extensions installed
- [ ] Memory limit >= 256MB
- [ ] Upload limit configured
- [ ] OPcache enabled
- [ ] Web server (Nginx/Apache) configured
- [ ] `.env` file permissions restricted

### Testing

- [ ] Test login endpoint: `POST /api/login`
- [ ] Test protected endpoints require token
- [ ] Test token validation
- [ ] Test logout clears token
- [ ] Test CORS headers correct
- [ ] Test error handling

## Frontend Setup

### Environment Variables (.env.production)

- [ ] `VITE_API_URL=https://yourdomain.com/api`
- [ ] `VITE_APP_NAME=Bookkeeping System`
- [ ] Remove debug/logging code

### Build

- [ ] Run build: `npm run build`
- [ ] Check dist/ folder is created
- [ ] Verify all assets compiled
- [ ] Check bundle size is reasonable
- [ ] Test source maps disabled in production

### Configuration

- [ ] Update `src/config/api.js` base URL to production
- [ ] Remove mock authentication code
- [ ] Ensure error messages are user-friendly
- [ ] Configure toast/notification styling

### Performance

- [ ] Minification enabled
- [ ] Code splitting configured
- [ ] Images optimized
- [ ] CSS/JS compressed
- [ ] Service worker configured (optional)
- [ ] Caching headers set

### Security

- [ ] Remove console.log statements
- [ ] Remove debug mode
- [ ] Sensitive data not hardcoded
- [ ] API keys not exposed in code
- [ ] XSS protection enabled
- [ ] CSP headers configured

### Deployment

- [ ] Upload dist/ folder to server
- [ ] Configure web server to serve index.html for SPA routes
- [ ] Set correct MIME types for assets
- [ ] Enable gzip compression
- [ ] Enable browser caching headers

### Testing

- [ ] Test login flow on production
- [ ] Test all protected routes
- [ ] Test API calls work
- [ ] Test error handling
- [ ] Test logout
- [ ] Test remember me functionality
- [ ] Test on mobile devices
- [ ] Test on different browsers

## Nginx Configuration Example

```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Frontend
    root /var/www/bookkeeping/dist;
    index index.html;

    # Gzip compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript;

    # SPA routing
    location / {
        try_files $uri $uri/ /index.html;
        expires 1h;
        add_header Cache-Control "public, max-age=3600";
    }

    # API proxy to Laravel
    location /api {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 60s;
    }

    # Assets caching
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # Security headers
    add_header X-Frame-Options "DENY";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}
```

## Apache Configuration Example

```apache
<VirtualHost *:443>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key

    DocumentRoot /var/www/bookkeeping/dist

    <Directory /var/www/bookkeeping/dist>
        Options -MultiViews
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^ index.html [QSA,L]
    </Directory>

    # API proxy
    ProxyPreserveHost On
    ProxyPass /api http://localhost:8000/api
    ProxyPassReverse /api http://localhost:8000/api

    # Gzip compression
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/plain text/html text/css text/xml text/javascript application/javascript application/json
    </IfModule>

    # Caching headers
    <IfModule mod_expires.c>
        ExpiresActive On
        ExpiresByType application/json "access 1 hour"
        ExpiresByType text/javascript "access 30 days"
        ExpiresByType application/javascript "access 30 days"
        ExpiresByType text/css "access 30 days"
        ExpiresByType image/png "access 30 days"
        ExpiresByType image/jpeg "access 30 days"
    </IfModule>

    # Security headers
    Header set X-Frame-Options "DENY"
    Header set X-Content-Type-Options "nosniff"
    Header set X-XSS-Protection "1; mode=block"
</VirtualHost>

<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    Redirect permanent / https://yourdomain.com/
</VirtualHost>
```

## Deployment Commands

### Initial Deployment

```bash
# 1. Clone repository
git clone <repo> /var/www/bookkeeping
cd /var/www/bookkeeping

# 2. Backend setup
cd backend
cp .env.example .env
# Edit .env with production credentials
php artisan key:generate
composer install --no-dev
php artisan migrate --force
php artisan db:seed --force

# 3. Frontend setup
cd ../
npm install
npm run build

# 4. Set permissions
chmod -R 755 backend/storage
chmod -R 755 backend/bootstrap/cache
chown -R www-data:www-data backend/storage backend/bootstrap/cache

# 5. Start services
cd backend
php-fpm start  # or restart
# Configure Nginx/Apache
```

### Update Deployment

```bash
# 1. Pull latest code
git pull origin main

# 2. Backend update
cd backend
composer install --no-dev
php artisan migrate --force

# 3. Frontend update
cd ../
npm install
npm run build

# 4. Clear caches
cd backend
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 5. Restart services
php-fpm restart
# Reload Nginx: nginx -s reload
```

## Monitoring

### Backend Monitoring

- [ ] Monitor server logs: `tail -f storage/logs/laravel.log`
- [ ] Monitor MySQL queries
- [ ] Monitor Redis (if using)
- [ ] Track failed login attempts
- [ ] Monitor disk space
- [ ] Monitor memory usage
- [ ] Monitor CPU usage

### Frontend Monitoring

- [ ] Monitor browser console errors
- [ ] Track failed API calls
- [ ] Monitor page load times
- [ ] Use error tracking service (Sentry, etc.)
- [ ] Monitor 401/403 errors

### Performance Monitoring

- [ ] Setup uptime monitoring
- [ ] Setup performance monitoring
- [ ] Setup alerting for errors
- [ ] Setup logs aggregation (ELK, Datadog, etc.)

## Maintenance

### Regular Tasks

- [ ] Update dependencies monthly
  ```bash
  composer update
  npm update
  ```
- [ ] Review and delete old logs
- [ ] Backup database daily
- [ ] Monitor failed logins
- [ ] Review security logs
- [ ] Update SSL certificates before expiration

### Security Updates

- [ ] Subscribe to Laravel security notifications
- [ ] Subscribe to Vue/npm security notifications
- [ ] Test updates in staging first
- [ ] Keep PHP updated
- [ ] Keep OS updated
- [ ] Enable unattended security updates

## Rollback Plan

```bash
# If deployment fails, rollback to previous version:
git checkout <previous-commit>
cd backend && composer install
cd .. && npm install && npm run build
php artisan cache:clear
```

## Health Check URLs

- [ ] Frontend: `https://yourdomain.com`
- [ ] API Health: `https://yourdomain.com/api/health`
- [ ] Login: `https://yourdomain.com/login`

## Post-Deployment Testing

- [ ] Test login with production database user
- [ ] Test all major workflows
- [ ] Test on different devices/browsers
- [ ] Test with slow network (DevTools throttling)
- [ ] Verify HTTPS working
- [ ] Verify redirects working
- [ ] Verify error pages displaying
- [ ] Check console for errors
- [ ] Check network for failed requests

## Backup & Recovery

### Database Backup

```bash
# MySQL backup
mysqldump -u root -p bookkeeping > backup_$(date +%Y%m%d).sql

# Restore
mysql -u root -p bookkeeping < backup_20240117.sql
```

### File Backup

```bash
# Backup entire application
tar -czf backup_$(date +%Y%m%d).tar.gz /var/www/bookkeeping/

# Restore
tar -xzf backup_20240117.tar.gz -C /var/www/
```

## DNS & Domain

- [ ] Update DNS records to point to new server
- [ ] Verify DNS propagation
- [ ] Setup email if needed
- [ ] Configure SPF/DKIM if using email
- [ ] Test email delivery

## Compliance & Legal

- [ ] Privacy policy updated
- [ ] Terms of service updated
- [ ] GDPR compliance (if EU users)
- [ ] Data retention policy
- [ ] User data export functionality
- [ ] User data deletion functionality

---

## Deployment Checklist Status

**Backend Ready**: ☐
**Frontend Ready**: ☐
**Infrastructure Ready**: ☐
**Testing Complete**: ☐
**Monitoring Setup**: ☐
**Backup Ready**: ☐
**Go Live**: ☐
