# Deployment Guide

This guide covers deploying the Self Order Laravel application to production environments.

## Prerequisites

### Server Requirements
- **OS**: Ubuntu 20.04 LTS or newer
- **PHP**: 8.2 or higher
- **Node.js**: 18.0 or higher
- **MySQL**: 8.0 or higher
- **Redis**: 6.0 or higher (optional but recommended)
- **Nginx**: Latest stable version
- **SSL Certificate**: Required for production

### Hardware Requirements
- **CPU**: 2+ cores
- **RAM**: 4GB minimum, 8GB recommended
- **Storage**: 20GB minimum, SSD recommended
- **Network**: Stable internet connection

## Server Setup

### 1. Update System
```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Install PHP and Extensions
```bash
sudo apt install php8.2-fpm php8.2-mysql php8.2-redis php8.2-xml php8.2-curl php8.2-gd php8.2-mbstring php8.2-zip php8.2-intl php8.2-bcmath -y
```

### 3. Install MySQL
```bash
sudo apt install mysql-server -y
sudo mysql_secure_installation
```

### 4. Install Redis
```bash
sudo apt install redis-server -y
sudo systemctl enable redis-server
```

### 5. Install Nginx
```bash
sudo apt install nginx -y
sudo systemctl enable nginx
```

### 6. Install Node.js
```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y
```

### 7. Install Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

## Application Deployment

### 1. Clone Repository
```bash
cd /var/www
sudo git clone https://github.com/your-username/self-order-laravel.git
sudo chown -R www-data:www-data self-order-laravel
cd self-order-laravel
```

### 2. Install Dependencies
```bash
# PHP dependencies
composer install --optimize-autoloader --no-dev

# Node.js dependencies
npm ci --only=production
```

### 3. Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure environment variables
nano .env
```

### 4. Environment Variables
```env
APP_NAME="UMKM Restaurant"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=self_order_production
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your-pusher-app-id
PUSHER_APP_KEY=your-pusher-key
PUSHER_APP_SECRET=your-pusher-secret
PUSHER_APP_CLUSTER=your-cluster

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls

# Printing Configuration
CUSTOMER_PRINTER_ENABLED=true
CUSTOMER_PRINTER_TYPE=network
CUSTOMER_PRINTER_HOST=192.168.1.100
CUSTOMER_PRINTER_PORT=9100

KITCHEN_PRINTER_ENABLED=true
KITCHEN_PRINTER_TYPE=network
KITCHEN_PRINTER_HOST=192.168.1.101
KITCHEN_PRINTER_PORT=9100
```

### 5. Database Setup
```bash
# Create database
mysql -u root -p
CREATE DATABASE self_order_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'your_db_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON self_order_production.* TO 'your_db_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Run migrations and seeders
php artisan migrate --force
php artisan db:seed --force
```

### 6. Build Assets
```bash
npm run build
```

### 7. Storage and Permissions
```bash
# Create storage link
php artisan storage:link

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 8. Optimize Application
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Generate API documentation
php artisan l5-swagger:generate
```

## Web Server Configuration

### Nginx Configuration
Create `/etc/nginx/sites-available/self-order-laravel`:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/self-order-laravel/public;

    # SSL Configuration
    ssl_certificate /path/to/your/certificate.crt;
    ssl_certificate_key /path/to/your/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    index index.php;

    charset utf-8;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/javascript;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/self-order-laravel /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## Process Management

### Supervisor Configuration
Create `/etc/supervisor/conf.d/self-order-laravel.conf`:

```ini
[program:self-order-laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/self-order-laravel/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/self-order-laravel/storage/logs/worker.log
stopwaitsecs=3600

[program:self-order-laravel-scheduler]
process_name=%(program_name)s
command=/bin/bash -c 'while true; do php /var/www/self-order-laravel/artisan schedule:run --verbose --no-interaction & sleep 60; done'
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/self-order-laravel/storage/logs/scheduler.log
```

Start Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

## SSL Certificate

### Using Let's Encrypt
```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

### Auto-renewal
```bash
sudo crontab -e
# Add this line:
0 12 * * * /usr/bin/certbot renew --quiet
```

## Monitoring and Logging

### Log Rotation
Create `/etc/logrotate.d/self-order-laravel`:

```
/var/www/self-order-laravel/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
```

### Performance Monitoring
```bash
# Add to crontab for regular performance monitoring
0 */6 * * * cd /var/www/self-order-laravel && php artisan performance:monitor --report
```

## Backup Strategy

### Database Backup Script
Create `/usr/local/bin/backup-database.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/self-order-laravel"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="self_order_production"
DB_USER="your_db_user"
DB_PASS="your_secure_password"

mkdir -p $BACKUP_DIR

mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/database_$DATE.sql.gz

# Keep only last 30 days of backups
find $BACKUP_DIR -name "database_*.sql.gz" -mtime +30 -delete
```

### Application Backup Script
Create `/usr/local/bin/backup-application.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/self-order-laravel"
DATE=$(date +%Y%m%d_%H%M%S)
APP_DIR="/var/www/self-order-laravel"

mkdir -p $BACKUP_DIR

# Backup storage directory
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz -C $APP_DIR storage

# Keep only last 7 days of storage backups
find $BACKUP_DIR -name "storage_*.tar.gz" -mtime +7 -delete
```

### Automated Backups
```bash
sudo chmod +x /usr/local/bin/backup-*.sh
sudo crontab -e
# Add these lines:
0 2 * * * /usr/local/bin/backup-database.sh
0 3 * * * /usr/local/bin/backup-application.sh
```

## Security Hardening

### Firewall Configuration
```bash
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### Fail2Ban
```bash
sudo apt install fail2ban -y
sudo systemctl enable fail2ban
```

### Regular Updates
```bash
# Add to crontab for automatic security updates
0 4 * * * apt update && apt upgrade -y
```

## Troubleshooting

### Common Issues

1. **Permission Errors**
   ```bash
   sudo chown -R www-data:www-data /var/www/self-order-laravel
   sudo chmod -R 775 storage bootstrap/cache
   ```

2. **Queue Not Processing**
   ```bash
   sudo supervisorctl restart self-order-laravel-worker:*
   ```

3. **Cache Issues**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

4. **Database Connection Issues**
   - Check MySQL service: `sudo systemctl status mysql`
   - Verify credentials in `.env`
   - Test connection: `php artisan tinker` then `DB::connection()->getPdo()`

### Log Locations
- Application logs: `/var/www/self-order-laravel/storage/logs/`
- Nginx logs: `/var/log/nginx/`
- PHP-FPM logs: `/var/log/php8.2-fpm.log`
- MySQL logs: `/var/log/mysql/`

## Maintenance

### Regular Tasks
- Monitor disk space and clean old logs
- Update dependencies monthly
- Review and rotate API keys quarterly
- Test backup restoration procedures
- Monitor application performance metrics
- Review security logs for suspicious activity

### Performance Optimization
- Enable OPcache in production
- Use Redis for session storage
- Implement CDN for static assets
- Monitor and optimize database queries
- Set up database read replicas for high traffic
