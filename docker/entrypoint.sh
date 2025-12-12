#!/bin/sh

set -e

# انتظر قاعدة البيانات لحد ما تجهز
echo "Waiting for MySQL..."
until nc -z -v -w30 db 3306; do
  echo "Waiting for database connection..."
  sleep 5
done

# نزل المكتبات إذا vendor مش موجود
if [ ! -d "vendor" ]; then
  echo "Running composer install..."
  composer install --no-dev --optimize-autoloader
fi

# انسخ ملف env إذا مش موجود
if [ ! -f ".env" ]; then
  cp .env.example .env
fi

# اعمل key generate إذا ما في APP_KEY
if ! grep -q "APP_KEY=" .env || [ -z "$(grep APP_KEY= .env | cut -d '=' -f2)" ]; then
  php artisan key:generate --force
fi

# شغل migrate + seed
echo "Running migrations..."
php artisan migrate --seed --force



# شغل php-fpm (الخدمة الرئيسية للـ app)
exec "$@"

