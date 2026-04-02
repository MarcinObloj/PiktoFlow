FROM webdevops/php-nginx:8.4

# Ustawiamy katalog publiczny Laravela jako główny folder dla serwera WWW
ENV WEB_DOCUMENT_ROOT=/app/public

WORKDIR /app
COPY . .

# 1. Instalacja zależności PHP (Backend)
RUN composer install --no-dev --optimize-autoloader

# 2. Instalacja Node.js i budowanie Vue (Frontend)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && apt-get install -y nodejs
RUN npm install && npm run build

# 3. Nadanie uprawnień do zapisu dla Laravela
RUN chown -R application:application /app/storage /app/bootstrap/cache

# 4. Automatyczne migracje i linkowanie storage przy każdym starcie serwera
RUN echo '#!/bin/bash\nphp artisan migrate --force\nphp artisan storage:link' > /opt/docker/provision/entrypoint.d/20-laravel-setup.sh
RUN chmod +x /opt/docker/provision/entrypoint.d/20-laravel-setup.sh
