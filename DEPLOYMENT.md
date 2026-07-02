# 🚀 Web-VPN Deployment Guide - SmartCityApps Production Server

## 📋 Prerequisites

### Server Requirements
- **OS**: Ubuntu 20.04+ or CentOS 7+
- **RAM**: Minimum 2GB (Recommended 4GB)
- **Storage**: Minimum 20GB
- **CPU**: 2 cores minimum

### Software Requirements
- Docker 20.10+
- Docker Compose 2.0+
- PostgreSQL 14+ (will be installed via Docker)
- Git
- SSL Certificate (Let's Encrypt recommended)

---

## 🔧 Step 1: Server Initial Setup

### 1.1 Update System Packages
```bash
# For Ubuntu/Debian
sudo apt update && sudo apt upgrade -y

# For CentOS/RHEL
sudo yum update -y
```

### 1.2 Install Required Dependencies
```bash
# For Ubuntu/Debian
sudo apt install -y curl git wget ufw

# For CentOS/RHEL
sudo yum install -y curl git wget
```

### 1.3 Setup Firewall
```bash
# Allow SSH
sudo ufw allow 22/tcp

# Allow HTTP
sudo ufw allow 80/tcp

# Allow HTTPS
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable
```

### 1.4 Create Deployment User (Optional but Recommended)
```bash
# Create user for deployment
sudo adduser webvpn

# Add to docker group (will be created after docker installation)
sudo usermod -aG docker webvpn

# Switch to deployment user
su - webvpn
```

---

## 🐳 Step 2: Install Docker and Docker Compose

### 2.1 Install Docker
```bash
# Download Docker installation script
curl -fsSL https://get.docker.com -o get-docker.sh

# Run installation script
sudo sh get-docker.sh

# Enable Docker service
sudo systemctl enable docker

# Start Docker service
sudo systemctl start docker

# Verify installation
docker --version
```

### 2.2 Install Docker Compose
```bash
# Download Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose

# Make executable
sudo chmod +x /usr/local/bin/docker-compose

# Verify installation
docker-compose --version
```

### 2.3 Add User to Docker Group
```bash
# Add current user to docker group
sudo usermod -aG docker $USER

# Apply group changes (logout and login or run)
newgrp docker

# Verify
docker ps
```

---

## 📥 Step 3: Clone Application Repository

### 3.1 Clone from GitLab
```bash
# Navigate to deployment directory
cd /opt

# Clone repository
sudo git clone https://git-smartcity.jakarta.go.id/gansrama/web-vpn.git

# Change ownership
sudo chown -R $USER:$USER /opt/web-vpn

# Navigate to project directory
cd /opt/web-vpn/typescript-version
```

### 3.2 Verify Repository
```bash
# Check git status
git status

# Check branches
git branch -a

# Switch to main branch if needed
git checkout main
```

---

## 🗄️ Step 4: Setup PostgreSQL Database

### 4.1 Create Docker Compose File
Create `docker-compose.yml` in `/opt/web-vpn/typescript-version/`:

```yaml
version: '3.8'

services:
  # PostgreSQL Database
  postgres:
    image: postgres:14-alpine
    container_name: webvpn-postgres
    restart: unless-stopped
    environment:
      POSTGRES_DB: web_vpn_production
      POSTGRES_USER: web_vpn_user
      POSTGRES_PASSWORD: ${DB_PASSWORD}
    volumes:
      - postgres_data:/var/lib/postgresql/data
      - ./docker/postgres/init:/docker-entrypoint-initdb.d
    ports:
      - "5432:5432"
    networks:
      - webvpn-network
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U web_vpn_user -d web_vpn_production"]
      interval: 10s
      timeout: 5s
      retries: 5

  # Redis (Optional - for caching and queues)
  redis:
    image: redis:7-alpine
    container_name: webvpn-redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    networks:
      - webvpn-network
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 5s
      retries: 5

  # Web Application
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: webvpn-app
    restart: unless-stopped
    ports:
      - "80:80"
    environment:
      DB_HOST: postgres
      DB_PORT: 5432
      DB_DATABASE: web_vpn_production
      DB_USERNAME: web_vpn_user
      DB_PASSWORD: ${DB_PASSWORD}
      REDIS_HOST: redis
      REDIS_PORT: 6379
      APP_ENV: production
      APP_DEBUG: false
      APP_URL: https://your-domain.com
    volumes:
      - ./storage:/var/www/storage
      - ./bootstrap/cache:/var/www/bootstrap/cache
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_healthy
    networks:
      - webvpn-network

volumes:
  postgres_data:
    driver: local

networks:
  webvpn-network:
    driver: bridge
```

### 4.2 Create Environment File
```bash
# Copy example environment file
cp .env.example .env

# Edit environment file
nano .env
```

Update the following variables in `.env`:
```bash
APP_NAME="Web-VPN"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database Configuration
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=web_vpn_production
DB_USERNAME=web_vpn_user
DB_PASSWORD=your_secure_password_here

# Redis Configuration (if using Redis)
REDIS_HOST=redis
REDIS_PORT=6379

# Cache Configuration
CACHE_STORE=redis
QUEUE_CONNECTION=redis

# Session Configuration
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# Log Configuration
LOG_CHANNEL=stack
LOG_LEVEL=error
```

### 4.3 Generate APP_KEY
```bash
# Generate application key
docker run --rm -v $(pwd):/app -w /app composer:latest php artisan key:generate

# Copy the generated key to your .env file
```

---

## 🔐 Step 5: Setup SSL Certificate with Let's Encrypt

### 5.1 Install Certbot
```bash
# For Ubuntu/Debian
sudo apt install -y certbot python3-certbot-nginx

# For CentOS/RHEL
sudo yum install -y certbot python3-certbot-nginx
```

### 5.2 Obtain SSL Certificate
```bash
# Replace with你的实际域名
sudo certbot certonly --standalone -d your-domain.com -d www.your-domain.com

# Certificate will be stored in:
# /etc/letsencrypt/live/your-domain.com/fullchain.pem
# /etc/letsencrypt/live/your-domain.com/privkey.pem
```

### 5.3 Setup Auto-Renewal
```bash
# Test renewal
sudo certbot renew --dry-run

# Setup cron job for auto-renewal
sudo crontab -e

# Add this line (runs daily at 2 AM)
0 2 * * * certbot renew --quiet --post-hook "docker-compose -f /opt/web-vpn/typescript-version/docker-compose.yml restart app"
```

---

## 🚀 Step 6: Build and Deploy Application

### 6.1 Build Docker Images
```bash
# Navigate to project directory
cd /opt/web-vpn/typescript-version

# Build and start services
docker-compose up -d --build
```

### 6.2 Run Database Migrations
```bash
# Run migrations inside the app container
docker-compose exec app php artisan migrate --force

# Seed database if needed
docker-compose exec app php artisan db:seed --force
```

### 6.3 Create Storage Symlink
```bash
# Create storage symlink
docker-compose exec app php artisan storage:link
```

### 6.4 Clear and Cache Configuration
```bash
# Clear all caches
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan view:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan cache:clear

# Cache configuration for production
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan view:cache
docker-compose exec app php artisan route:cache
```

---

## 🌐 Step 7: Setup Nginx Reverse Proxy (Optional but Recommended)

### 7.1 Install Nginx
```bash
# For Ubuntu/Debian
sudo apt install -y nginx

# For CentOS/RHEL
sudo yum install -y nginx

# Start Nginx
sudo systemctl start nginx
sudo systemctl enable nginx
```

### 7.2 Create Nginx Configuration
Create `/etc/nginx/sites-available/webvpn`:

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;

    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com www.your-domain.com;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Proxy to Docker container
    location / {
        proxy_pass http://localhost:80;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }

    # Increase upload size
    client_max_body_size 50M;
}
```

### 7.3 Enable Configuration
```bash
# Create symbolic link
sudo ln -s /etc/nginx/sites-available/webvpn /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

---

## 📊 Step 8: Setup Monitoring and Logging

### 8.1 View Application Logs
```bash
# View app logs
docker-compose logs -f app

# View database logs
docker-compose logs -f postgres

# View all logs
docker-compose logs -f
```

### 8.2 Setup Log Rotation
Create `/etc/logrotate.d/webvpn`:
```
/opt/web-vpn/typescript-version/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0644 webvpn webvpn
    sharedscripts
}
```

### 8.3 Monitor Container Health
```bash
# Check container status
docker-compose ps

# Check container health
docker inspect webvpn-app | grep -A 10 Health

# View resource usage
docker stats
```

---

## 🔧 Step 9: Setup Systemd Service (Optional)

### 9.1 Create Systemd Service File
Create `/etc/systemd/system/webvpn.service`:
```ini
[Unit]
Description=Web-VPN Docker Compose Service
Requires=docker.service
After=docker.service

[Service]
Type=oneshot
RemainAfterExit=yes
WorkingDirectory=/opt/web-vpn/typescript-version
ExecStart=/usr/local/bin/docker-compose up -d
ExecStop=/usr/local/bin/docker-compose down
TimeoutStartSec=0

[Install]
WantedBy=multi-user.target
```

### 9.2 Enable and Start Service
```bash
# Reload systemd
sudo systemctl daemon-reload

# Enable service
sudo systemctl enable webvpn

# Start service
sudo systemctl start webvpn

# Check status
sudo systemctl status webvpn
```

---

## 🔄 Step 10: Update and Maintenance

### 10.1 Update Application
```bash
# Navigate to project directory
cd /opt/web-vpn/typescript-version

# Pull latest changes
git pull origin main

# Rebuild and restart
docker-compose up -d --build

# Run migrations
docker-compose exec app php artisan migrate --force

# Clear and cache
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan view:cache
docker-compose exec app php artisan route:cache
```

### 10.2 Backup Database
```bash
# Backup database
docker-compose exec postgres pg_dump -U web_vpn_user web_vpn_production > backup_$(date +%Y%m%d_%H%M%S).sql

# Restore database
docker-compose exec -T postgres psql -U web_vpn_user web_vpn_production < backup_file.sql
```

### 10.3 Backup Application Files
```bash
# Create backup script
cat > /opt/backup-webvpn.sh << 'EOF'
#!/bin/bash
BACKUP_DIR="/opt/backups/webvpn"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR

# Backup database
docker-compose exec postgres pg_dump -U web_vpn_user web_vpn_production > $BACKUP_DIR/db_$DATE.sql

# Backup application files
tar -czf $BACKUP_DIR/app_$DATE.tar.gz /opt/web-vpn/typescript-version

# Keep only last 7 days
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
EOF

# Make executable
chmod +x /opt/backup-webvpn.sh

# Add to cron (daily at 3 AM)
0 3 * * * /opt/backup-webvpn.sh
```

---

## ✅ Step 11: Final Verification

### 11.1 Health Check
```bash
# Check if containers are running
docker-compose ps

# Check application logs for errors
docker-compose logs app | tail -50

# Test database connection
docker-compose exec app php artisan tinker
>>> DB::connection()->getPdo();
>>> exit
```

### 11.2 Test Application
- Open browser and navigate to `https://your-domain.com`
- Test all main features
- Check error logs: `docker-compose logs app`
- Check Laravel logs: `docker-compose exec app tail -f storage/logs/laravel.log`

---

## 🔒 Security Recommendations

### 12.1 Security Hardening
```bash
# Disable root SSH login
sudo sed -i 's/PermitRootLogin yes/PermitRootLogin no/' /etc/ssh/sshd_config
sudo systemctl restart sshd

# Setup fail2ban
sudo apt install -y fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban

# Regular security updates
sudo apt install -y unattended-upgrades
sudo dpkg-reconfigure -plow unattended-upgrades
```

### 12.2 Environment Security
- Never commit `.env` file to version control
- Use strong passwords for database
- Rotate secrets regularly
- Keep dependencies updated
- Monitor security advisories

---

## 📞 Troubleshooting

### Common Issues

#### Container won't start
```bash
# Check logs
docker-compose logs app

# Check container status
docker-compose ps

# Restart services
docker-compose restart
```

#### Database connection failed
```bash
# Check if postgres is running
docker-compose ps postgres

# Check postgres logs
docker-compose logs postgres

# Test connection
docker-compose exec app php artisan tinker
>>> DB::connection()->getPdo();
```

#### Permission issues
```bash
# Fix storage permissions
sudo chown -R $USER:$USER storage bootstrap/cache
chmod -R 755 storage bootstrap/cache
```

#### Port conflicts
```bash
# Check what's using port 80
sudo netstat -tlnp | grep :80

# Change port in docker-compose.yml if needed
```

---

## 📝 Maintenance Schedule

### Daily
- Monitor application logs
- Check container health
- Verify backups ran successfully

### Weekly
- Review security logs
- Check disk space usage
- Review error logs

### Monthly
- Update dependencies
- Review and update SSL certificates
- Security audit
- Performance review

### Quarterly
- Full security audit
- Disaster recovery testing
- Capacity planning

---

## 🆘 Support

For issues or questions:
- Check Laravel logs: `storage/logs/laravel.log`
- Check Docker logs: `docker-compose logs`
- Check Nginx logs: `/var/log/nginx/`
- Database documentation: https://www.postgresql.org/docs/

---

## 📚 Additional Resources

- Laravel Documentation: https://laravel.com/docs
- Docker Documentation: https://docs.docker.com
- PostgreSQL Documentation: https://www.postgresql.org/docs/
- Nginx Documentation: https://nginx.org/en/docs/
