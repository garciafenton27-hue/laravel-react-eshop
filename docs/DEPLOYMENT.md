# Deployment Guide

This guide covers how to deploy OpenShop to various environments.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Environment Setup](#environment-setup)
- [Frontend Deployment](#frontend-deployment)
- [Backend Deployment](#backend-deployment)
- [Database Setup](#database-setup)
- [Environment Variables](#environment-variables)
- [SSL Configuration](#ssl-configuration)
- [Monitoring](#monitoring)
- [Troubleshooting](#troubleshooting)

## Prerequisites

### System Requirements

- **PHP**: 8.2 or higher
- **MySQL**: 8.0 or higher
- **Node.js**: 18 or higher
- **Nginx**: 1.18 or higher (for production)
- **Composer**: 2.0 or higher
- **SSL Certificate** (for production)

### Server Requirements

- **Minimum RAM**: 2GB
- **Recommended RAM**: 4GB+
- **Storage**: 20GB+ SSD
- **CPU**: 2+ cores

## Environment Setup

### Production Environment

```bash
# Clone the repository
git clone https://github.com/yourusername/ecomer.git
cd ecomer

# Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Frontend Deployment

### Vercel (Recommended)

1. **Connect Repository**
   ```bash
   # Install Vercel CLI
   npm i -g vercel

   # Deploy
   cd frontend
   vercel --prod
   ```

2. **Environment Variables**
   ```
   VITE_API_URL=https://your-api-domain.com
   VITE_RAZORPAY_KEY=your_razorpay_key
   ```

3. **Automatic Deployment**
   - Connect GitHub to Vercel
   - Set build command: `npm run build`
   - Set output directory: `dist`
   - Deploy on push to main branch

### Netlify Alternative

1. **Build Settings**
   - Build command: `cd frontend && npm run build`
   - Publish directory: `frontend/dist`
   - Node version: 18

2. **Environment Variables**
   ```
   VITE_API_URL=https://your-api-domain.com
   ```

## Backend Deployment

### Shared Hosting

1. **Upload Files**
   ```bash
   # Upload all files to public_html
   # Ensure .env is in the root
   ```

2. **Configure .env**
   ```bash
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com
   ```

3. **Install Dependencies**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

4. **Run Setup Commands**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan migrate --force
   ```

### VPS/Dedicated Server

1. **Install Dependencies**
   ```bash
   # Ubuntu/Debian
   sudo apt update
   sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath
   sudo apt install nginx mysql-server composer
   ```

2. **Configure Nginx**
   ```nginx
   server {
       listen 80;
       server_name yourdomain.com;
       root /var/www/ecomer/public;
       index index.php;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
           fastcgi_index index.php;
           include fastcgi_params;
           fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
       }
   }
   ```

3. **SSL with Let's Encrypt**
   ```bash
   sudo apt install certbot python3-certbot-nginx
   sudo certbot --nginx -d yourdomain.com
   ```

### Docker Deployment

1. **Create Dockerfile**
   ```dockerfile
   FROM php:8.2-fpm
   
   WORKDIR /var/www
   
   RUN apt-get update && apt-get install -y \
       git \
       curl \
       libpng-dev \
       libonig-dev \
       libxml2-dev \
       zip \
       unzip
   
   RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd
   
   COPY . .
   
   RUN composer install --no-dev --optimize-autoloader
   
   RUN chown -R www-data:www-data /var/www
   
   CMD ["php-fpm"]
   ```

2. **Docker Compose**
   ```yaml
   version: '3'
   services:
     app:
       build: .
       container_name: openshop-app
       restart: unless-stopped
       working_dir: /var/www
       volumes:
         - ./:/var/www
       networks:
         - openshop-network
   
     db:
       image: mysql:8.0
       container_name: openshop-db
       restart: unless-stopped
       environment:
         MYSQL_DATABASE: openshop
         MYSQL_ROOT_PASSWORD: password
         MYSQL_PASSWORD: password
         MYSQL_USER: openshop
       volumes:
         - dbdata:/var/lib/mysql
       networks:
         - openshop-network
   
     nginx:
       image: nginx:alpine
       container_name: openshop-nginx
       restart: unless-stopped
       ports:
         - "80:80"
         - "443:443"
       volumes:
         - ./:/var/www
         - ./nginx.conf:/etc/nginx/nginx.conf
       networks:
         - openshop-network
   
   networks:
     openshop-network:
       driver: bridge
   
   volumes:
     dbdata:
       driver: local
   ```

## Database Setup

### MySQL Configuration

1. **Create Database**
   ```sql
   CREATE DATABASE openshop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'openshop'@'localhost' IDENTIFIED BY 'secure_password';
   GRANT ALL PRIVILEGES ON openshop.* TO 'openshop'@'localhost';
   FLUSH PRIVILEGES;
   ```

2. **Import Database**
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```

3. **Optimize MySQL**
   ```sql
   -- Add to my.cnf
   innodb_buffer_pool_size = 1G
   innodb_log_file_size = 256M
   query_cache_size = 64M
   ```

## Environment Variables

### Backend (.env)

```bash
# Application
APP_NAME=OpenShop
APP_ENV=production
APP_KEY=base64:your_app_key
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=openshop
DB_USERNAME=openshop
DB_PASSWORD=secure_password

# Cache
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@openshop.com
MAIL_FROM_NAME="${APP_NAME}"

# Storage
FILESYSTEM_DISK=public

# Payment
RAZORPAY_KEY_ID=your_razorpay_key
RAZORPAY_KEY_SECRET=your_razorpay_secret
```

### Frontend (.env.production)

```bash
VITE_API_URL=https://your-api-domain.com
VITE_RAZORPAY_KEY=your_razorpay_key
```

## SSL Configuration

### Let's Encrypt

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Get SSL Certificate
sudo certbot --nginx -d yourdomain.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

### Manual SSL

1. **Generate CSR**
   ```bash
   openssl req -new -newkey rsa:2048 -nodes -keyout openshop.key -out openshop.csr
   ```

2. **Configure Nginx**
   ```nginx
   server {
       listen 443 ssl http2;
       server_name yourdomain.com;
       
       ssl_certificate /path/to/openshop.crt;
       ssl_certificate_key /path/to/openshop.key;
       
       ssl_protocols TLSv1.2 TLSv1.3;
       ssl_ciphers HIGH:!aNULL:!MD5;
   }
   ```

## Monitoring

### Application Monitoring

1. **Laravel Telescope**
   ```bash
   composer require laravel/telescope
   php artisan telescope:install
   ```

2. **Error Tracking**
   - Sentry for error monitoring
   - Bugsnag for exception tracking

3. **Performance Monitoring**
   - New Relic for APM
   - Datadog for metrics

### Server Monitoring

1. **System Metrics**
   ```bash
   # Install htop
   sudo apt install htop
   
   # Monitor with htop
   htop
   ```

2. **Log Monitoring**
   ```bash
   # Tail Laravel logs
   tail -f storage/logs/laravel.log
   
   # Monitor Nginx logs
   tail -f /var/log/nginx/access.log
   tail -f /var/log/nginx/error.log
   ```

## Troubleshooting

### Common Issues

1. **500 Internal Server Error**
   ```bash
   # Check permissions
   chmod -R 755 storage bootstrap/cache
   
   # Check logs
   tail -f storage/logs/laravel.log
   ```

2. **Database Connection Failed**
   ```bash
   # Test MySQL connection
   mysql -u openshop -p openshop
   
   # Check .env database settings
   php artisan config:cache
   ```

3. **Frontend Build Errors**
   ```bash
   # Clear node modules
   rm -rf node_modules package-lock.json
   npm install
   
   # Clear build cache
   npm run build -- --reset-cache
   ```

### Performance Optimization

1. **Enable OPcache**
   ```ini
   ; php.ini
   opcache.enable=1
   opcache.memory_consumption=128
   opcache.max_accelerated_files=4000
   ```

2. **Configure Redis**
   ```bash
   # Install Redis
   sudo apt install redis-server
   
   # Configure Laravel
   CACHE_DRIVER=redis
   SESSION_DRIVER=redis
   ```

3. **CDN for Assets**
   ```bash
   # Use CDN for static assets
   ASSET_URL=https://cdn.yourdomain.com
   ```

## Backup Strategy

### Database Backup

```bash
# Daily backup script
#!/bin/bash
DATE=$(date +%Y%m%d)
mysqldump -u openshop -p openshop > /backups/openshop_$DATE.sql
find /backups -name "openshop_*.sql" -mtime +7 -delete
```

### File Backup

```bash
# Backup storage files
rsync -av /var/www/ecomer/storage/ /backups/storage/
```

## Security Hardening

1. **Firewall Configuration**
   ```bash
   sudo ufw enable
   sudo ufw allow ssh
   sudo ufw allow 'Nginx Full'
   ```

2. **Fail2Ban**
   ```bash
   sudo apt install fail2ban
   sudo systemctl enable fail2ban
   ```

3. **Security Headers**
   ```nginx
   add_header X-Frame-Options "SAMEORIGIN" always;
   add_header X-XSS-Protection "1; mode=block" always;
   add_header X-Content-Type-Options "nosniff" always;
   ```

---

For additional help, contact our support team at support@openshop.com
