FROM webdevops/php-nginx:8.4

ENV WEB_DOCUMENT_ROOT=/app/public

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && apt-get install -y nodejs
RUN npm install --legacy-peer-deps && npm run build

RUN chown -R application:application /app/storage /app/bootstrap/cache

RUN echo '#!/bin/bash\nphp artisan config:clear\nphp artisan migrate --force\nphp artisan storage:link' > /opt/docker/provision/entrypoint.d/20-laravel-setup.sh
RUN chmod +x /opt/docker/provision/entrypoint.d/20-laravel-setup.sh
