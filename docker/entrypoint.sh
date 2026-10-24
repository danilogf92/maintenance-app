#!/bin/sh
set -eu
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache storage/app/public storage/app/private
if [ "$(id -u)" = "0" ]; then
    # Runtime caches live on Linux volumes. Do not traverse all uploaded files on every command.
    chown www-data:www-data storage storage/app storage/app/public storage/app/private
    chown -R www-data:www-data storage/framework storage/logs bootstrap/cache
fi
exec "$@"
