#!/bin/bash

echo "Starting Laravel application..."
echo "Environment variables:"
env | grep -E "(DB_|APP_|SUPABASE_|N8N_)" || echo "No DB/APP env vars found"

# Skip migrations for now to get app running
echo "Skipping migrations for now..."

# Start Laravel server
echo "Starting server on port ${PORT:-80}"
php artisan serve --host=0.0.0.0 --port=${PORT:-80}
