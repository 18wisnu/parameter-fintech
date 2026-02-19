#!/bin/bash

# Exit on error
set -e

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Optimize Laravel
echo "Optimizing Laravel..."
php artisan optimize

# Execute the CMD
exec "$@"
