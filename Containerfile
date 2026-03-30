FROM docker.io/node:22-alpine AS frontend-builder

ARG MODULE
RUN test -n "${MODULE}" || (echo "missing --build-arg=MODULE=<archeo|etno>" && exit 1)

WORKDIR /frontend
RUN apk add --no-cache git=~2 && \
    git clone --depth 1 https://github.com/metafori-studio/collection-toolbox-frontend.git .

RUN npm install && \
    npm run build --workspace=apps/${MODULE}


FROM docker.io/composer:2.9.5 AS vendor-builder

WORKDIR /app
COPY composer.json composer.lock ./
COPY app-modules/ ./app-modules/
RUN composer install \
    --no-interaction \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts \
    --ignore-platform-reqs


FROM docker.io/dunglas/frankenphp:1.12.1-php8.5

ARG MODULE
RUN test -n "${MODULE}" || (echo "ERROR: MODULE build arg is required (archeo or etno)" && exit 1)
ENV MODULE=${MODULE}

RUN install-php-extensions \
    pdo_pgsql \
    pgsql \
    redis \
    imagick \
    opentelemetry \
    pcntl \
    intl \
    zip \
    opcache \
    curl

WORKDIR /app

COPY . /app
RUN rm -rf bootstrap/cache/*.php

COPY --from=vendor-builder /app/vendor/ /app/vendor/
RUN php artisan package:discover --ansi \
    && php artisan modules:sync

COPY --from=frontend-builder /frontend/apps/${MODULE}/dist/ /app/public/

ENTRYPOINT ["php", "artisan", "octane:frankenphp"]
