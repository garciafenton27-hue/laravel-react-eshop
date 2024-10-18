# Docker Setup Guide

This guide covers how to set up OpenShop using Docker for development and production environments.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Quick Start](#quick-start)
- [Development Setup](#development-setup)
- [Production Setup](#production-setup)
- [Docker Containers](#docker-containers)
- [Configuration](#configuration)
- [Commands](#commands)
- [Troubleshooting](#troubleshooting)

## Prerequisites

- **Docker**: 20.10+ and Docker Compose 2.0+
- **RAM**: Minimum 4GB (8GB recommended)
- **Disk**: 20GB+ free space
- **Git**: For cloning the repository

## Quick Start

### 1. Clone Repository
```bash
git clone https://github.com/yourusername/ecomer.git
cd ecomer
```

### 2. Environment Setup
```bash
# Copy environment files
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env

# Edit environment variables as needed
nano backend/.env
```

### 3. Start Development Environment
```bash
docker-compose up -d
```

### 4. Access Applications
- **Frontend**: http://localhost:3000
- **Backend API**: http://localhost:8000
- **MySQL**: localhost:3306
- **Redis**: localhost:6379
- **MailHog**: http://localhost:8025

## Development Setup

### Docker Compose Development

The `docker-compose.yml` file includes:

- **MySQL 8.0**: Database server
- **Redis 7**: Cache and session storage
- **Laravel Backend**: PHP 8.2 with Nginx
- **React Frontend**: Node.js build with Nginx
- **Nginx Proxy**: Reverse proxy (optional)
- **MailHog**: Email testing

### Starting Development Environment

```bash
# Start all services
docker-compose up -d

# Start specific services
docker-compose up -d db redis backend

# View logs
docker-compose logs -f backend

# Stop all services
docker-compose down
```

### Database Setup

```bash
# Run migrations
docker-compose exec backend php artisan migrate

# Seed database
docker-compose exec backend php artisan db:seed

# Access MySQL shell
docker-compose exec db mysql -u openshop -p openshop
```

### Common Development Commands

```bash
# Install new PHP dependencies
docker-compose exec backend composer install

# Install new Node dependencies
docker-compose exec frontend npm install

# Clear Laravel cache
docker-compose exec backend php artisan cache:clear

# Generate application key
docker-compose exec backend php artisan key:generate

# Storage link
docker-compose exec backend php artisan storage:link
```

## Production Setup

### Docker Compose Production

The `docker-compose.prod.yml` file includes production-optimized services:

- **MySQL**: Optimized configuration
- **Redis**: Persistent storage
- **Laravel Backend**: Production build
- **React Frontend**: Production build
- **Nginx**: SSL termination, caching, security
- **Queue Worker**: Background job processing
- **Scheduler**: Laravel task scheduling

### Production Environment Setup

1. **Create Production Environment File**
```bash
cp backend/.env.example backend/.env.production
cp frontend/.env.example frontend/.env.production
```

2. **Configure Production Variables**
```bash
# backend/.env.production
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_DATABASE=openshop_prod
DB_USERNAME=openshop_user
DB_PASSWORD=secure_password

REDIS_HOST=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

MAIL_MAILER=smtp
MAIL_HOST=smtp.yourprovider.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_mail_password

RAZORPAY_KEY_ID=your_razorpay_key
RAZORPAY_KEY_SECRET=your_razorpay_secret
```

3. **Start Production Environment**
```bash
docker-compose -f docker-compose.prod.yml up -d
```

### SSL Certificate Setup

```bash
# Create SSL directory
mkdir -p docker/nginx/ssl

# Generate self-signed certificate (for testing)
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout docker/nginx/ssl/key.pem \
  -out docker/nginx/ssl/cert.pem

# Or use Let's Encrypt for production
certbot certonly --standalone -d yourdomain.com
cp /etc/letsencrypt/live/yourdomain.com/fullchain.pem docker/nginx/ssl/cert.pem
cp /etc/letsencrypt/live/yourdomain.com/privkey.pem docker/nginx/ssl/key.pem
```

### Production Optimization

```bash
# Optimize Laravel
docker-compose -f docker-compose.prod.yml exec backend php artisan config:cache
docker-compose -f docker-compose.prod.yml exec backend php artisan route:cache
docker-compose -f docker-compose.prod.yml exec backend php artisan view:cache

# Restart queue worker
docker-compose -f docker-compose.prod.yml restart queue
```

## Docker Containers

### Backend Container

**Base Image**: `php:8.2-fpm-alpine`

**Features**:
- PHP 8.2 with required extensions
- Composer for dependency management
- Nginx for web serving
- Supervisor for process management

**Customization**:
```dockerfile
# backend/Dockerfile
FROM php:8.2-fpm-alpine
# ... custom configuration
```

### Frontend Container

**Base Image**: `node:18-alpine` (build), `nginx:alpine` (production)

**Features**:
- Multi-stage build
- Optimized production build
- Nginx serving static files

**Customization**:
```dockerfile
# frontend/Dockerfile
FROM node:18-alpine as build-stage
# ... build process
FROM nginx:alpine as production-stage
# ... production setup
```

### Database Container

**Base Image**: `mysql:8.0`

**Features**:
- Optimized MySQL configuration
- Persistent data storage
- Binary logging for backups

**Customization**:
```ini
# docker/mysql/my.cnf
[mysqld]
innodb_buffer_pool_size=256M
max_connections=200
```

### Redis Container

**Base Image**: `redis:7-alpine`

**Features**:
- Memory optimization
- Persistence configuration
- Slow query logging

**Customization**:
```conf
# docker/redis/redis.conf
maxmemory 256mb
maxmemory-policy allkeys-lru
```

## Configuration

### Environment Variables

#### Backend (.env)
```bash
# Application
APP_NAME=OpenShop
APP_ENV=local
APP_KEY=base64:your_app_key
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=openshop
DB_USERNAME=openshop
DB_PASSWORD=password

# Cache
CACHE_DRIVER=redis
REDIS_HOST=redis
REDIS_PORT=6379

# Queue
QUEUE_CONNECTION=redis

# Mail
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
```

#### Frontend (.env)
```bash
VITE_API_URL=http://localhost:8000
VITE_RAZORPAY_KEY=your_razorpay_key
```

### Docker Compose Override

Create `docker-compose.override.yml` for local customizations:

```yaml
version: '3.8'

services:
  backend:
    volumes:
      - ./backend:/var/www
    environment:
      - APP_DEBUG=true
      - CACHE_DRIVER=file

  frontend:
    volumes:
      - ./frontend/src:/app/src
    environment:
      - VITE_API_URL=http://localhost:8000
```

## Commands

### Development Commands

```bash
# Start development environment
docker-compose up -d

# Build and start
docker-compose up --build -d

# View logs
docker-compose logs -f
docker-compose logs -f backend
docker-compose logs -f frontend

# Execute commands in containers
docker-compose exec backend bash
docker-compose exec frontend sh
docker-compose exec db mysql -u openshop -p

# Stop and remove containers
docker-compose down
docker-compose down -v  # Remove volumes
```

### Production Commands

```bash
# Start production environment
docker-compose -f docker-compose.prod.yml up -d

# Scale services
docker-compose -f docker-compose.prod.yml up -d --scale backend=2

# Update services
docker-compose -f docker-compose.prod.yml pull
docker-compose -f docker-compose.prod.yml up -d

# Backup database
docker-compose exec db mysqldump -u openshop openshop > backup.sql

# Restore database
docker-compose exec -T db mysql -u openshop openshop < backup.sql
```

### Maintenance Commands

```bash
# Clear Laravel cache
docker-compose exec backend php artisan cache:clear
docker-compose exec backend php artisan config:clear
docker-compose exec backend php artisan route:clear

# Run queue worker
docker-compose exec backend php artisan queue:work

# Schedule tasks
docker-compose exec backend php artisan schedule:run

# Optimize for production
docker-compose exec backend php artisan optimize
```

## Troubleshooting

### Common Issues

#### Container Won't Start
```bash
# Check logs
docker-compose logs service_name

# Check container status
docker-compose ps

# Rebuild container
docker-compose up --build -d service_name
```

#### Database Connection Issues
```bash
# Check MySQL container
docker-compose exec db mysql -u openshop -p

# Reset database
docker-compose down -v
docker-compose up -d db
docker-compose exec backend php artisan migrate:fresh --seed
```

#### Permission Issues
```bash
# Fix storage permissions
docker-compose exec backend chown -R www-data:www-data storage
docker-compose exec backend chmod -R 775 storage
```

#### Performance Issues
```bash
# Check resource usage
docker stats

# Optimize MySQL
docker-compose exec backend php artisan config:cache
docker-compose exec backend php artisan route:cache
```

### Debug Mode

Enable debug mode in development:

```bash
# backend/.env
APP_DEBUG=true
LOG_LEVEL=debug
```

View detailed logs:

```bash
# Laravel logs
docker-compose exec backend tail -f storage/logs/laravel.log

# Nginx logs
docker-compose exec backend tail -f /var/log/nginx/error.log

# MySQL logs
docker-compose exec db tail -f /var/log/mysql/error.log
```

### Health Checks

Monitor container health:

```bash
# Check container status
docker-compose ps

# Custom health check
curl http://localhost:8000/health
curl http://localhost:3000/health
```

## Best Practices

### Development
- Use volume mounts for hot reloading
- Enable debug mode for easier debugging
- Use MailHog for email testing
- Regular database backups

### Production
- Use read-only filesystems where possible
- Implement proper logging and monitoring
- Use secrets for sensitive data
- Regular security updates
- SSL/TLS encryption
- Rate limiting and firewall rules

### Security
- Don't commit secrets to version control
- Use environment variables for configuration
- Regular security scanning
- Limit container privileges
- Network segmentation

---

For additional help, check our [GitHub Issues](https://github.com/yourusername/ecomer/issues) or contact support@openshop.com
