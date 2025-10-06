#!/bin/sh
set -e


### Step 2: Make Directories if not exist
mkdir -p \
  /var/www/html/storage/app/public \
  /var/www/html/storage/logs \
  /var/www/html/storage/framework/sessions \
  /var/www/html/storage/framework/cache \
  /var/www/html/storage/framework/views \
  /var/www/html/bootstrap/cache

#### Step 3: Set storage directories permission to www-data
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

##### Step 4: Linking Laravel Storage
echo "🔗 Ensuring Laravel storage symlink exists..."
if [ ! -L "public/storage" ]; then
  php artisan storage:link
fi

### Step 1.5: Setup SQLite database
echo "🗃️ Configuring SQLite database..."
mkdir -p /var/www/html/database/sqlite

SQLITE_PATH="/var/www/html/database/sqlite/database.sqlite"

# Create SQLite file if it doesn't exist (volume will persist it)
if [ ! -f "$SQLITE_PATH" ]; then
  touch "$SQLITE_PATH"
  chown www-data:www-data "$SQLITE_PATH"
  chmod 664 "$SQLITE_PATH"
  echo "✅ Created new SQLite database file at $SQLITE_PATH"
else
  echo "ℹ️ Existing SQLite database found at $SQLITE_PATH"
fi

##### Step 5: Clearing Laravel cache
echo "✅ Clearing Laravel cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan optimize:clear


##### Step 6: Create Queue-job migration if not exist
if ! grep -q "Schema::create('jobs'" database/migrations/*.php 2>/dev/null; then
  echo "🛠️ Queue migration files not found. Generating..."
  php artisan queue:table
else
  echo "✅ 'jobs' table migration already exists. Skipping queue:table."
fi

if ! grep -q "Schema::create('failed_jobs'" database/migrations/*.php 2>/dev/null; then
  echo "🛠️ Failed jobs migration not found. Generating..."
  php artisan queue:failed-table
else
  echo "✅ 'failed_jobs' table migration already exists. Skipping queue:failed-table."
fi



##### Step 7: Migrate 
# php artisan migrate --force




### Step 8: Create Laravel Passport key if not exist and if using Passport
if grep -q '"laravel/passport"' composer.json; then
  echo "🔐 Laravel Passport detected. Ensuring Passport keys exist..."
  if [ ! -f storage/oauth-private.key ] || [ ! -f storage/oauth-public.key ]; then
    php artisan passport:keys
  else
    echo "✅ Passport keys already exist. Skipping passport:keys."
  fi
else
  echo "ℹ️ Laravel Passport not detected. Skipping passport:keys."
fi




#### Step 10: Set Storage directory permission to www-data
echo "🔧 Re-applying permissions to fix any root-owned cache files..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

#### Step 11: Call Supervisord.conf
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
