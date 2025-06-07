#!/bin/bash

# Script di avvio per Laravel con Docker
# Gestisce attesa database, migrations e avvio PHP-FPM
# Versione migliorata con gestione errori robusta

set -e

echo "🚀 Tech Company Game - Avvio..."

# Funzione per attendere PostgreSQL con test di connessione più robusto
wait_for_postgres() {
    echo "🔍 Attendendo PostgreSQL..."
    
    max_attempts=30
    attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        echo "⏳ Tentativo $attempt/$max_attempts..."
        
        # Test più specifico per PostgreSQL con il database specifico
        if PGPASSWORD="$DB_PASSWORD" psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" -d "$DB_DATABASE" -c '\q' >/dev/null 2>&1; then
            echo "✅ PostgreSQL è pronto e accessibile!"
            return 0
        fi
        
        sleep 3
        attempt=$((attempt + 1))
    done
    
    echo "❌ Timeout: PostgreSQL non è diventato disponibile dopo $max_attempts tentativi"
    exit 1
}

# Funzione per attendere Redis
wait_for_redis() {
    echo "🔍 Attendendo Redis..."
    
    max_attempts=15
    attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        echo "⏳ Redis tentativo $attempt/$max_attempts..."
        
        if redis-cli -h "$REDIS_HOST" -p "$REDIS_PORT" ping >/dev/null 2>&1; then
            echo "✅ Redis è pronto!"
            return 0
        fi
        
        sleep 2
        attempt=$((attempt + 1))
    done
    
    echo "❌ Timeout: Redis non è diventato disponibile"
    exit 1
}

# Funzione per testare la connessione al database da Laravel
test_laravel_db_connection() {
    echo "🔍 Test connessione database Laravel..."
    
    max_attempts=10
    attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        echo "⏳ Test Laravel DB tentativo $attempt/$max_attempts..."
        
        if php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'OK'; } catch(Exception \$e) { echo 'ERROR: ' . \$e->getMessage(); exit(1); }" 2>/dev/null | grep -q "OK"; then
            echo "✅ Laravel può connettersi al database!"
            return 0
        fi
        
        sleep 2
        attempt=$((attempt + 1))
    done
    
    echo "❌ Laravel non riesce a connettersi al database"
    return 1
}

# Attendere i servizi
wait_for_postgres
wait_for_redis

echo "🔧 Configurazione Laravel..."

# Imposta permessi corretti (solo se necessario)
if [ -w "/var/www/storage" ]; then
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
fi

# Controlla se esiste la chiave dell'app
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo "🔑 Generazione APP_KEY..."
    php artisan key:generate --no-interaction
fi

# Cache delle configurazioni
echo "⚡ Ottimizzazione configurazioni..."
php artisan config:clear
php artisan config:cache

# Test connessione database Laravel
if ! test_laravel_db_connection; then
    echo "❌ Impossibile procedere: database non accessibile da Laravel"
    echo "🔍 Verifica configurazione .env e connettività database"
    exit 1
fi

# === MIGRATION MANAGEMENT ===
echo "🗄️ Gestione Migration Database..."

# Step 1: Assicurati che la migration table esista
echo "🔧 Installazione migration table se necessaria..."
php artisan migrate:install --force 2>/dev/null || echo "ℹ️ Migration table già presente o installazione non necessaria"

# Step 2: Verifica status migration
echo "📊 Verifica status migration..."
migration_status_output=$(php artisan migrate:status 2>&1 || echo "MIGRATION_STATUS_ERROR")

if echo "$migration_status_output" | grep -q "MIGRATION_STATUS_ERROR\|No migrations found\|Migration table not found"; then
    echo "⚠️ Problemi con migration status, procedendo comunque..."
else
    echo "✅ Migration status OK"
fi

# Step 3: Esegui migration con retry logic
echo "📈 Esecuzione migration..."
migration_success=false
max_migration_attempts=3

