#!/bin/sh
set -e

if [ -n "$USE_PATH_PARAM" ] && [ "$USE_PATH_PARAM" != "0" ]; then
  sed -i "11s/^# //" .htaccess
  sed -i "12s/^# //" .htaccess

  sed -i "14s/^[^#]/# &/" .htaccess
fi

exec docker-php-entrypoint "$@"
