FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    icu-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    zip \
    opcache

RUN apk add --no-cache autoconf gcc g++ make \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del autoconf gcc g++ make

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/app/php.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www

# Ensure storage dirs are writable when volume-mounted
CMD ["sh", "-c", "chmod -R 775 storage bootstrap/cache 2>/dev/null; php-fpm"]
