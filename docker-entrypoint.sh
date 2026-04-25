#!/bin/sh
set -e

# Wait for hostname to become resolvable (helpful if Docker DNS is slow)
echo "Waiting for database hostname to resolve..."
MAX_RETRIES=10
COUNT=0
until ping -c 10.0.0.2   > /dev/null 2>&1 || [ $COUNT -eq $MAX_RETRIES ]; do
  echo "Hostname 10.0.0.2 not yet resolvable. Retrying in 2s ($COUNT/$MAX_RETRIES)..."
  sleep 2
  COUNT=$((COUNT + 1))
done

if [ $COUNT -eq $MAX_RETRIES ]; then
  echo "CRITICAL: Could not resolve database hostname. Please check Dokploy network settings."
fi

# Run migrations automatically
echo "Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing

# Execute the default Apache command
echo "Starting Apache server..."
exec apache2-foreground
