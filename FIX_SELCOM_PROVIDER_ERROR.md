# Fix: Class "Bryceandy\Selcom\SelcomBaseServiceProvider" not found

This error appears when Laravel’s **cached** package list still references the removed `bryceandy/laravel-selcom` package.

## Fix (run on the machine where the error appears)

### Option 1: Use the script (recommended)

```bash
chmod +x clear-bootstrap-cache.sh
./clear-bootstrap-cache.sh
```

### Option 2: Run commands manually

```bash
# 1. Remove cached package/service list
rm -f bootstrap/cache/packages.php
rm -f bootstrap/cache/services.php
rm -f bootstrap/cache/config.php

# 2. Regenerate
composer dump-autoload
php artisan package:discover --ansi
```

### Option 3: On Windows (PowerShell)

```powershell
Remove-Item -Force bootstrap/cache/packages.php -ErrorAction SilentlyContinue
Remove-Item -Force bootstrap/cache/services.php -ErrorAction SilentlyContinue
Remove-Item -Force bootstrap/cache/config.php -ErrorAction SilentlyContinue
composer dump-autoload
php artisan package:discover --ansi
```

After this, load the app again; the error should be gone.

## Why it happens

Laravel caches discovered packages in `bootstrap/cache/`. After removing the Selcom package, that cache can still list `Bryceandy\Selcom\SelcomBaseServiceProvider`. Deleting the cache and running `package:discover` rebuilds the list without the old package.
