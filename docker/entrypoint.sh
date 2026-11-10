#!/bin/sh
set -eu

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
