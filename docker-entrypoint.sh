#!/bin/sh
set -e

# Wait for database host/IP to become reachable
DB_HOST="10.0.0.2"
echo "Waiting for database host $DB_HOST to become reachable..."
MAX_RETRIES=15
COUNT=0

# Use netcat to check if the database port is open
# This is more reliable than ping for ensuring the service is actually ready
until nc -z $DB_HOST 3306 || [ $COUNT -eq $MAX_RETRIES ]; do
  echo "Database $DB_HOST:3306 not yet reachable. Retrying in 2s ($COUNT/$MAX_RETRIES)..."
  sleep 2
  COUNT=$((COUNT + 1))
done

if [ $COUNT -eq $MAX_RETRIES ]; then
  echo "WARNING: Could not reach database on $DB_HOST:3306. Attempting migrations anyway..."
fi

# Check for migrations
echo "Checking for migration files..."
MIGRATION_COUNT=$(ls migrations/Version*.php 2>/dev/null | wc -l)

if [ "$MIGRATION_COUNT" -eq 0 ]; then
  echo "No migrations found. Generating initial migration..."
  # We use --no-interaction and a dummy DATABASE_URL if needed, but since we are at runtime,
  # DATABASE_URL should be set in the env.
  php bin/console make:migration --no-interaction || echo "Failed to generate migration. Ensure database is reachable."
fi

# Run migrations automatically
echo "Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing

# Execute the default Apache command
echo "Starting Apache server..."
exec apache2-foreground
