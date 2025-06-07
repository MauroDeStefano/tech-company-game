#!/bin/bash

# Helper script per migration manuali
# Uso: docker-compose exec app bash /usr/local/bin/migrate.sh

set -e

echo "🗄️ Laravel Migration Helper"
echo "=========================="

# Verifica connessione database
echo "🔍 Verifica connessione database..."
if ! php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK';" 2>/dev/null | grep -q "OK"; then
    echo "❌ Impossibile connettersi al database!"
    echo "🔧 Verifica che PostgreSQL sia in running e accessibile"
    exit 1
fi

echo "✅ Database connesso correttamente"

# Mostra status attuale
echo ""
echo "📊 Status migration corrente:"
php artisan migrate:status || echo "⚠️ Impossibile leggere lo status"

echo ""
echo "🔧 Opzioni disponibili:"
echo "1. Esegui migration (migrate)"
echo "2. Rollback ultima migration (rollback)"
echo "3. Reset tutte le migration (reset)"
echo "4. Fresh migration + seed (fresh)"
echo "5. Solo status (status)"
echo "6. Installa migration table (install)"

read -p "Scegli un'opzione (1-6): " choice

case $choice in
    1)
        echo "📈 Esecuzione migration..."
        php artisan migrate --force --verbose
        ;;
    2)
        echo "📉 Rollback ultima migration..."
        php artisan migrate:rollback --force
        ;;
    3)
        echo "🔄 Reset di tutte le migration..."
        read -p "⚠️ Questo cancellerà tutti i dati! Confermi? (yes/no): " confirm
        if [ "$confirm" = "yes" ]; then
            php artisan migrate:reset --force
        else
            echo "❌ Operazione cancellata"
        fi
        ;;
    4)
        echo "🆕 Fresh migration con seeding..."
        read -p "⚠️ Questo ricreerà il database! Confermi? (yes/no): " confirm
        if [ "$confirm" = "yes" ]; then
            php artisan migrate:fresh --force --seed
        else
            echo "❌ Operazione cancellata"
        fi
        ;;
    5)
        echo "📊 Status migration:"
        php artisan migrate:status
        ;;
    6)
        echo "🔧 Installazione migration table..."
        php artisan migrate:install --force
        ;;
    *)
        echo "❌ Opzione non valida"
        exit 1
        ;;
esac

echo ""
echo "✅ Operazione completata!"
echo ""
echo "📊 Status finale:"
php artisan migrate:status || echo "⚠️ Impossibile leggere lo status finale"