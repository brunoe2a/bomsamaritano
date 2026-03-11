#!/bin/sh
set -e

echo "🚀 Iniciando deploy do Bom Samaritano..."

# Create .env if it doesn't exist
if [ ! -f /var/www/html/.env ]; then
    echo "📄 Criando .env a partir do .env.example..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    echo "🔑 Gerando APP_KEY..."
    php artisan key:generate --force
fi

# Create SQLite database if using sqlite
if [ "$DB_CONNECTION" = "sqlite" ]; then
    if [ ! -f /var/www/html/database/database.sqlite ]; then
        echo "📦 Criando banco SQLite..."
        touch /var/www/html/database/database.sqlite
        chown www-data:www-data /var/www/html/database/database.sqlite
    fi
fi

# Run migrations
echo "🗄️  Executando migrations..."
php artisan migrate --force

# Seed admin user
echo "👤 Verificando usuário admin..."
php artisan db:seed --class=AdminSeeder --force

# Cache configurations for production
echo "⚡ Otimizando para produção..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Create storage symlink
php artisan storage:link --force 2>/dev/null || true

# Ensure correct permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create supervisor log directory
mkdir -p /var/log/supervisor

echo "✅ Deploy finalizado! Iniciando serviços..."

exec "$@"
