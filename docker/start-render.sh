#!/bin/sh

set -eu

fail() {
    echo "Render startup error: $1" >&2
    exit 1
}

: "${APP_KEY:?Render startup error: APP_KEY must be set persistently in Render.}"
: "${APP_URL:?Render startup error: APP_URL must be set to the public HTTPS URL.}"
: "${DATABASE_URL:?Render startup error: DATABASE_URL must be the Render internal database URL.}"

[ "${APP_ENV:-}" = "production" ] || fail "APP_ENV must be production."
[ "${APP_DEBUG:-}" = "false" ] || fail "APP_DEBUG must be false."
[ "${DB_CONNECTION:-}" = "pgsql" ] || fail "DB_CONNECTION must be pgsql."

case "$APP_URL" in
    https://*) ;;
    *) fail "APP_URL must use https://." ;;
esac

case "$DATABASE_URL" in
    postgres://*|postgresql://*) ;;
    *) fail "DATABASE_URL must be a PostgreSQL URL." ;;
esac

PORT="${PORT:-10000}"
case "$PORT" in
    *[!0-9]*|'') fail "PORT must be a numeric TCP port." ;;
esac
export PORT

printf 'Listen 0.0.0.0:%s\n' "$PORT" > /etc/apache2/ports.conf
sed "s/__PORT__/$PORT/g" \
    /etc/apache2/sites-available/000-default.conf.template \
    > /etc/apache2/sites-available/000-default.conf

php artisan config:clear --no-interaction
php artisan route:clear --no-interaction
php artisan view:clear --no-interaction

php artisan app:wait-for-database \
    --attempts="${DB_WAIT_ATTEMPTS:-20}" \
    --delay="${DB_WAIT_DELAY_SECONDS:-3}" \
    --no-interaction

if ! php artisan migrate --force --no-interaction; then
    fail "database migrations failed; the web server was not started."
fi

php artisan config:cache --no-interaction
php artisan view:cache --no-interaction

exec apache2-foreground
