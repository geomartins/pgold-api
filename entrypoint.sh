#!/bin/sh
set -e

### Step 1: Prepare Laravel storage
mkdir -p \
  /var/www/html/storage/app/public \
  /var/www/html/storage/logs \
  /var/www/html/storage/framework/sessions \
  /var/www/html/storage/framework/cache \
  /var/www/html/storage/framework/views \
  /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

### Step 2: Configure SQLite
echo "🗃️ Configuring SQLite database..."
mkdir -p /var/www/html/database/sqlite

SQLITE_PATH="/var/www/html/database/sqlite/database.sqlite"

if [ ! -f "$SQLITE_PATH" ]; then
  touch "$SQLITE_PATH"
  echo "✅ Created new SQLite database file at $SQLITE_PATH"
else
  echo "ℹ️ Existing SQLite database found at $SQLITE_PATH"
fi

chown -R www-data:www-data /var/www/html/database/sqlite
chmod -R 775 /var/www/html/database/sqlite

echo "📂 SQLite directory content:"
ls -l /var/www/html/database/sqlite

### Step 3: Laravel cache clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan optimize:clear

### Step 4: Migrate
php artisan migrate --force

### Step 5: Reapply permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database/sqlite
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database/sqlite

### Step 6: Start Supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
