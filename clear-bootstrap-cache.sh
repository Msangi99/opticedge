#!/usr/bin/env bash
# Run this after removing bryceandy/laravel-selcom to fix:
#   Class "Bryceandy\Selcom\SelcomBaseServiceProvider" not found
# Usage: ./clear-bootstrap-cache.sh   or   bash clear-bootstrap-cache.sh

set -e
cd "$(dirname "$0")"

echo "Removing Laravel bootstrap cache files..."
rm -f bootstrap/cache/packages.php
rm -f bootstrap/cache/services.php
rm -f bootstrap/cache/config.php

echo "Regenerating autoload and package discovery..."
composer dump-autoload
php artisan package:discover --ansi

echo "Done. Try loading your app again."
