#!/bin/bash

echo "Starting Laravel application..."

# Remove .env file to force Laravel to use Railway environment variables
if [ -f .env ]; then
    echo "Removing .env file to use Railway environment variables"
    rm .env
fi

echo "Environment variables from Railway:"
env | grep -E "(DB_|APP_|SUPABASE_|N8N_)" || echo "No DB/APP env vars found"

# Skip migrations for now to get app running
echo "Skipping migrations for now..."

# Start Laravel server
echo "Starting server on port ${PORT:-80}"
php artisan serve --host=0.0.0.0 --port=${PORT:-80}
