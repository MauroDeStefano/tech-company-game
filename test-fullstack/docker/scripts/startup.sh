#!/bin/bash

set -e
echo "🚀 Tech Company Game - Avvio..."

# Aspetta PostgreSQL
echo "🔍 Attendendo PostgreSQL..."
wait_for_db() {
    local max_attempts=30
    local attempt=1
    while [ $attempt -le $max_attempts ]; do
        if php artisan db:show --database=pgsql >/dev/null 2>&1; then
            echo "✅ Database PostgreSQL connesso!"
            return 0
        fi
        echo "⏳ Tentativo $attempt/$max_attempts..."
        sleep 2
        ((attempt++))
    done
    echo "❌ Database non raggiungibile"
    exit 1
}
wait_for_db

# Genera APP_KEY se manca
if grep -q "APP_KEY=$" .env || ! grep -q "APP_KEY=" .env; then
    echo "🔑 Generazione chiave applicazione..."
    php artisan key:generate --force
fi

# Pulisci cache
echo "🧹 Pulizia cache..."
php artisan config:clear
php artisan cache:clear

# Controlla e esegui migrazioni
echo "📦 Controllo migrazioni database..."
PENDING=$(php artisan migrate:status --pending | grep -c "Pending" || echo "0")
if [ "$PENDING" -gt 0 ]; then
    echo "📋 Esecuzione $PENDING migrazioni..."
    php artisan migrate --force
    echo "✅ Migrazioni completate"
else
    echo "✅ Database già aggiornato"
fi

# Permessi base
echo "🔐 Impostazione permessi..."
mkdir -p storage/logs storage/api-docs
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "🎉 Setup completato - Avvio PHP-FPM..."
exec php-fpm