for migration_attempt in $(seq 1 $max_migration_attempts); do
    echo "⏳ Tentativo migration $migration_attempt/$max_migration_attempts..."
    
    if php artisan migrate --force --verbose 2>&1; then
        echo "✅ Migration eseguite con successo!"
        migration_success=true
        break
    else
        echo "⚠️ Tentativo migration $migration_attempt fallito"
        if [ $migration_attempt -lt $max_migration_attempts ]; then
            echo "🔄 Attendo 5 secondi prima del prossimo tentativo..."
            sleep 5
        fi
    fi
done

# Step 4: Verifica finale migration
if [ "$migration_success" = true ]; then
    echo "📋 Verifica finale migration:"
    php artisan migrate:status 2>&1 || echo "⚠️ Status finale non disponibile ma migration completate"
    
    # Conta le tabelle create
    table_count=$(php artisan tinker --execute="
        try {
            \$count = DB::select('SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ?', [env('DB_DATABASE')]);
            echo \$count[0]->count;
        } catch(Exception \$e) {
            echo '0';
        }
    " 2>/dev/null || echo "0")
    
    echo "📊 Tabelle nel database: $table_count"
    
    if [ "$table_count" -gt "0" ]; then
        echo "✅ Database correttamente inizializzato con tabelle"
    else
        echo "⚠️ Database sembra vuoto, ma migration riportate come riuscite"
    fi
else
    echo "❌ Migration fallite dopo $max_migration_attempts tentativi"
    echo "🔍 Verifica manualmente lo stato del database:"
    echo "   - docker-compose exec app php artisan migrate:status"
    echo "   - docker-compose exec app php artisan migrate --force"
    echo "⚠️ Continuando senza migration complete..."
fi

# === SEEDING (SOLO SE MIGRATION RIUSCITE) ===
if [ "$migration_success" = true ] && [ "$APP_ENV" = "local" ] || [ "$APP_ENV" = "development" ]; then
    echo "🌱 Seeding database (ambiente di sviluppo)..."
    
    # Verifica se la tabella users esiste prima del seeding
    users_table_exists=$(php artisan tinker --execute="
        try {
            DB::table('users')->count();
            echo 'true';
        } catch(Exception \$e) {
            echo 'false';
        }
    " 2>/dev/null || echo "false")
    
    if [ "$users_table_exists" = "true" ]; then
        echo "✅ Tabella users verificata, procedendo con seeding..."
        
        if php artisan db:seed --force --class=DatabaseSeeder 2>&1; then
            echo "✅ Seeding completato con successo!"
        else
            echo "⚠️ Seeding fallito - probabilmente dati già presenti (normale)"
        fi
    else
        echo "❌ Tabella users non trovata, saltando seeding"
        echo "🔍 Migration potrebbero non essere completate correttamente"
    fi
else
    echo "⏭️ Seeding saltato (migration fallite o ambiente non di sviluppo)"
fi

# Cache delle route (solo in produzione)
if [ "$APP_ENV" = "production" ]; then
    echo "⚡ Cache route e views..."
    php artisan route:cache
    php artisan view:cache
fi

# Pulizia cache generale
echo "🧹 Pulizia cache..."
php artisan cache:clear

# Crea il link simbolico per storage se non esiste
php artisan storage:link >/dev/null 2>&1 || true

# === AVVIO SERVIZIO ===
# Avvia il queue worker in background se necessario
if [ "$CONTAINER_ROLE" = "queue" ]; then
    echo "🔄 Avvio Queue Worker..."
    exec php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
else
    echo "🎉 Laravel setup completato, avvio PHP-FPM..."
    
    # Log finale dello stato
    echo ""
    echo "📊 === STATO FINALE SETUP ==="
    echo "- Database: $(php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'Connesso'; } catch(Exception \$e) { echo 'Errore'; }" 2>/dev/null || echo 'Sconosciuto')"
    echo "- Migration: $([ "$migration_success" = true ] && echo 'Completate' || echo 'Problematiche')"
    echo "- Tabelle: $table_count"
    echo "- Ambiente: $APP_ENV"
    echo "=========================="
    echo ""
    
    exec php-fpm
fi