ARG PHP_VERSION=8.5

FROM dunglas/frankenphp:1-php${PHP_VERSION} AS base
RUN install-php-extensions pdo_mysql opcache pcntl intl zip bcmath

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev

FROM base AS assets
COPY --from=node:22-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY --from=vendor /app /app
RUN cp .env.example .env && php artisan key:generate --force
ARG VITE_APP_NAME=Hookify
ENV VITE_APP_NAME=${VITE_APP_NAME}
RUN npm run build

FROM base
ENV SERVER_NAME=:80
WORKDIR /app
COPY --from=vendor /app /app
COPY --from=assets /app/public/build /app/public/build
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache
ENTRYPOINT ["/entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]