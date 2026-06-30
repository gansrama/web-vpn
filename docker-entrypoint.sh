#!/bin/bash

# Wait for database connection if needed
# Run migrations at runtime when env vars are available
php artisan migrate --force || echo "Migrations failed or already run"

# Start Laravel server
php artisan serve --host=0.0.0.0 --port=${PORT:-80}
