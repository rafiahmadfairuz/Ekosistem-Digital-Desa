#!/bin/sh
set -eu

# Named volumes can retain ownership from an earlier container run. Ensure
# Laravel's runtime directories remain writable by PHP-FPM without changing
# ownership of uploaded documents or other stored user files.
mkdir -p \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/storage/app/private \
    /var/www/html/storage/app/public \
    /var/www/html/bootstrap/cache

chown www-data:www-data \
    /var/www/html/storage \
    /var/www/html/storage/framework \
    /var/www/html/storage/framework/cache \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/storage/app \
    /var/www/html/storage/app/private \
    /var/www/html/storage/app/public \
    /var/www/html/bootstrap/cache

if [ -f /var/www/html/storage/logs/laravel.log ]; then
    chown www-data:www-data /var/www/html/storage/logs/laravel.log
fi

key_directory=/var/lib/desa-digital
mkdir -p "$key_directory"

ensure_key() {
    key_name="$1"
    current_value="$(printenv "$key_name" || true)"
    key_file="$key_directory/$key_name"

    if [ -z "$current_value" ]; then
        if [ ! -s "$key_file" ]; then
            php -r 'echo "base64:".base64_encode(random_bytes(32));' > "$key_file"
            chmod 600 "$key_file"
        fi

        current_value="$(cat "$key_file")"
        export "$key_name=$current_value"
    fi
}

ensure_key APP_KEY
ensure_key DATA_ENCRYPTION_KEY
ensure_key IDENTITY_BLIND_INDEX_KEY

exec "$@"
