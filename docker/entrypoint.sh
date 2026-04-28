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

# Run migrations
echo "🗄️  Executando migrations..."
# Espera opcional se o banco externo demorar a responder (timeout de 30s)
for i in $(seq 1 30); do
    if php artisan db:monitor > /dev/null 2>&1; then
        break
    fi
    echo "⏳ Aguardando banco de dados ($i/30)..."
    sleep 1
done

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

# Avisa workers em execução para reiniciar (pegam o código novo após deploy)
echo "🔄 Restart da fila (queue:restart)..."
php artisan queue:restart || true

# Create storage symlink
echo "🔗 Verificando link de storage..."
if [ ! -L public/storage ]; then
    php artisan storage:link --force
fi

# Ensure correct permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create supervisor log directory
mkdir -p /var/log/supervisor

echo "✅ Deploy finalizado! Iniciando serviços..."

exec "$@"
