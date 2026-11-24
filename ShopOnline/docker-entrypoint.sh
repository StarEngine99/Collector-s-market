#!/bin/sh
set -e

php -m | grep -E 'PDO|pdo_mysql|mysqli|mysqlnd' || true

php artisan config:cache
php artisan storage:link || true

MYSQL_ARGS="-h$DB_HOST -P${DB_PORT:-3306} -u$DB_USERNAME"
export MYSQL_PWD="$DB_PASSWORD"

TABLE_EXISTS=$(mysql $MYSQL_ARGS --batch --skip-column-names -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='model_store' AND table_name='users'" 2>/dev/null || true)
if [ "$TABLE_EXISTS" = "0" ] || [ -z "$TABLE_EXISTS" ]; then
  mysql $MYSQL_ARGS < database/schema.sql
fi

php artisan migrate --force

exec "$@"
