FROM docker.io/dunglas/frankenphp:1-php8.5-alpine

WORKDIR /app

# Install system dependencies
RUN apk add --no-cache \
    git \
    unzip \
    bash \
    nodejs \
    npm

# Install PHP extensions
RUN install-php-extensions \
    gd \
    pcntl \
    posix \
    zip \
    pdo_pgsql \
    pgsql \
    redis \
    imagick \
    opentelemetry \
    bcmath \
    intl

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV OCTANE_SERVER=frankenphp

# Copy application files
COPY . .

# Install dependencies and build assets
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && npm install \
    && npm run build

ENTRYPOINT ["php", "artisan", "octane:start", "--server=frankenphp", "--host=0.0.0.0", "--port=8000"]
