#!/bin/sh
set -e
if [ "$RUN_MIGRATIONS" = "true" ]; then
  php artisan migrate --force
fi
php artisan storage:link 2>/dev/null || true
php artisan optimize
exec "$@"