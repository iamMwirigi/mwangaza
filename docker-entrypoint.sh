#!/bin/sh
set -e

# Run migrations automatically
echo "Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing

# Execute the default Apache command
echo "Starting Apache server..."
exec apache2-foreground
