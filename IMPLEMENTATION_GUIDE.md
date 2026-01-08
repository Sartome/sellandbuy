# 🚀 IMPLEMENTATION GUIDE
# Sell & Buy Marketplace - Enhanced Version

## 📋 Table of Contents
1. [Prerequisites](#prerequisites)
2. [Installation Steps](#installation-steps)
3. [Configuration](#configuration)
4. [Security Setup](#security-setup)
5. [Feature Usage](#feature-usage)
6. [Troubleshooting](#troubleshooting)
7. [Maintenance](#maintenance)

---

## Prerequisites

### Required Software
- ✅ PHP >= 7.4 (PHP 8.0+ recommended)
- ✅ MySQL >= 5.7 or MariaDB >= 10.2
- ✅ Apache or Nginx web server
- ✅ Composer (PHP dependency manager)
- ✅ Git (optional, for version control)

### Required PHP Extensions
```bash
php -m | grep -E "pdo|pdo_mysql|mbstring|json|session|gd|fileinfo|openssl"
```

All of the above should be installed. If any are missing:
```bash
# Ubuntu/Debian
sudo apt-get install php-pdo php-mysql php-mbstring php-json php-gd

# CentOS/RHEL
sudo yum install php-pdo php-mysqlnd php-mbstring php-json php-gd
```

---

## Installation Steps

### Step 1: Download and Extract

```bash
# If using Git
cd /path/to/htdocs
git clone [repository-url] sellandbuy

# Or extract from zip
unzip sellandbuy.zip -d /path/to/htdocs/
```

### Step 2: Install Dependencies

```bash
cd sellandbuy
composer install
```

This will install:
- TCPDF (PDF generation)
- PHPUnit (testing framework)

### Step 3: Database Setup

#### Create Database
```sql
CREATE DATABASE vente_groupe CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Import Schema
```bash
mysql -u your_user -p vente_groupe < database/vente_groupe.sql
```

#### Initialize Categories
```bash
php admin/init_categories.php
```

#### Initialize the "vente_groupe" module (additional tables)
```bash
php admin/init_vente_groupe.php
```

#### Generate ER Diagram (admin UI)
The application can generate a basic ER diagram from the live database schema. As an admin:

```bash
# View in browser
# Open: http://yourhost/index.php?controller=admin&action=erDiagram
# Download SVG: http://yourhost/index.php?controller=admin&action=downloadDiagram
```

### Step 4: File Permissions

```bash
# Create necessary directories
mkdir -p logs
mkdir -p public/images/uploads

# Set permissions (Linux/Mac)
chmod 755 public/images/uploads
chmod 755 logs

# For shared hosting, you may need 777
chmod 777 public/images/uploads
chmod 777 logs
```

### Step 5: Configuration

#### Copy Environment File
```bash
cp .env.example .env
```

#### Edit .env File
```ini
# Application
APP_NAME="Sell & Buy Marketplace"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com/sellandbuy

# Database
DB_HOST=localhost
DB_NAME=vente_groupe
DB_USER=your_database_user
DB_PASS=your_database_password
DB_CHARSET=utf8mb4

# Security
SESSION_LIFETIME=7200
CSRF_TOKEN_EXPIRE=3600
PASSWORD_MIN_LENGTH=8

# File Upload
MAX_UPLOAD_SIZE=5242880
ALLOWED_IMAGE_TYPES=jpg,jpeg,png,gif,webp
UPLOAD_PATH=public/images/uploads
```

#### Update config/database.php
```php
<?php
require_once __DIR__ . '/Config.php';

define('DB_HOST', Config::get('DB_HOST', 'localhost'));
define('DB_NAME', Config::get('DB_NAME', 'vente_groupe'));
define('DB_USER', Config::get('DB_USER', 'root'));
define('DB_PASS', Config::get('DB_PASS', ''));
define('DB_CHARSET', Config::get('DB_CHARSET', 'utf8mb4'));
```

#### Update config/constants.php
```php
<?php
require_once __DIR__ . '/Config.php';

define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('MODELS_PATH', ROOT_PATH . '/models');
define('HELPERS_PATH', ROOT_PATH . '/helpers');

define('BASE_URL', Config::get('APP_URL', 'http://localhost/sellandbuy'));
define('ASSETS_URL', BASE_URL . '/public');
```

### Step 6: Web Server Configuration

#### Apache (.htaccess)
Create/update `.htaccess` in root:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Redirect to HTTPS (if available)
    # RewriteCond %{HTTPS} off
    # RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Prevent access to sensitive files
    RewriteRule ^\.env - [F,L]
    RewriteRule ^\.git - [F,L]
    RewriteRule ^logs/ - [F,L]
    RewriteRule ^config/ - [F,L]
</IfModule>

# Disable directory listing
Options -Indexes

# Protect sensitive files
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>
```

#### Nginx
Add to your server block:
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/sellandbuy;
    index index.php;

    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ ^/(config|logs|admin)/ {
        deny all;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Step 7: Create Admin User

```bash
php helpers/create_admin.php
```

Follow the prompts to create an administrator account.

---

## Configuration

### Security Configuration

#### CSRF Protection
Already integrated into forms. To add to new forms:
```php
<?php require_once HELPERS_PATH . '/Security.php'; ?>
<form method="POST">
    <?= Security::csrfField() ?>
    <!-- form fields -->
</form>
```

Validate in controller:
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!Security::validateCsrfToken($token)) {
        die('CSRF token validation failed');
    }
    // Process form
}
```

#### Password Requirements
Update in `.env`:
```ini
PASSWORD_MIN_LENGTH=8
```

Passwords must contain:
- At least 8 characters (configurable)
- At least one uppercase letter
- At least one lowercase letter
- At least one number
- At least one special character

#### Rate Limiting
Implement in login/register:
```php
if (!Security::checkRateLimit('login', 5, 300)) {
    die('Too many attempts. Please try again later.');
}
```

### Upload Configuration

Update `.env`:
```ini
MAX_UPLOAD_SIZE=5242880          # 5MB in bytes
ALLOWED_IMAGE_TYPES=jpg,jpeg,png,gif,webp
UPLOAD_PATH=public/images/uploads
```

### Logging Configuration

Logs are automatically created in `logs/` directory:
- `app-YYYY-MM-DD.log` - Application logs
- `security-YYYY-MM-DD.log` - Security events

Usage:
```php
Logger::info('Product created', ['product_id' => $id]);
Logger::error('Database error', ['error' => $e->getMessage()]);
Logger::security('Failed login attempt', ['email' => $email]);
```

---

## Security Setup

### 1. HTTPS Configuration
**Highly Recommended for Production**

```bash
# Using Let's Encrypt (Free)
sudo apt-get install certbot python3-certbot-apache
sudo certbot --apache -d yourdomain.com
```

### 2. Database Security

```sql
-- Create dedicated database user
CREATE USER 'sellandbuy_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT SELECT, INSERT, UPDATE, DELETE ON vente_groupe.* TO 'sellandbuy_user'@'localhost';
FLUSH PRIVILEGES;
```

Update `.env`:
```ini
DB_USER=sellandbuy_user
DB_PASS=strong_password_here
```

### 3. Session Security

Add to php.ini or .htaccess:
```ini
; PHP.ini
session.cookie_httponly = 1
session.cookie_secure = 1        ; If using HTTPS
session.cookie_samesite = Strict
session.use_strict_mode = 1

; .htaccess
php_value session.cookie_httponly 1
php_value session.cookie_secure 1
php_value session.use_strict_mode 1
```

### 4. File Upload Security

Already implemented in ImageUpload class. Validates:
- File type (MIME type checking)
- File size
- File extension
- Image dimensions

### 5. Security Headers

Already implemented in index.php:
- X-Frame-Options: DENY
- X-Content-Type-Options: nosniff
- X-XSS-Protection: 1; mode=block
- Referrer-Policy: strict-origin-when-cross-origin

### 6. Backup Strategy

#### Automated Database Backup
```bash
#!/bin/bash
# backup.sh
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u user -p password vente_groupe > backup_$DATE.sql
gzip backup_$DATE.sql
# Keep only last 7 days
find . -name "backup_*.sql.gz" -mtime +7 -delete
```

Add to crontab:
```bash
crontab -e
# Add line:
0 2 * * * /path/to/backup.sh
```

---

## Feature Usage

### API Endpoints

#### Health Check
```bash
curl http://localhost/sellandbuy/index.php?controller=api&action=health
```

Response:
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "status": "ok",
    "timestamp": 1704672000,
    "version": "1.0.0"
  }
}
```

#### List Products
```bash
curl "http://localhost/sellandbuy/index.php?controller=api&action=products&page=1&limit=20"
```

#### Search Products
```bash
curl "http://localhost/sellandbuy/index.php?controller=api&action=search&q=laptop&category=1"
```

### Advanced Search

Use the search form with filters:
- Text search (product description, vendor name, category)
- Category filter
- Price range (min/max)
- Real-time search with debouncing

### Form Validation

Add `data-validate` attribute to forms:
```html
<form method="POST" data-validate>
    <input type="email" name="email" required>
    <input type="password" name="password" required minlength="8">
    <button type="submit">Submit</button>
</form>
```

JavaScript will automatically validate:
- Required fields
- Email format
- Minimum/maximum length
- Numeric values
- Password confirmation

### Toast Notifications

```javascript
// Show success message
Toast.show('Product created successfully!', 'success');

// Show error message
Toast.show('An error occurred', 'error');

// Show warning
Toast.show('Please review your input', 'warning');

// Show info
Toast.show('Processing your request...', 'info');
```

### Image Preview

```html
<div id="imagePreview"></div>
<input type="file" accept="image/*" id="productImage">

<script>
new ImagePreview(
    document.getElementById('productImage'),
    document.getElementById('imagePreview')
);
</script>
```

---

## Troubleshooting

### Common Issues

#### 1. "Database connection failed"
**Solution:**
- Check database credentials in `.env`
- Verify MySQL is running: `systemctl status mysql`
- Check user permissions: `SHOW GRANTS FOR 'user'@'localhost';`

#### 2. "Upload failed"
**Solution:**
- Check directory permissions: `ls -la public/images/uploads`
- Verify PHP upload settings: `php.ini` → `upload_max_filesize`, `post_max_size`
- Check disk space: `df -h`

#### 3. "CSRF token validation failed"
**Solution:**
- Clear browser cookies/cache
- Check session is starting: `session_status() === PHP_SESSION_ACTIVE`
- Verify session directory is writable

#### 4. "Class not found"
**Solution:**
- Run `composer install`
- Check file paths in `config/constants.php`
- Verify file exists: `ls -la helpers/Security.php`

#### 5. "Permission denied" errors
**Solution:**
```bash
# Fix file permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Fix upload directory
chmod 777 public/images/uploads
chmod 777 logs
```

### Debugging

#### Enable Debug Mode
In `.env`:
```ini
APP_DEBUG=true
```

In `index.php`:
```php
if (Config::get('APP_DEBUG', false)) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
```

#### Check Logs
```bash
# Application logs
tail -f logs/app-$(date +%Y-%m-%d).log

# Security logs
tail -f logs/security-$(date +%Y-%m-%d).log

# PHP error log
tail -f /var/log/php/error.log
```

#### Database Query Debugging
In models, add:
```php
try {
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
} catch (PDOException $e) {
    Logger::error('Database error', [
        'query' => $sql,
        'params' => $params,
        'error' => $e->getMessage()
    ]);
    throw $e;
}
```

---

## Maintenance

### Daily Tasks
- Monitor error logs
- Check system resources
- Review security logs

### Weekly Tasks
- Database backup verification
- Log file rotation
- Security updates check

### Monthly Tasks
- Full system backup
- Performance analysis
- Security audit
- Update dependencies: `composer update`

### Quarterly Tasks
- Database optimization: `OPTIMIZE TABLE tablename;`
- Clean old logs and uploads
- Review and update documentation

### Commands

#### Clear old logs
```bash
find logs/ -name "*.log" -mtime +30 -delete
```

#### Database optimization
```sql
OPTIMIZE TABLE Produit, Utilisateur, Categorie;
ANALYZE TABLE Produit, Utilisateur, Categorie;
```

#### Check disk usage
```bash
du -sh public/images/uploads
du -sh logs
```

#### Update composer dependencies
```bash
composer update --with-dependencies
```

---

## Production Deployment Checklist

- [ ] Database backup created
- [ ] .env file configured for production
- [ ] APP_DEBUG set to false
- [ ] HTTPS enabled
- [ ] File permissions set correctly
- [ ] CSRF protection enabled on all forms
- [ ] Security headers configured
- [ ] Error logging enabled
- [ ] Backup automation configured
- [ ] Monitoring setup
- [ ] Admin account created
- [ ] Test all critical features
- [ ] Documentation reviewed
- [ ] Rollback plan prepared

---

## Support

For issues and questions:
- Check logs first: `logs/app-YYYY-MM-DD.log`
- Review this guide
- Check the Statement of Work document
- Contact technical support

---

## Additional Resources

- [Statement of Work](STATEMENT_OF_WORK.md)
- [README](README.md)
- [PHP Documentation](https://www.php.net/docs.php)
- [OWASP Security Guidelines](https://owasp.org/)

---

**Last Updated:** January 7, 2026  
**Version:** 1.0
